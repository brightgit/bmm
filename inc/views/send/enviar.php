<?php
	//Compatibilidade com a versão anterior.
	$id = $_GET["id"];

	if($id == -1){
		echo '<div class="error">Não encontrado.</div>';
		echo '<meta http-equiv="refresh" content="1; url=?mod=newsletters&view=messages">';
		die('');
	}

	$mensagem = $send->get_mensagem_by_id($id);
	if(!$mensagem){
		echo '<div class="error">Erro: Mensagem não encontrada.</div>';
		echo '<meta http-equiv="refresh" content="1; url=?mod=newsletters&view=messages">';
		die('');
	}
		//Mensagens de aviso
	?>
	<div class="alert alert-info"><h1>A enviar mensagem: <?php echo $mensagem->assunto; ?></h1></div>
	<p style="text-align:left;">Antes de enviar, por favor confirme que:</p>
	<ul class="send-tips">
		<li>Enviou um email de teste para várias plataformas e mail services por forma a garantir a mesma experiência de visualização a todos os destinatários.</li>
		<li>Adicionou um link alternativo de visualização da newsletter no navegador</li>
		<li>Adicionou um link / método de remoção da mailing list. A falta de um método de remoção poderá trazer complicações legais em caso de queixa.</li>
		<li>Cumpriu, dentro do possível as boas práticas na composição de um email HTML: textos preferencialmente escritos e não embebidos em imagens; HTML bem construído com <b>alts</b> em imagens sempre que aplicável.</li>

		<li style="padding:15px 0px; font-weight:bold; list-style-type:none;"><i class="icon-exclamation-sign"></i> Avançado</li>

		<li>Não utilizar JavaScript ou links externos para CSS.</li>
		<li>A submissão de vários testes de envio para determinados mail services, poderá aumentar a possibilidade de um envio definitivo ser tratado como legítimo.</li>
	</ul>

	<form action="?mod=send&view=send_to_all" method="post">
		<input type="hidden" name="mensagem_id" value="<?php echo $_GET['id']; ?>" />

		<div>
			<?php 
				//listar todos todos os grupos
			if ($_SESSION["user"]->is_admin != 0) {
				$query = "SELECT * FROM `newsletter_categorias`";
			}else{
				$query = "SELECT * FROM `newsletter_categorias` left join user_permissions on user_permissions.group_id = newsletter_categorias.id 
				where user_permissions.user_id = ".$_SESSION["user"]->id;
			}
				//echo $query;
			$res = mysql_query( $query );

			if ($res) {
				while ($row = mysql_fetch_object($res)) {
					$groups[] = $row;
				}
			} 
			?>

			<h2>Opções de envio</h2>
			<div class="multiple-checkboxes well">

				<label>Remetente</label>
				
				
				<?php $sender_permissions = $send->get_sender_permissions($_SESSION["user"]->id) ?>

				<select name="sender_id">
					<option>Seleccione um remetente</option>
					<?php foreach ($sender_permissions as $key => $email): ?>
					<option value="<?php echo $key ?>"><?php echo $email["email_from"] ?> - <?php echo $email["email"] ?></option>
				<?php endforeach ?>
			</select>

			<br /><br />

			<label>Destinatários</label>


			<label class="checbox">
				<input type="checkbox" class="select-all" />
				Todos
			</label>

			<?php foreach ($groups as $group): ?>
			<label class="checkbox">
				<input type="checkbox" name="groups[]" value="<?php echo $group->id ?>" />
				<?php echo $group->categoria ?>
			</label>
		<?php endforeach ?>

	</div>
</div>

<input type="submit" name="enviar_grupo" value="Enviar" class="btn btn-danger" />

</form>
