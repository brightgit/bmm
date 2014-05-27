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

		<!-- All this data was moved to users -->
		<!-- h4>Sender</h4>

		<div class="well ">

		<div class="row-fluid">
			<div class="span4">

				<label>SMTP Host / IP</label>
				<input type="text" name="sender_host" value="<?php echo $this->settings->sender_host ?>" /> <i data-original-title="Defini&ccedil;&atilde;o essencial" class="tip icon icon-exclamation-sign"></i>

				<label>SMTP Port</label>
				<input type="text" name="sender_smtp_port" value="<?php echo $this->settings->sender_smtp_port ?>" /> <i data-original-title="Defini&ccedil;&atilde;o essencial" class="tip icon icon-exclamation-sign"></i>

				<label>Username</label>
				<input type="text" name="sender_username" value="<?php echo $this->settings->sender_username?>" /> <i data-original-title="Defini&ccedil;&atilde;o essencial" class="tip icon icon-exclamation-sign"></i>

				<label>Password</label>
				<input type="password" name="sender_password" value="<?php echo $this->settings->sender_password ?>" /> <i data-original-title="Defini&ccedil;&atilde;o essencial" class="tip icon icon-exclamation-sign"></i>

				<!-- label>API Key</label>
				<input type="text" name="sender_api_key" value="<?php echo $this->settings->sender_api_key ?>" /> <i data-original-title="Defini&ccedil;&atilde;o essencial" class="tip icon icon-exclamation-sign"></i -->

			<!-- /div>

			<div class="span4">

				<label>Name</label>
				<input type="text" name="sender_name" value="<?php echo $this->settings->sender_name ?>" />

				<label>E-mail from</label>
				<input type="text" name="sender_email_from" value="<?php echo $this->settings->sender_email_from ?>" /> <i data-original-title="Defini&ccedil;&atilde;o essencial" class="tip icon icon-exclamation-sign"></i>

				<!-- label>Return path</label>
				<input type="text" name="return_path" value="<?php echo $this->settings->return_path ?>" /> <i data-original-title="Defini&ccedil;&atilde;o essencial" class="tip icon icon-exclamation-sign"></i>

				<label>Return path password</label>
				<input type="password" name="return_path_password" value="<?php echo $this->settings->return_path_password ?>" /> <i data-original-title="Defini&ccedil;&atilde;o essencial" class="tip icon icon-exclamation-sign"></i -->

				<!-- label>Domain</label>
				<input type="text" name="sender_domain" value="<?php echo $this->settings->sender_domain ?>" />

			</div>

			<div class="span4">
				<label>Alternate / manual removal email</label>
				<input type="text" name="alternate_remove_email" value="<?php echo $this->settings->alternate_remove_email ?>" />

				<label>Base path</label>
				<input type="text" name="base_path" value="<?php echo $this->settings->base_path ?>" />
			</div>

			</div>

			<div class="clear"></div>

		</div -->

		<h4>Outros</h4>

		<div class="well ">
		
			<div class="span6">

				<label>CK upload url</label>
				<input type="text" name="ck_upload_url" value="<?php echo $this->settings->ck_upload_url ?>" />

				<label>CK upload dir</label>
				<input type="text" name="ck_upload_dir" value="<?php echo $this->settings->ck_upload_dir ?>" />

				<!-- label>Swift absolute path</label>
				<input type="text" name="swift_absolute_path" value="<?php echo $this->settings->swift_absolute_path ?>" /> <i data-original-title="Defini&ccedil;&atilde;o essencial" class="tip icon icon-exclamation-sign"></i -->

			</div>

			<!-- div class="span6">
				
				<label>Remove bounces</label>
				<?php $checked_yes = ($this->settings->remove_bounces == 1) ? "checked=\"checked\"" : "" ?>
				<?php $checked_no = ($this->settings->remove_bounces == 0) ? "checked=\"checked\"" : "" ?>
				Sim <input <?php echo $checked_yes ?> type="radio" name="remove_bounces" value="1">
				N&atilde;o <input <?php echo $checked_no ?> type="radio" name="remove_bounces" value="0">

				<label>Remove bounces count</label>
				<input type="text" name="remove_bounces_count" value="<?php echo $this->settings->remove_bounces_count ?>" />

				<label>Unsubscribe automatically</label>
				<?php $checked_yes = ($this->settings->unsubscribe_automatically == 1) ? "checked=\"checked\"" : "" ?>
				<?php $checked_no = ($this->settings->unsubscribe_automatically == 0) ? "checked=\"checked\"" : "" ?>
				Sim <input <?php echo $checked_yes ?> type="radio" name="unsubscribe_automatically" value="1">
				N&atilde;o <input <?php echo $checked_no ?> type="radio" name="unsubscribe_automatically" value="0">

			</div>

			<div class="clear"></div>

		</div -->

		<h4>Dados de configura&ccedil;&atilde;o</h4>

		<div class="well">
			<div class="span6">
				<label>CRON</label>
				<input type="text" value="curl --silent --compressed curl http://brightminds.pt/bmm/inc/send_email.php?api=bright91 &gt; /dev/null 2&gt;&amp;1"/>
				<label>Base path</label>
				<input type="text" value="<?php echo Core::base_path() ?>" />
			</div>

			<!-- div class="span6">
				<label>Logo:</label>
				<img src="<?php echo base_path() ?>/inc/img/admin/client_logo.png" class="img-polaroid" />
				<br />
				<!--input type="file" name="client_logo" /-->
			<!-- /div -->

			<div class="clear"></div>
		</div>

		<input type="submit" class="btn btn-success" value="Alterar" />

	</form>
