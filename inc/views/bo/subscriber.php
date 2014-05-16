<div class="content">

	<pre>
		<?php var_dump($data) ?>
	</pre>

	<h1>A editar subscritor</h1>

	<a class="btn" href="?mod=newsletter&amp;view=subscribers">Voltar</a>

	<br>
	<br>

	<div class="well">
		<form action="" method="post" novalidate="novalidate">

			<label>Nome:</label>
			<input class="seamless-input" type="text" name="subscriber_username" value="<?php echo $data->nome ?>">

			<label>Email:</label>
			<input class="seamless-input" type="text" name="subscriber_first_name" value="<?php echo $data->email ?>">

			<label>Sexo:</label>
			<input class="seamless-input" type="text" name="subscriber_first_name" value="<?php echo $data->email ?>">

			<label>Telefone:</label>
			<input class="seamless-input" type="text" name="subscriber_telefone_1" value="<?php echo $data->telefone_1 ?>">

			<label>Telefone (alternativo):</label>
			<input class="seamless-input" type="text" name="subscriber_telefone_1" value="<?php echo $data->telefone_2 ?>">

			<label>Data nascimento:</label>
			<input class="seamless-input" type="text" name="subscriber_first_name" value="<?php echo $data->data_nascimento ?>">
			
			<label>Activo:</label>
			


			<input class="btn btn-primary" type="submit" name="save" value="Inserir / Editar">
			<a class="btn" href="?mod=newsletter&amp;view=subscribers">Voltar</a>

		</form>
	</div>

</div>