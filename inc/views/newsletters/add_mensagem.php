<?php
	//Gravar ou não
	

	$id = (isset($_GET["id"]))?$_GET["id"]:-1;
	//var_dump($_SESSION);
	if ( isset($_POST["mensagem_id"]) ) {	//Já tivemos envio, então vamos buscar os dados que o utilizador colocou.
		$mensagem = $newsletters->initialize_mensagem_from_post();
		if( $mensagem->assunto=='' || strip_tags($mensagem->mensagem)==''){
			tools::notify_add( "Mensagem vazia", "Error" );
		}else{
			if($mensagem->id == -1){
				$newsletters->insert_mensagem($mensagem);
			}else{
				$newsletters->update_mensagem($mensagem);
			}
		}

	}elseif( isset($_GET["id"]) ) {
		$mensagem = $newsletters->initialize_mensagem( $_GET["id"] );
	}else{
		$mensagem = $newsletters->initialize_mensagem( );
	}

	if( $id != -1 ){
		if (!$_SESSION["user"]->is_admin) {
			//Verificar permissões
			$query = "select * from user_permissions_newsletter where id_user = ".$_SESSION["user"]->id.' and id_newsletter = '.$id;
			//echo $query;
			$res = mysql_query($query) or die(mysql_error());
			if (mysql_num_rows($res) == 0) {
				die("Newsletter não encontrada.");
			}
		}

	}



	?>
	<div class="main_div row-fluid">
		<?php if ( $id==-1 ) { ?>
		<h1>A adicionar newsletter</h1>
		<?php }else{ ?>
		<h1>A editar mensagem</h1>
		<?php } ?>
		<form action="?mod=newsletters&amp;view=add_mensagem" method="post" accept-charset="utf-8">



			<div class="span9 well">
				<label for="mensagem">Mensagem (Email)</label>
				<textarea class="ckeditor" name="mensagem"><?php echo $mensagem->mensagem; ?></textarea>

				<br />

				<label for="mensagem_browser">Mensagem (Browser)</label>
				<textarea class="ckeditor" name="mensagem_browser"><?php echo $mensagem->mensagem_browser; ?></textarea>


				<br />

				<div class="alert alert-info">O campo <strong>mensagem texto</strong>, permite a clientes de email apresentar uma mensagem alternativa à newsletter, quando a apresentação de conteúdo em HTML está desabilitado. O texto utilizado é também lido por filtros de SPAM e ao representar uma alternativa textual ao conteúdo da newsletter, aumenta a probabilidade de entrega. É recomendado o preenchimento deste campo com um texto sem formatação HTML.</div>

				<label for="mensagem_text">Mensagem texto</label>
				<textarea cols="80" rows="5" name="mensagem_text"><?php echo $mensagem->mensagem_text ?></textarea>

				<div class="clear"></div>

				<br />
				<br />

				<div class="pull-right">
					<a href="?mod=newsletter&view=messages" class="btn"><?php echo _('Voltar');?></a>
					<input type="submit" class="btn btn-success" name="submit" value="Inserir / Editar" />
					<a class="btn btn-primary" href="?mod=newsletter&amp;view=pre_send&amp;id=<?php echo $mensagem->id ?>">Preparar envio <i class="icon-white icon-share"></i></a>
				</div>
			</div>

			<div class="span3 well">
				<input type="hidden" name="mensagem_id" value="<?php echo $mensagem->id; ?>" />

				<label>Assunto: </label>
				<input maxlength="500" required="required" type="text" class="span12 required" name="assunto" value="<?php echo $mensagem->assunto; ?>" />
				<label>URL: </label>
				<input maxlength="128" required="required" type="text" class="span12" name="url" value="<?php echo $mensagem->url; ?>"/>

				<label>Garantir permiss&otilde;es a:</label>
				<?php $users_with_permission = $newsletters->get_users_with_permission_in_newsletter($mensagem->id) ?>
				<?php $users = $newsletters->get_users(); ?>

				<ul>
					<?php foreach ($users as $user): ?>
					<?php $checked = (@array_key_exists($user->id, $users_with_permission)) ? "checked=\"checked\"":""; ?>
					<?php if($user->id == $_SESSION["user"]->id) $force_checked = "checked=\"checked\""; else $force_checked = ""; ?>
					<li><input <?php echo $checked ?> <?php echo $force_checked; ?> type="checkbox" name="user_permissions[]" value="<?php echo $user->id?>" /> <?php echo $user->first_name . " " . $user->last_name ?></li>
				<?php endforeach ; ?>
			</ul>

			<label>Directrizes / Alias:</label>
			<dl>
				<dt>{ver_no_browser}</dt>
				<dd>Link de visualização on-line</dd>
				<dt>{remover_email}</dt>
				<dd>Link para remoção da mailing list</dd>
				<dt>{saudacao}</dt>
				<dd>Caro / Cara (dependendo do sexo definido)</dd>
				<dt>{idade}</dt>
				<dd>Idade do subscritor (dependendo da data nascimento definida)</dd>
				<dt>{campo:db_name}</dt>
				<dd>Campos <i>custom</i></dd>
			</dl>


			<div class="pull-right">
				<a href="?mod=newsletter&amp;view=messages" class="btn"><?php echo _('Voltar');?></a>
				<input type="submit" class="btn btn-success" name="submit" value="Guardar" />
				<a class="btn btn-primary" href="?mod=newsletter&amp;view=pre_send&amp;id=<?php echo $mensagem->id ?>">Preparar envio <i class="icon-white icon-share"></i></a>
			</div>

		</div>

	</form>
</div>

