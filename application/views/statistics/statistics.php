<?php
	#Número de newsletters
	$query = "SELECT * FROM `mensagens`";
	$res = mysql_query($query) or die( mysql_error() );
	$num_news = mysql_num_rows( $res );

		#Núemro de newsletters enviadas
	$query = "SELECT * FROM `mensagens` WHERE `estado_code` = '1'";
	$res = mysql_query($query) or die( mysql_error() );
	$num_news_enviadas = mysql_num_rows( $res );

		#Evolução dos subscritores
	$query = "SELECT COUNT(email) as num, MONTH(date_created) as month FROM `subscribers` WHERE `is_active` = '1' GROUP BY MONTH(date_created)";

	$res = mysql_query( $query ) or die( mysql_error() );
	while( $row = mysql_fetch_object( $res ) ) {
		$evo[(int)$row->month] = $row;
	}

	?>

	<h1>Estat&iacute;sticas</h1>

	<!-- Navegação lateral, Uma para cada estatística -->
	<ul class="nav nav-tabs" id="tabThis">
		<li><a href="#home" data-toggle="tab">Newsletters</a></li>
		<li class="active"><a href="#profile" data-toggle="pill">Subscritores por m&ecirc;s</a></li>
		<li><a href="#bounces" data-toggle="tab">Bounces</a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane" id="home">
			<table class="table table-condensed table-bordered">
				<caption>Newsletters</caption>
				<thead>
					<tr>
						<th></th>
						<th>Número</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th>Número de newsletters</th>
						<td><?php echo $num_news; ?></td>
					</tr>
					<tr>
						<th>Número de newsletters enviadas</th>
						<td><?php echo $num_news_enviadas; ?></td>
					</tr>
				</tbody> 
			</table>

			<div class="alert alert-info">Seleccione uma das newsletters para visualizar dados relativos ao envio da mesma</div>

			<div>
				<?php $mensagens_res = $statistics->get_messages(0, 30); ?>
				<table class="table table-bordered">
					<tr>
						<th>#</th>
						<th>Newsletter</th>
						<th>Data Cria&ccedil;&atilde;o</th>
						<th>Estado</th>
					</tr>
					<?php while ($newsletter = mysql_fetch_object($mensagens_res)): ?>
					<tr>
						<td><?php echo $newsletter->id ?></td>
						<td><a href="?mod=statistics&amp;view=newsletter_statistics&amp;id=<?php echo $newsletter->id ?>"><?php echo $newsletter->assunto ?></a></td>
						<td><?php echo $newsletter->data_criada ?></td>
						<td><span class="label label-info"><?php echo $newsletter->estado ?></span></td>
					</tr>	
				<?php endwhile ?>
				<tr>

				</tr>
			</table>
		</div>
	</div>

		<!--div class="tab-pane" id="messages">
	</div-->

	<?php
	$sql = "SELECT COUNT(id) as total, MONTH(date_created) AS month_created, YEAR(date_created) AS year_created FROM subscribers GROUP BY year_created, month_created";
	$query = mysql_query($sql);

	while ($row = mysql_fetch_object($query)) {
		$subscribers_per_month[$row->month_created] = $row->total;
	}

	for ($i=1; $i < 13; $i++) { 
		$subscribers_per_all_months[$i] = (int) $subscribers_per_month[$i];
	}

	?>

	<div class="tab-pane bright_graph active" id="profile">
		<table id="evo-subscritores" class="table table-condensed table-bordered discrete">
			<caption>Evolução dos subscritores</caption>
			<thead>
				<tr>
					<td></td>
					<th class="data-header">Jan</th>
					<th class="data-header">Fev</th>
					<th class="data-header">Mar</th>
					<th class="data-header">Abr</th>
					<th class="data-header">Mai</th>
					<th class="data-header">Jun</th>
					<th class="data-header">Jul</th>
					<th class="data-header">Ago</th>
					<th class="data-header">Set</th>
					<th class="data-header">Out</th>
					<th class="data-header">Nov</th>
					<th class="data-header">Dez</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<th>Número de subscritores</th>
					<td class="data-value"><?php echo $subscribers_per_all_months[1] ?></td><td class="data-value"><?php echo $subscribers_per_all_months[2] ?></td><td class="data-value"><?php echo $subscribers_per_all_months[3] ?></td><td class="data-value"><?php echo $subscribers_per_all_months[4] ?></td><td class="data-value"><?php echo $subscribers_per_all_months[5] ?></td><td class="data-value"><?php echo $subscribers_per_all_months[6] ?></td><td class="data-value"><?php echo $subscribers_per_all_months[7] ?></td><td class="data-value"><?php echo $subscribers_per_all_months[8] ?></td><td class="data-value"><?php echo $subscribers_per_all_months[9] ?></td><td class="data-value"><?php echo $subscribers_per_all_months[10] ?></td><td class="data-value"><?php echo $subscribers_per_all_months[11] ?></td><td class="data-value"><?php echo $subscribers_per_all_months[12] ?></td></tr>
				</tbody>
			</table>

			<div id="morris-bars-subscritores" style="height:150px;"></div>

		</div>


		<!-- bounces -->
		<div class="tab-pane" id="bounces">
			<?php 
			$sql = "select * from subscribers where hard_bounces_count > 0";
			$query = mysql_query($sql);
			$total_bounces = mysql_num_rows($query);

			?>

			<div class="alert alert-info">
				<span>De um total de <b><?php echo $total ?></b> subscritores,  <b><?php echo $total_bounces ?></b> est&atilde;o automaticamente exclu&iacute;dos da lista de envio devido a mensagens anteriores terem sido devolvidas.</span>
			</div>
			<table class="table table-bordered">
				<tr>
					<th>E-mail</th>
				</tr>
				<?php while ($bounce = mysql_fetch_object($query)): ?>
				<tr>
					<td><?php echo $bounce->email ?></td>
				</tr>
			<?php endwhile ?>
		</table>
	</div>
	<!-- bounces -->

</div>


