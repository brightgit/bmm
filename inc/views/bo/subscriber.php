<div class="content">

	<h1>A editar subscritor</h1>

	<a class="btn" href="?mod=newsletter&amp;view=subscribers">Voltar</a>

	<br>
	<br>

	<div class="well">
		<form action="" method="post" novalidate="novalidate">

			<div class="row-fluid">
				<div class="span4">
					<h4>Informação pessoal</h4>

					<label>Nome:</label>
					<input type="text" name="subscriber_username" value="<?php echo $data->subscriber->nome ?>">

					<label>Email:</label>
					<input type="text" name="subscriber_first_name" value="<?php echo $data->subscriber->email ?>">

					<label>Sexo:</label>
					<input type="text" name="subscriber_first_name" value="<?php echo $data->subscriber->email ?>">

					<label>Telefone:</label>
					<input type="text" name="subscriber_telefone_1" value="<?php echo $data->subscriber->telefone_1 ?>">

					<label>Telefone (alternativo):</label>
					<input type="text" name="subscriber_telefone_1" value="<?php echo $data->subscriber->telefone_2 ?>">

					<label>Data nascimento:</label>
					<input type="text" name="subscriber_first_name" value="<?php echo $data->subscriber->data_nascimento ?>">

					<label>Activo:</label>
					<span>Sim</span>
					<input type="radio" name="is_active" <?php echo ($data->subscriber->is_active == 1) ? "checked=\"checked\"":"" ?> />

					<span>N&atilde;o</span>
					<input type="radio" name="is_active" <?php echo ($data->subscriber->is_active == 0) ? "checked=\"checked\"":"" ?> />
				</div>

				<div class="span4">
					<h4>Grupos</h4>

					<select multiple="multiple">
						<?php foreach($data->categories as $category): ?>
						<?php $selected = array_key_exists($category->id, $data->groups_ids) ? "selected='selected'":""; ?>
						<option <?php echo $selected ?> value="<?php echo $category->id ?>"><?php echo $category->categoria ?></option>
						<?php endforeach; ?>
					</select>
	
				</div>

				<div class="span4">
					<h4>Dados estatísticos</h4>
				</div>

			</div>

			<div class="clear"></div>

			<br />
			<div class="alignright">
				<a class="btn" href="?mod=newsletter&amp;view=subscribers">Voltar</a>
				<input class="btn btn-primary" type="submit" name="save" value="Inserir / Editar">
			</div>

		</form>
	</div>

</div>