<?php

	//Para funcionar com a versão anterior
	$id = $_GET["id"];

	$bmm  = new BRIGHT_mail_feedback();
	
	$query ="SELECT * FROM `mensagens` WHERE `id` = '".$id."'";
	$res = mysql_query( $query ) or die(mysql_error());
	$mensagem = mysql_fetch_object($res);
	if( ! $mensagem ) {
		?>
		<h1>Erro:</h1>
		<p>A mensagem não foi encontrada.</p>
		<?php
		return false;
	}
	echo '<h2>Estatisticas para a mensagem: '.$mensagem->assunto.'</h2>';
	

	//Ir buscar os envios

	if ( $_SESSION["user"]->is_admin ) {
		$query = "select envios.*, users.first_name, users.last_name from envios left join users on users.id = envios.user_id where mensagem_id = '".$mensagem->id."'";
	}else{
		$query = "select * from envios where mensagem_id = '".$mensagem->id."' and user_id = '".$_SESSION["user"]->id."'";
	}
	$res = mysql_query($query) or die( mysql_error() );
	if ( mysql_num_rows( $res ) == 0  ) {
		?>
		<h1>Erro:</h1>
		<p>Esta mensagem não tem nenhum envio associado.</p>
		<?php
		return false;

	}elseif( mysql_num_rows( $res ) == 1 ) {
		$active_envio = mysql_fetch_array($res);
	}else{
		$i = 1;
		echo '<div class="control-group">
			<label>Envio:</label>';
		echo '<form method="get" action="index.php" class="form-horizontal">';
		echo '<input type="hidden" name="mod" value="newsletter" />';
		echo '<input type="hidden" name="view" value="statistics" />';
		echo '<input type="hidden" name="id" value="'.$_GET["id"].'" />';
		echo '<div class=""><select name="envio_id" class="inline" style="width:auto;">';
		while( $row = mysql_fetch_array($res) ) {	//Iterar os envios
			if ( $i == 1 ) {
				$active_envio = $row;
				$i++;
			}
			if ( isset( $_GET["envio_id"] ) && $row["id"] == $_GET["envio_id"] ) {
				$active_envio = $row;
				echo '<option value="'.$row["id"].'" selected="selected">'.$row["date_sent"].( ($_SESSION["user"]->is_admin)? ' ('.$row["first_name"].' '.$row["last_name"].') ':'' ).'</option>';
			}else{
				echo '<option value="'.$row["id"].'">'.$row["date_sent"].'</option>';
			}
		}
		echo '</select> <input type="submit" class="btn btn-primary" value="Visualizar" name="submit" /> </div></div>';
	}



	//$client_id = $bmm->get_client_id();
	$num_aberturas = $bmm->get_num_opened_from_newsletter($active_envio["id"]);

	//Isto tem que mudar
	if( $mensagem->estado_code == 0 || $mensagem->estado == "Não utilizada" || $num_aberturas === 0 ){
		?>
		<p>Ainda não existem dados suficientes para aceder a este módulo.</p>
		<?php
		return false;
	}
	?>
	

	<!-- Navegação lateral, Uma para cada estatística -->
	<ul class="nav nav-tabs" id="tabThis">
		<li><a href="#home" data-toggle="tab">Dados gerais</a></li>
		<li><a href="#profile" data-toggle="pill">Listagem aberturas</a></li>
		<li><a href="#clicks" data-toggle="pill">Listagem de cliques</a></li>
		<li class="active"><a href="#messages" data-toggle="tab">Gráficos</a></li>
	</ul>

	<div class="tab-content">

		<div class="tab-pane" id="home">
			<table class="table table-condensed table-bordered">
				<thead>
					<tr>
						<td>-</td>
						<th>#</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$query = "SELECT * FROM `mensagens_enviadas` WHERE `mensagem_id`= '".$id."' AND `output` = '3'"; 
					$res = mysql_query( $query ) or die( mysql_error() );
					$envios_espera = mysql_num_rows($res);

					//$query = "SELECT COUNT(id) as total FROM `subscribers` WHERE hard_bounces_count > 0";
					$query = "SELECT COUNT(id) as total from subscribers where hard_bounces_count > 0 AND FIND_IN_SET($id, bounced_on);";

					$res = mysql_query($query);
					if ( $res ) {
						$bounces = mysql_fetch_object($res);
						$bounces_count =  intval($bounces->total);
						# code...
					}else{
						$bounces_count = 0;
					}


					$query = "SELECT * FROM `mensagens_enviadas` WHERE `mensagem_id`= '".$id."' AND `output` = '0'"; 
					$res = mysql_query( $query ) or die( mysql_error() );
					$envios_falhados = mysql_num_rows($res);

					

					$evios_sucesso = $bmm->get_num_send($active_envio["id"]);
					$envios = $envios_espera + $envios_falhados + $evios_sucesso;

					?>

					<tr>
						<th>Número de envios </th>
						<td><?php echo $envios ?></td>
					</tr>
					<tr>
						<th>Envios bem sucedidos</th>
						<td>
							<?php echo $evios_sucesso - $bounces_count; ?>
						</td>
					</tr>
					<tr>
						<th>Envios em espera </th>
						<td>
							<?php echo $envios_espera; ?>
						</td>
					</tr>
					<tr>
						<th>Envios falhados </th>
						<td>
							<?php echo $envios_falhados; ?>
						</td>
					</tr>
					<tr>
						<th>Envios com retorno de erro por parte do destinat&aacute;rio (bounces) </th>
						<td>
							<?php echo $bounces_count; ?>
						</td>
					</tr>
					<tr>
						<th>Total de aberturas</th>
						<?php

						$num_aberturas = $bmm->get_num_opened_from_newsletter($active_envio["id"]);
						$num_pessoas_abriram = $bmm->get_num_distinct_opened_from_newsletter($active_envio["id"]);

						?>
						<td>
							<?php echo $num_aberturas; ?>
						</td>
					</tr>
					<tr>
						<th>Visualiza&ccedil;&otilde;es (&uacute;nicas)</th>
						<td>
							<?php echo $num_pessoas_abriram; ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- cliques -->
		<?php
		//info dos cliques
		$bmm = new BRIGHT_mail_feedback();
		$clicks = $bmm->get_clicks_from_client($active_envio["id"]);

		//definir as colunas
		$columns = array("email", "url", "date", "referer", "ip");

		//carregar para tabela temporaria
		$bmm->load_clicks_into_table($clicks, $columns);

		//processar os dados
		//links mais visitados
		$top_clicks = $bmm->get_top_clicks();


		//utilizadors mais activos
		$top_active_users = $bmm->get_top_active_users();

		//remover a tabela temporári
		$bmm->clear_clicks_table();

		?>
		<div class="tab-pane" id="clicks">
			<table class="table data-sort" id="clicks-table">
				<thead>
					<tr>
						<th>Email</th>
						<th>URL</th>
						<th>Data</th>
						<th>Origem</th>
						<th>IP</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($top_clicks as $click): ?>
					<tr>
						<td><?php echo $click["email"] ?></td>
						<td><?php echo $click["url"] ?></td>
						<td><?php echo $click["date"] ?></td>
						<td><?php echo $click["referer"] ?></td>
						<td><?php echo $click["ip"] ?></td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</div>
	<!-- cliques -->

	<!-- Listagem de aberturas -->
	<div class="tab-pane" id="profile">
		<?php
		$bmm = new BRIGHT_mail_feedback();
		$list = $bmm->get_opened_from_client($active_envio["id"]);

		?>

		<table class="table" id="datasort">
			<thead>
				<tr>
					<th>Email</th>
					<th>Navegador</th>
					<th>IP</th>
					<th>Origem</th>
					<th>Data de visualiza&ccedil;&atilde;o</th>                    
				</tr>
			</thead>
			<tbody>

				<?php 
				foreach ($list as $item) { ?>
				<tr>
					<td><a href="mailto: <?php echo $item->email ?>"><?php echo $item->email ?></a></td>
					<td>
						<a href="#" class="tooltip2" rel="tooltip" data-original-title="<?php echo $item->user_agent; ?>">
							<?php echo substr( $item->user_agent, 0, 50); echo ( $item->user_agent > 50 )? '...' : ''; ?>
						</a>
					</td>
					<td><?php echo $item->ip ?></td>
					<td>
						<a href="#" class="tooltip2" rel="tooltip" data-original-title="<?php echo $item->user_agent; ?>">
							<?php echo substr( $item->referer, 0, 50); echo ( $item->referer > 50 )? '...' : ''; ?>
						</a>
					</td>
					<td><?php echo ($item->date_in) ?></td>
				</tr>

				<?php 
			}
			?>
		</tbody>
	</table>


</div>
<!-- graficos -->
<div class="tab-pane active bright_graph" id="messages">
	
	<div class="row-fluid">
		<div class="span6">
			<div id="morris-pie-aberturas" style="height:200px;"></div>
		</div>
		<div class="span6">
			<table class="table table-condensed table-bordered  discrete">
				<caption>Percentagem de aberturas</caption>
				<thead>
					<tr>
						<th></th>
						<th>Número</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th>Entregues sem abertura de imagens</th>
						<td id="morris-recebidas-nao-lidas"><?php echo ($evios_sucesso - $num_pessoas_abriram - $bounces_count); ?></td>
					</tr>
					<tr>
						<th>Entregues com abertura de imagens</th>
						<td id="morris-recebidas-lidas"><?php echo $num_pessoas_abriram; ?></td>
					</tr>
					<tr>
						<th>Devolvidos</th>
						<td id="morris-bounces"><?php echo $bounces_count ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	
	<div id="morries-line-aberturas" style="height:250px;"></div>
	

	<!-- Tabela com as aberturas em função do tempo -->

	<?php
				#Pre-Processing
	unset($list);
	$bmm = new BRIGHT_mail_feedback();
	$list = $bmm->get_statistics_by_day($active_envio["id"]);

	?>

	<?php if ($list): ?>

	<table style="display:none;" id="table-aberturas-dia" class="table table-condensed table-bordered discrete">
		<caption>N&uacute;mero de aberturas por dia</caption>
		<thead>
			<tr>
				<td></td>
				<?php 
				$counter = 0;
				foreach ($list as $item) { ?>
				<?php if ($counter < 7): ?>
				<th class="data-header"><?php echo substr($item->day, 0, 2) ?>/<?php echo substr($item->day, 2, 4) ?></th>
			<?php endif ?>

			<?php 
			$counter ++;

		}
		?>
		<th class="data-header">&gt; 7</th>
	</tr>
</thead>
<tbody>
	<tr>
		<th># de aberturas</th>
		<?php 
		$counter = 0;
			$total = 0; //total acima de 7
			foreach ($list as $item) { ?>
			<?php if ($counter < 7): ?>
			<td class="data-value"><?php echo $item->num; ?></td>
		<?php else: ?>
		<?php $total += $item->num ?>
	<?php endif ?>
	<?php 
			$counter++; //incrementar a contagem, limitar a 7
		}
		?>
		<td class="data-value"><?php echo $total ?></td>
	</tr>
</tbody>
</table>
<?php endif ?>
</div>
<!-- graficos -->

<br />
<br />

<div class="clear"></div>

<!-- clicks -->
<div class="row-fluid">

	<?php if (!empty($top_clicks)): ?>
	
	<div class="well most-viewed">

		<div class="span6">
			<table class="table table-condensed table-bordered">
				<caption>Conte&uacute;do mais visualizado</caption>
				<thead>
					<tr>
						<td>URL</td>
						<td>Total de cliques</td>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($top_clicks as $click): ?>
					<tr>
						<td><a href="<?php echo $click->url ?>"><?php echo $click["url"] ?></a></td>
						<td><?php echo $click["total_clicks"] ?></td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</div>

	<div class="span6">
		<table class="table table-condensed table-bordered">
			<caption>Utilizadores mais activos</caption>
			<thead>
				<tr>
					<td>E-mail</td>
					<td>Total de cliques</td>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($top_active_users as $user): ?>
				<tr>
					<td><a href="mailto:<?php echo $user["email"] ?>"><?php echo $user["email"] ?></a></td>
					<td><?php echo $user["total_clicks"] ?></td>
				</tr>
			<?php endforeach ?>
		</tbody>
	</table>
</div>

<div class="clear"></div>

</div>

<?php else: ?>

	<div class="alert alert-info">N&atilde;o h&aacute; ainda dados suficientes para gerar estat&iacute;sticas sobre cliques</div>

<?php endif ?>

</div>

</div>
			<!--
			<div class="tab-pane" id="settings">
			</div>
		-->

	</div>

