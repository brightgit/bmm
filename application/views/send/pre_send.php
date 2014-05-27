<?php
	//$tools = $this->getTools();

	//Compatibiliade com a versão anterior.
	$id = $_GET["id"];

	echo '<div class="main_div">';
	function return_to_messages($string_error){
		echo '<div class="error">'.$string_error.'</div>';
		echo '<!-- meta http-equiv="refresh" content="1; url=?mod=newsletters&view=messages" -->';
	}
	if($id == -1){
		return_to_messages('Mensagem não encontrada. A voltar para a listagem...');
	}
	$mensagem = $send->get_mensagem_by_id($id);
	if(!$mensagem){
		return_to_messages('Mensagem não encontrada. A voltar para a listagem...');
	}
		//A processar mensagem: 
	echo '<h1>A processar mensagem: <br />'.$mensagem->assunto.'</h1>';



	/* No more external db
	try {
		//$pdo = new PDO($pdo_string);

		$pdo = new PDO('mysql:host=195.200.253.230;dbname=brightmi_mail_stats', "brightmi_mstats", "Bright#$91", array(
			PDO::ATTR_PERSISTENT => false
			));
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	} catch (PDOException $ex) {
		echo 'Connection failed: ' . $ex->getMessage();
		$pdo = false;
	}		


	//Tenho que ir buscar o client_id
	$sql = "SELECT * FROM clients where `domain` = 'holmesplacenews.pt'";
	//echo $sql;

	$query = $pdo->query( $sql );
	$client = $query->fetchObject();

	*/



	?>
	<!-- Acções -->
	<p style="text-align:left;" class="action_messages">
		<a class="btn" href="../inc/visualize_news.php/<?php echo $mensagem->id; ?>/mensagem-teste/admin" target="_blank"><i class="icon-eye-open"></i> Pré-visualizar</a>
		<a class="btn" href="?mod=newsletters&view=add_mensagem&id=<?php echo $id; ?>"><i class="icon-pencil"></i> Editar</a>
		<a class="btn btn-info newsletter_toggle" href="#">Enviar teste <i class="icon-share icon-white"></i></a>
		<a class="btn btn-success" href="?mod=send&view=enviar&id=<?php echo $id; ?>">Enviar <i class="icon-share icon-white"></i></a>
	</p>
	<!-- POP UP Actions forms -->
	<!-- Enviar teste -->        
	<form action="?mod=send&view=send_test_email" method="post" id="send_test" class="newsletter_expand">
		<!--p style="text-align:left; background-color:whitesmoke; border:1px solid grey; margin:30px;" id="text_email_p"-->
		<div class="well">
			<div>

				<label>Remetente</label>
				
				<?php $sender_permissions = $send->get_sender_permissions($_SESSION["user"]->id) ?>

				<select name="sender_id">
					<option>Seleccione um remetente</option>
					<?php foreach ($sender_permissions as $key => $email): ?>
					<option value="<?php echo $key ?>"><?php echo $email["email_from"] ?> - <?php echo $email["email"] ?></option>
				<?php endforeach ?>
			</select>

			<p><i class="icon-info-sign"></i> <small>Ir&aacute; enviar esta newsletter para o endere&ccedil;o introduzido abaixo</small></p>
			<input type="hidden" name="mensagem_id" value="<?php echo $_GET["id"] ?>" />
			<label class="" for="text_email">Email de destino: </label>
		</div>
		<input type="text" name="text_email" id="text_email" />
		<!--input type="submit" class="submit btn btn-info" value="Enviar" /-->
		<input type="submit" class="submit btn btn-info" value="Enviar" />
	</div>
	<!--/p-->
</form>

<h2 style="text-align:left;">Informação geral: </h2>
<div class="well">
	<!-- Informação -->
	<p style="text-align:left;"><span>Estado: </span> <span class="label label-info"><?php echo $mensagem->estado; ?></span></p>
	<p style="text-align:left;"><span>Último Update:</span> <b><?php echo tools::timestamp_to_jan($mensagem->data_update); ?></b></p>
	<p style="text-align:left;"><span>Data Criação:</span> <b><?php echo tools::timestamp_to_jan($mensagem->data_criada); ?></b></p>
</div>


<h2 style="text-align:left;">Emails teste já enviados: </h2>

<?php $res = $send->get_send_test($_GET['id']); ?>


<?php if(mysql_num_rows($res) > 0): ?>

	<table class="table">
		<tr>
			<th>Destinatário</th>
			<th>Data</th>
		</tr>

		<?php 
		while($row = mysql_fetch_object($res)){
			if($row->output == 'sucesso'){
				$style ="background-color:lightgreen;";
			}else{
				$style ="background-color:red;";
			}
			echo "
			<tr style=\"".$style."\">
			<td>".$row->destino."</td>
			<td>".tools::timestamp_to_jan($row->hora)."</td>
			</tr>";

				//echo '<p style="text-align:left; '.$style.'"><span class="label">'.$row->assunto.'</span><span class="label">'.$row->destino.'</span>'.$tools->timestamp_to_jan($row->hora).'</p>';
				//echo '<p style="text-align:left; '.$style.'"><span class="label">'.$row->assunto.'</span><span class="label">'.$row->destino.'</span>'.$tools->timestamp_to_jan($row->hora).'</p>';
		}
		
		?>

	</table>

<?php else: ?>

	<div class="alert alert-info">Ainda n&atilde;o foram efectuados testes de envio.</div>

<?php endif; ?>

</div>