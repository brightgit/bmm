<?php
	//Previous Version support
	$id = $_GET["id"];

	$user = $utilizadores->get_utilizador($id);
?>

			<h1>A editar utilizador</h1>

			<a class="btn" href="?mod=utilizadores&amp;view=utilizadores">Voltar</a>

			<br />
			<br />


			<form action="index.php?mod=utilizadores&view=add_update_utilizador&id=<?php echo $_GET["id"]; ?>" method="post" enctype="multipart/form-data">
			<div class="well">
				<div class="row-fluid">
				

					<div class="span4">
						<label>Username:</label>
						<input type="text" name="user_username" value="<?php echo $user->username ?>" />

						<label>Primeiro nome:</label>
						<input type="text" name="user_first_name" value="<?php echo $user->first_name ?>" />

						<label>Último nome:</label>
						<input type="text" name="user_last_name" value="<?php echo $user->last_name ?>" />

						<label>E-mail:</label>
						<input type="text" name="user_email" value="<?php echo $user->email ?>" />

						<label>Activo:</label>
						<span>Sim</span>
						<input type="radio" name="is_active" <?php echo ($user->is_active == 1) ? "checked=\"checked\"":"" ?> />

						<span>N&atilde;o</span>
						<input type="radio" name="is_active" <?php echo ($user->is_active == 0) ? "checked=\"checked\"":"" ?> />

						<label>(Re)definir senha:</label>
						<input type="password" name="user_password" value="" />
					</div>

					<div class="span4">
						<label>Tipo de utilizador:</label>
						<select name="user_group">
							<?php echo $utilizadores->render_user_groups($user->user_group); ?>
						</select>

						<h2>Permiss&otilde;es sobre as mailing lists</h2>

						<?php 
						$groups = $utilizadores->get_grupos(); 
						$senders = $utilizadores->get_senders();
						?>

						<ul>
							<?php foreach($groups as $grupo): ?>
							<?php $user_permissions = $utilizadores->get_group_permissions($user->id); ?>
							<?php $checked = (@array_key_exists($grupo->id, $user_permissions)) ? "checked=\"checked\"":""; ?>
							<li><input value="<?php echo $grupo->id ?>"  type="checkbox" name="user_group_permissions[]" <?php echo $checked ?> /> <span><?php echo $grupo->categoria ?></span></li>
						<?php endforeach ?>
					</ul>

					<h2>Pode enviar de</h2>

					<ul>
						<?php foreach ($senders as $sender): ?>
						<?php $sender_permissions = $utilizadores->get_sender_permissions($user->id); ?>
						<?php $checked = (@array_key_exists($sender->id, $sender_permissions)) ? "checked=\"checked\"":""; ?>
						<li><input value="<?php echo $sender->id ?>"  type="checkbox" name="user_sender_permissions[]" <?php echo $checked ?> /> <span><?php echo $sender->email_from ?> (<b><?php echo $sender->email ?></b>)</span></li>
					<?php endforeach ?>

				</ul>
			</div>

			<div class="span4">
				<h2>Logótipo</h2>

				<?php if(!empty($user->image_blob)): ?>
				<div>
					<img class="img-polaroid" src="data:image/jpeg;base64, <?php echo base64_encode( $user->image_blob ) ?>"/>
				</div>
				<?php endif; ?>

				<br />

				<input type="file" name="image" />
				<br />

				<h2>Host</h2>
				<div>
				<input type="text" name="sender_host" value="<?php echo $user->sender_host; ?>" />
				</div>

		</div>

	
</div>
</div>

<div class="pull-right">
	<a class="btn" href="?mod=utilizadores&amp;view=utilizadores">Voltar</a>
	<input class="btn btn-primary" type="submit" name="save" value="Guardar" />
</div>

</form>

<div class="clearfix"></div>

<?php 
function render_group_permissions($is_admin, $groups){

	if($is_admin){
		echo "<span class=\"label label-success\">Todos os grupos</span> ";
	}

	else{
		if(count($groups) > 0){
			foreach ($groups as $group) {
				echo "<span class=\"label label-success\">" . $group. "</span> ";
			}
		}

		return false;
	}
	
}


 ?>
