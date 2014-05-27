		<?php
		//há necessidade de update?
		/* Passado para o mod;
		if($_POST["update_group"])
			$this->update_grupo($id);
		*/
		//informação do grupo
		$grupo = $grupos->get_grupo($_GET["id"]); 

		?>



		<div class="well">

			<h1>A editar grupo</h1>

			<form method="post" action="index.php?mod=grupos&view=update_group&id=<?php echo $_GET["id"]; ?>">
				<label>Nome:</label>
				<input type="text" name="group_name" value="<?php echo $grupo->categoria ?>" />

				<br />
				<br />

				<label>Adicionar novos subscritores a este grupo automaticamente?</label>
				<span>Sim</span> <input <?php echo ($grupo->is_default == 1) ? "checked=\"checked\"":"" ?> type="radio" name="is_default" value="1" />
				<span> N&atilde;o</span> <input <?php echo ($grupo->is_default == 0) ? "checked=\"checked\"":"" ?> type="radio" name="is_default" value="0" />

				<br />
				<br />

				<input class="btn btn-primary" type="submit" name="update_group" value="Editar" />
				<a href="?mod=grupos&amp;view=grupos" class="btn">Voltar</a>
			</form>

		</div>

		<h2>Utilizadores com permiss&atilde;o sobre este grupo</h2>

		<?php $users_with_permission = $grupos->get_users_with_permission_in_group($grupo->id) ?>

		<?php if(count($users_with_permission) > 0): ?>

		<table class="table table-bordered">
			<thead>
				<th>id</th>
				<th>Nome</th>
				<th>Username</th>
			</thead>
			<tbody>				

				<?php foreach($users_with_permission as $user): ?>
				
				<tr>
					<td><?php echo $user->id ?></td>
					<td><a href="?mod=utilizadores&amp;view=edit_utilizador&amp;id=<?php echo $user->id ?>"><?php echo $user->first_name . " " . $user->last_name ?></a></td>
					<td><?php echo $user->username ?></td>
				</tr>
				
			<?php endforeach; ?>
			
		</tbody>
	</table>

<?php else: ?>

	<div class="alert alert-info">N&atilde;o existem utilizadores com permiss&atilde;o sobre este grupo (excepto o(s) <b>Administrador(es))</b></div>

<?php endif; ?>
