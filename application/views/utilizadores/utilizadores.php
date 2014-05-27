<?php
$users = $utilizadores->get_users();
 ?>
		<div class="main_div">
			<h1>Utilizadores</h1>
			<a href="?mod=utilizadores&amp;view=add_utilizador&amp;id=0" class="add-new-news btn btn-success"><i class="icon-white icon-plus-sign"></i> <?php echo _('Adicionar Utilizador');?></a>

			<br />
			<br />
			<div class="clear"></div>


			<table class="addpadding table" id="datasort">
				<thead>
					<tr>
						<th>Nome</th>
						<th>Data Criação</th>
						<th>Permissões em:</th>
						<th>Estado</th>
						<th>Administrador</th>
						<th class="nosrot">Remover</th>
					</tr>
				</thead>

				<tbody>
					<?php foreach ($users as $user): ?>
					<?php $group_permissions = $utilizadores->get_group_permissions($user->id); ?>
					<tr>
						<td><a href="?mod=utilizadores&amp;view=add_utilizador&amp;id=<?php echo $user->id ?>"><?php echo $user->first_name . " " . $user->last_name ?></a></td>
						<td><?php echo $user->date_joined ?></td>
						<td><?php render_group_permissions( intval($user->is_admin), $group_permissions); ?> </td>
						<td><?php Core::draw_boolean_status($user->is_active) ?></td>
						<td><?php Core::draw_boolean_status($user->is_admin) ?></td>
						<td><a class="btn btn-danger" href="?mod=utilizadores&amp;view=remove_utilizador&amp;id=<?php echo $user->id ?>"><i class="icon-white icon-remove"></i> Remover</a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

	</div>

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
