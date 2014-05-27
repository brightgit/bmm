<?php
		if(!empty($_POST))
			$settings_list->save();

		?>

	<h1>Defini&ccedil;&otilde;es</h1>

	<div class="alert alert-warning">
		<span>Est&atilde;o abaixo defini&ccedil;&otilde;es essenciais ao funcionamento do sistema. As definições assinaladas  devem ser apenas alteradas pelo fornecedor do servi&ccedil;o pois o seu valor incorrecto poder&aacute; suspender a funcionalidade da aplica&ccedil;&atilde;o</span>
	</div>

	<form action="index.php?mod=settings_list" method="post" enctype="multipart/form-data">

		<?php $senders = $settings_list->get_senders(); ?>
		<h4>Remetentes</h4>

		<div class="well">
			<div class="row-fluid">
				<div class="span12">
					<table class="table background-white senders-table">
						<tr>
							<th>Email</th>
							<th>Remetente</th>
							<th>Return Path</th>
							<th>Password</th>
							<th></th>
						</tr>
						<?php foreach ($senders as $sender): ?>
						<tr>
							<td><input type="text" name="sender[<?php echo $sender->id ?>][email]" class="seamless-input" value="<?php echo $sender->email ?>" /></td>
							<td><input type="text" name="sender[<?php echo $sender->id ?>][email_from]" class="seamless-input" value="<?php echo $sender->email_from ?>" /></td>
							<td><input type="text" name="sender[<?php echo $sender->id ?>][return_path]" class="seamless-input" value="<?php echo $sender->return_path ?>" /></td>
							<td><input type="password" name="sender[<?php echo $sender->id ?>][return_path_password]" class="seamless-input" value="<?php echo $sender->return_path_password ?>" /></td>
							<td>
								<div class="table-actions">
									<a class="link-confirm btn btn-small btn-danger" href="?mod=settings_list&amp;view=delete_sender&amp;id=<?php echo $sender->id ?>"><i class="icon-white icon-remove"></i></a>
								</div>
							</td>
						</tr>
						<?php endforeach ?>
						<tr>
							<td colspan="4" class="align-center"><button class="btn btn-primary btn-sender-add" type="button"><i class="icon-white icon-plus-sign"></i> Adicionar novo</button></td>
						</tr>
					</table>
				</div>
			</div>
		</div>

		<input type="submit" class="btn btn-success" value="Alterar" />

	</form>
