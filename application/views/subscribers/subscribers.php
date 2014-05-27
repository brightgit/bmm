	<?php
		//get info
	$list = $subscribers->getSubscribers();
	?>

	<div class="main_div">
		<h1>Subscritores da Newsletter</h1>
		<?php 
		//var_dump($subscribers->invalid_emails);

		if(count($subscribers->invalid_emails) > 0 ){
			$invalid_emails = $subscribers->invalid_emails;

			$total_invalid_emails = count($invalid_emails);
			$total_emails = $total_invalid_emails + $total_valid_emails;
			
			tools::notify_add( "Foram identificados <b>".$total_emails."</b> endere&ccedil;os no ficheiro seleccionado.", "info" );

			//mostrar os e-mails que não foram inseridos para o utilizador os corrigir manualmente se possível
				$invalid_emails_html = implode("<br />", $invalid_emails); ?>

				<div class="alert alert-danger"><b><?php echo $total_invalid_emails ?></b> e-mails foram considerados inv&aacute;lidos</div>

				<div class="accordion" id="accordion2">
					<div class="accordion-group">
						<div class="accordion-heading">
						  <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseOne">
							Ver e-mails inv&aacute;lidos:
						  </a>
						</div>
						<div id="collapseOne" class="accordion-body collapse" style="height: 0px;">
						  <div class="accordion-inner">
							<?php echo $invalid_emails_html ?>
						  </div>
						</div>
			  		</div>
				</div>

			<?php
			
		}



				//como os grupos são um array do tipo grupo[id_grupo] = "Nome grupo", iterar com for i = 0
		if($_SESSION["user"]->is_admin)
			$grupos = $subscribers->get_admin_group_permissions();
		else
			$grupos = $subscribers->get_group_permissions($_SESSION["user"]->id);

		if($grupos){
			$keys = array_keys($grupos);
			$index = $keys[0];
		}

		

		?>

		<?php if(!empty($grupos[$index])): ?>
		<?php $active = Tools::check_subscriber_tab();  ?>
		<?php $control = 0; ?>

		
		<ul class="nav nav-tabs" id="tabThis">
			<?php foreach($grupos as $i => $grupo): ?>
			<?php $active_string = ($active == $i) ? "active" : ""; ?>
			<?php 
			if(empty($active) && $control == 0)
					$fall_back_active = "active"; //primeira tab - big ball of
				else
					$fall_back_active = "";
				?>

				<li class="<?php echo $active_string ?> <?php echo $fall_back_active ?> "><a href="#grupo<?php echo $i ?>"><?php echo $grupos[$i] ?> </a></li>
				<?php $control++; ?>
			<?php endforeach; ?>
			<li class="exclusion"><a href="#excluded">Lista de exclus&atilde;o</a></li>
		</ul>

		<div class="tab-content">
			<?php $control = 0; ?>
			<?php foreach($grupos as $i => $grupo): ?>

			<?php $active_string = ($active == $i) ? "active" : ""; ?>
			<?php 
			if(empty($active) && $control == 0)
					$fall_back_active = "active"; //primeira tab
				else
					$fall_back_active = "";
				?>
				<?php $control++; ?>
				<div class="tab-pane <?php echo $active_string ?> <?php echo $fall_back_active ?> " id="grupo<?php echo $i ?>">

					<a href="#" class="add-new-news btn btn-success" onclick="$('.add-newsletter').fadeToggle(); $('.add-file').hide();"><i class="icon-white icon-plus-sign"></i> <?php echo _('Adicionar subscritor');?></a>
					<!--a href="#" class="add-new-news btn btn-success" onclick="$('.add-file').fadeToggle(); $('.add-newsletter').hide();"><i class="icon-white icon-list-alt"></i> <?php echo _('Importar ficheiro');?></a-->
					<a href="#" class="add-new-news btn btn-success" onclick="$('.add-file').fadeToggle(); $('.add-newsletter').hide();"><i class="icon-white icon-list-alt"></i> <?php echo _('Importar ficheiro');?></a>
					<div class="clear"></div>
					<br /> 

					<form method="post" action="index.php?mod=subscribers&view=add_subscriber" enctype="multipart/form-data">
						<div class="add-newsletter">
							<div>
								<label for="nome">Nome</label>
								<input type="text" name="nome" id="nome"/>
							</div>
							<div>
								<label for="email">Email</label>
								<input type="email" name="email" id="email"/>
							</div>
							<div>
								<label for="is_active">Activo?</label>
								<select name="is_active" id="is_active">
									<option selected="selected" value="1">Sim</option>
									<option value="0">Não</option>
								</select>
							</div>
							<br />
							<input type="hidden" name="group_id" value="<?php echo $i ?>" />
							<input type="submit" class="btn btn-primary" name="add-subscriber" value="Adicionar"/>
						</div>
					</form>

					<form method="post" action="index.php?mod=subscribers&view=import_file" enctype="multipart/form-data">

						<!-- Importar de csv -->
						<div class="add-file" style="display:none;">
							<br />
							<!--p>O ficheiro CSV dever&aacute; respeitar a estrutura <i><b>nome_subscritor</b></i>,<i><b>email_subscritor</b></i></p-->

							<div>
								<label for="nome">Ficheiro</label>
								<input type="hidden" name="csv_group_id" value="<?php echo $i ?>" />
								<input type="file" name="csv" />
							</div>
							<br />
							<input type="submit" class="btn btn-primary" name="add-subscriber-file" value="Adicionar"/>
						</div>
					</form>
						<!-- importar de csv -->
					<form method="post" action="index.php?mod=subscribers&view=multiple_action" enctype="multipart/form-data">

						<h2>A listar subscritores de <?php echo $grupos[$i]; ?></h2>

						<table cellpadding="0" cellspacing="0" width="100%" id="" class="table subscribers-table" data-group-id="<?php echo $i ?>" >
							<thead>
								<tr>
									<th class="rtl nosort nosearch">
										<input type="checkbox" name="select_items">
									</th>
									<th>
										<?php echo _('ID'); ?>
									</th>
									<th>
										<?php echo _('Email'); ?>
									</th>
									<th>
										<?php echo _('Bounces'); ?>
									</th>
									<th class="rtr nosearch">
										<?php echo _('Activo'); ?>
									</th>
							<!--th class="rtr nosort nosearch">
								<?php echo _('Categorias'); ?>
							</th-->
						</tr>
					</thead>
				</table>
				<div class="doActions form-inline">
					<select name="action">
						<option value="">--</option>
						<option value="delete">Apagar</option>
						<option value="activate">Activar</option>
						<option value="deactivate">Desactivar</option>
						<?php 
						if ( $_SESSION["user"]->is_admin ) {
							$query = "SELECT * FROM `newsletter_categorias`";
						}else{
							$query = "SELECT * FROM `newsletter_categorias` left join user_permissions on user_permissions.group_id = newsletter_categorias.id 
							where user_permissions.user_id = ".$_SESSION["user"]->id;
						}
						$res = mysql_query($query) or die(mysql_error());
						while( $row = mysql_fetch_array($res) ) {
							echo '<option value="add_'.$row['id'].'">Adicionar todos a: '.$row['categoria'].'</option>';
							echo '<option value="remove_'.$row['id'].'">Remover todos de: '.$row['categoria'].'</option>';
						}
						?>
					</select>
					<input class="btn" type="submit" value="<?php echo _('Submeter');?>"/>
				</div>
			</form>

		</div>
	<?php endforeach; ?>

	<div class="tab-pane" id="excluded">

		<div class="alert-large alert alert-info">A lista de exclusão é constituida por endereços que solicitaram remoção da mailing list, ou pelos endereços que foram excluídos através da detecção automática de bounces (<b><?php echo $this->settings->remove_bounces_count ?></b> e-mails de retorno).<br />Pode alterar esta op&ccedil;&atilde;o, visitando a p&aacute;gina de <a href="?mod=settings">defini&ccedil;&otilde;es</a></div>

		<div class="well">
			<h2>Adicionar endere&ccedil;os &agrave; lista de exclus&atilde;o</h2>
			<p>Os endere&ccedil;os adicionados a esta lista jamais receber&atilde;o mensagens independentemente do grupo seleccionado</p>
			<form name="" action="index.php?mod=subscribers&view=add_to_exclusion" method="post">
				<textarea rows="4" cols="24" class="textarea" name="add_to_exclusion"></textarea>
				<br />
				<br />
				<input class="btn btn-primary" type="submit" name="add_to_exclusion_submit" value="Adicionar &agrave; lista de exclus&atilde;o" />
			</form>
		</div>
		

		<table cellpadding="0" cellspacing="0" width="100%" id="" class="table exclusions-table">
			<thead>
				<tr>
					<th>
						<?php echo _('ID'); ?>
					</th>
					<th>
						<?php echo _('Email'); ?>
					</th>
					<th>
						<?php echo _('Bounces'); ?>
					</th>
					<th class="rtr nosearch">
						<?php echo _('Activo'); ?>
					</th>
				</tr>
			</thead>
		</table>
	</div>



</div>

<?php else: ?>

	<div class="alert alert-info">Não existem subscritores ou não tem permissão sobre qualquer grupo de subscritores</div>

<?php endif; ?>

<?php
if ($list != false) {
	?>
	<div class="clear"></div>
		<!--form method="post" action="" enctype="multipart/form-data">
			<table cellpadding="0" cellspacing="0" width="100%" id="" class="table subscribers-table">
				<thead>
					<tr>
						<th class="rtl nosort nosearch">
							<input type="checkbox" name="select_items">
						</th>
						<th>
							<?php echo _('ID'); ?>
						</th>
						<th>
							<?php echo _('Nome'); ?>
						</th>
						<th>
							<?php echo _('Email'); ?>
						</th>
						<th class="rtr nosort nosearch">
							<?php echo _('Activo'); ?>
						</th>
						<th class="rtr nosort nosearch">
							<?php echo _('Categorias'); ?>
						</th>
					</tr>
				</thead>
			</table>
			<div class="doActions form-inline">
				<select name="action">
					<option value="">--</option>
					<option value="delete">Apagar</option>
					<option value="activate">Activar</option>
					<option value="deactivate">Desactivar</option>
					<?php 
					$query = "SELECT * FROM `newsletter_categorias`";
					$res = mysql_query($query) or die(mysql_error());
					while( $row = mysql_fetch_array($res) ) {
						echo '<option value="add_'.$row['id'].'">Adicionar todos a: '.$row['categoria'].'</option>';
						echo '<option value="remove_'.$row['id'].'">Remover todos de: '.$row['categoria'].'</option>';
					}
					?>
				</select>
				<input class="btn" type="submit" value="<?php echo _('Submeter');?>"/>
			</div>
		</form-->
		<?php
			}//if results
			else { ?>
			<div class="clear"></div>
			<div class="error"><?php echo _('Sem resultados') ?></div>
			<?php } ?>
			<div class="clear"></div>
		</div>
		<?php
