<?php

class ViewNewsletter {

	private $mod = '';
	private $core = '';
	private $debug = '';
	private $tools = '';
	private $modName = '';
	private $option = '';
	private $ckeditor = '';

	function __construct($mod, $core, $params) {

		$this->setMod($mod);
		$this->setCore($core);
		$this->setTools($core->getTools());
		$this->setCkEditor();

		$this->modName = 'newsletter';
		//Adicionar newsletter
		if(isset($_POST['mensagem_id'])){
			$mensagem = $this->mod->initialize_mensagem_from_post();
			if( $mensagem->assunto=='' || strip_tags($mensagem->mensagem)==''){
				$this->add_mensagem(-1, $mensagem);
				return false;
			}else{
				if($mensagem->id == -1){
					$this->mod->insert_mensagem($mensagem);
				}else{
					$this->mod->update_mensagem($mensagem);
				}
			}
		}
		

		if(isset($_POST['action']) && $_POST['action']!=''){
			if( $_POST['action'] == 'delete' || $_POST['action'] == 'activate' || $_POST['action'] == 'deactivate' ){
				if ($_POST["action"] == "delete" ) {
					$query = "delete from subscriber_by_cat where id_subscriber IN (".implode($_POST["items"], ",") . ")";
					mysql_query($query) or die( mysql_error() );
				}
				$this->getTools()->doAction('subscribers',$_POST['action'],$_POST['items'],0,0);
			}else{
				$action = $_POST['action'];
				$action_a = explode("_", $action);
				$items = $_POST['items'];
				if( $action_a[0] == 'add' ) {
					$query = "DELETE FROM `subscriber_by_cat` WHERE `id_categoria` = '".$action_a[1]."'";
					mysql_query( $query ) or die( mysql_error() );
					$i = 0;
					while( isset($items[$i]) ) {
						$query = "INSERT INTO `subscriber_by_cat` (`id_subscriber`, `id_categoria`) VALUES ('".$items[$i]."', '".$action_a[1]."')";
						mysql_query($query) or die(mysql_error());
						$i++;
					}
				}elseif($action_a[0] == 'remove'){
					$query = "DELETE FROM `subscriber_by_cat` WHERE `id_categoria` = '".$action_a[1]."'";
					mysql_query( $query ) or die( mysql_error() );
				}
			}
		}

		if(isset($_POST['add-subscriber']))
			$mod->save();
		if(isset($_POST['add-subscriber-file'])){			
			$mod->import_file();
		}
		if(isset($_POST["add_to_exclusion"]))
			$mod->add_to_exclusion();


		if($_GET["view"] == "statistics"){
			if( isset($_GET['id']) ) {
				$this->show_statistics( $_GET['id'] );
			}else{
				$this->show_statistics_general( );
			}
		}
		elseif($_GET['view'] == 'messages')
			$this->showMessages();
		elseif($_GET['view'] == 'utilizadores'){
			$this->show_utilizadores();
		}elseif($_GET['view'] == 'add_mensagem'){
			if(isset($_GET['id'])){
				$this->add_mensagem($_GET['id']);
			}else{
				$this->add_mensagem();
			}
		}elseif($_GET['view'] == 'pre_send'){
			$this->pre_send($_GET['id']);
		}elseif($_GET['view'] == 'pre_visualizar'){
			$this->pre_visualizar($_GET['id']);
		}elseif($_GET['view'] == 'enviar'){
			$this->enviar($_GET['id']);
		}elseif($_GET['view'] == 'categorias'){
			if( isset($_POST['submit_add_categoria']) ) {
				$this->mod->add_categoria();
			}
			$this->categorias();
		}else{
			$this->show();
		}
	}

	function __destruct() {
		$this->login = null;
		unset($this->login);
		$this->ckeditor = null;
		unset($this->ckeditor);
	}

	public function get_sender_info($sender_id){
		$sql = "SELECT * FROM senders WHERE id = " . $sender_id;
		$query = mysql_query($sql);
		return $result = mysql_fetch_object($query);
	}

	public function get_senders(){
		$sql = "SELECT * from senders";
		$query = mysql_query($sql);

		if($query){
			while ($row = mysql_fetch_object($query))
				$output[] = $row;
			
			return $output;
		}


		return false;
	}


	public function get_grupos(){

		$sql = "SELECT * from newsletter_categorias";
		$query = mysql_query($sql);

		if($query){
			while ($row = mysql_fetch_object($query))
				$grupos[] = $row;
			
			return $grupos;
		}


		return false;
	}

	public function get_grupo($id){

		$sql = "SELECT * from newsletter_categorias WHERE id = ".($id);
		$query = mysql_query($sql);

		if($query)
			$group = mysql_fetch_object($query);

		return $group;
	}

	public function get_subscribers_from_group($id){
		$sql = "SELECT s.id, s.nome, s.email, s.is_active, s.soft_bounces_count, s.hard_bounces_count FROM subscriber_by_cat sbc INNER JOIN subscribers s ON sbc.id_subscriber = s.id WHERE id_categoria = ".$id;
		$query = mysql_query($sql);

		if($query){
			while ($row = mysql_fetch_object($query))
				$output[] = $row;

			return $output;
		}


		return false;
	}

	public function remove_message($id){
		$sql = "DELETE FROM mensagens WHERE id = ".$id;
		$query = mysql_query($sql);

		echo "<div class=\"alert alert-success\">Newsletter removida com sucesso.</div>";

		return true;
	}

	public function duplicate_message($id){
		$sql = "INSERT INTO mensagens (`id`, `url`, `assunto`, `mensagem_text`, `mensagem_browser`, `mensagem`, `estado`, `estado_code`) ( SELECT  NULL, `url`, `assunto`, `mensagem_text`, `mensagem_browser`, `mensagem`, `estado`, `estado_code` FROM mensagens WHERE id = {$id})";
		$query = mysql_query($sql);

		//buscar o id da newsletter replicada
		$sql = "SELECT MAX(id) AS id FROM mensagens";
		$query = mysql_query($sql);

		if($query){
			$mensagem = mysql_fetch_object($query);

			$q = "SELECT * FROM user_permissions_newsletter where id_newsletter = ".$id;			
			$r = mysql_query($q) or die(mysql_error());
			while( $row = mysql_fetch_object($r) ) {
				$i = "insert into user_permissions_newsletter values ( '".$row->id_user."', '".$mensagem->id."' )";
				mysql_query($i) or die(mysql_error());
			}

			echo "<div class=\"alert alert-success\">A newsletter foi duplicada com sucesso. O seu n&uacute;mero actual &eacute; <b>#".$mensagem->id."</b>. Pode agora <a href=\"?mod=newsletter&view=add_mensagem&id=".$mensagem->id."\"><b>editar a newsletter duplicada</b></a> </div>";
		}

		else
			return false;
	}

	public function update_grupo($id){
		$nome = $_POST["group_name"];
		$is_default = $_POST["is_default"];

		$sql = "UPDATE `newsletter_categorias` SET `categoria` = '{$nome}', `is_default` = {$is_default} WHERE id = {$id}";
		$query = mysql_query($sql);

		echo "<div class=\"alert alert-success\">Grupo actualizado com sucesso.</div>";

		return true;
	}

	//listar todos os utilizadores
	public function get_users(){
		$sql = "SELECT u.*, ug.is_admin FROM users u JOIN user_groups ug ON u.user_group = ug.id;";
		$query = mysql_query($sql);
		
		if($query){
			while ($row = mysql_fetch_object($query))
				$output[] = $row;

			return $output;
		}

		return false;			

	}

	//listar os grupos a quais os utilizadores têm permissão
	public function get_admin_group_permissions(){

		//um utilizador is_admin tem acesso a tudo
		$sql = "SELECT nc.id AS categoria_id, nc.categoria AS categoria_nome from newsletter_categorias nc";	
		$query = mysql_query($sql);

		if($query){
			while ($row = mysql_fetch_object($query))
				$groups[$row->categoria_id] = $row->categoria_nome;

			return $groups;
		}

		return false;

	}

	//listar os grupos a quais os utilizadores têm permissão
	public function get_group_permissions($user_id){

		//um utilizador is_admin tem acesso a tudo
		$sql = "SELECT nc.id AS categoria_id, nc.categoria AS categoria_nome from user_permissions up left join users u ON u.id = up.user_id left join newsletter_categorias nc ON up.group_id = nc.id where up.user_id = ".$user_id;	
		$query = mysql_query($sql);

		if($query){
			while ($row = mysql_fetch_object($query))
				$groups[$row->categoria_id] = $row->categoria_nome;

			return $groups;
		}

		return false;

	}

	//listar os grupos a quais os utilizadores têm permissão
	public function get_sender_permissions($user_id){

		//um utilizador is_admin tem acesso a tudo
		$sql = "SELECT s.id, s.email, s.`email_from` FROM user_sender_permissions usp
		LEFT JOIN users u ON u.id = usp.user_id
		LEFT JOIN senders s ON s.id = usp.sender_id
		WHERE usp.user_id = ".$user_id;	

		$query = mysql_query($sql);

		if($query){
			while ($row = mysql_fetch_object($query))
				$output[$row->id] = array("email_from" => $row->email_from, "email" => $row->email);

			return $output;
		}

		return false;

	}


	public function render_group_permissions($is_admin, $groups){

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

	function get_users_with_permission_in_group($group_id){
		$sql = "SELECT u.id, u.first_name, u.last_name, u.username from user_permissions up inner join users u ON u.id = up.user_id WHERE group_id = {$group_id}";
		$query = mysql_query($sql);

		if($query){
			while ($row = mysql_fetch_object($query))
				$users[] = $row;

			return $users;
			
		}

		return false;

	}

	public function show_grupo($id){

		//há necessidade de update?
		if($_POST["update_group"])
			$this->update_grupo($id);

		//informação do grupo
		$grupo = $this->get_grupo($id); ?>

		<div class="well">

			<h1>A editar grupo</h1>

			<form method="post" action="">
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
				<a href="?mod=newsletter&amp;view=categorias" class="btn">Voltar</a>
			</form>

		</div>

		<h2>Utilizadores com permiss&atilde;o sobre este grupo</h2>

		<?php $users_with_permission = $this->get_users_with_permission_in_group($grupo->id) ?>

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
					<td><a href="?mod=newsletter&amp;view=utilizadores&amp;id=<?php echo $user->id ?>"><?php echo $user->first_name . " " . $user->last_name ?></a></td>
					<td><?php echo $user->username ?></td>
				</tr>
				
			<?php endforeach; ?>
			
		</tbody>
	</table>

<?php else: ?>

	<div class="alert alert-info">N&atilde;o existem utilizadores com permiss&atilde;o sobre este grupo (excepto o(s) <b>Administrador(es))</b></div>

<?php endif; ?>

<?php
}

public function remove_grupo($id){
	$sql = "DELETE FROM newsletter_categorias WHERE id = {$id}";
	$query = mysql_query($sql);

	$sql2 = "DELETE from subscriber_by_cat where id_categoria = ".$id;
	mysql_query($sql2);

	echo "<div class=\"alert alert-success\">Grupo removido com sucesso</div>";
}

public function categorias() {

	if(isset($_GET["remove"]))
		$this->remove_grupo($_GET["remove"]);

	if(isset($_GET["id"])):
		$this->show_grupo($_GET["id"]);

	else:
		?>

	<div class="categorias_add">

		<div style="margin-top:30px; display:none;" id="categoria_form">
			<form action="?mod=newsletter&amp;view=categorias" method="post">
				<div>
					<label for="categoria_nome">Nome do grupo</label>
					<input type="text" name="categoria_nome" id="categoria_nome" placeholder="Nome do grupo" required="required" maxlength="500" />
				</div>
				<div style="margin-top:10px;">
					<label>É adicionado por defeito:</label>
					<div>
						<input style="display:inline-block;" type="radio" name="defeito" value="1" id="defeito_sim" palceholder="lalallala" />
						<label style="display:inline-block;" for="defeito_sim">Sim</label>
					</div>
					<div>
						<input style="display:inline-block;" type="radio" name="defeito" checked="checked" value="0" id="defeito_nao" palceholder="lalallala" />
						<label style="display:inline-block;" for="defeito_nao">Não</label>
					</div>
				</div>
				<div style="margin-top:10px;">
					<label>Adicionar todos os subscritores a este grupo:</label>
					<div>
						<input style="display:inline-block;" type="radio" name="add_subs" value="1" id="add_subs_sim" palceholder="lalallala" />
						<label style="display:inline-block;" for="add_subs_sim">Sim</label>
					</div>
					<div>
						<input style="display:inline-block;" type="radio" name="add_subs" checked="checked" value="0" id="add_subs_nao" palceholder="lalallala" />
						<label style="display:inline-block;" for="add_subs_nao">Não</label>
					</div>
				</div>
				<div>
					<br />
					<input class="btn btn-primary" type="submit" name="submit_add_categoria" value="Adicionar grupo" />
				</div>
			</form>
		</div>

	</div>


	<h1>Grupos</h1>

	<?php if($_SESSION["user"]->is_admin): ?>
	<p class="btn btn-success" onclick="$('#categoria_form').toggle();"><i class="icon-white icon-plus-sign"></i> Adicionar grupo</p>
	<br />
	<br />
<?php endif; ?>

<?php 

if($_SESSION["user"]->is_admin)
	$query ="SELECT *, (SELECT COUNT(DISTINCT(id_subscriber)) FROM subscriber_by_cat sc WHERE sc.id_categoria = nc.id) AS total FROM `newsletter_categorias` nc;";
else
	$query = "SELECT nc.*, up.*, (SELECT COUNT(DISTINCT(id_subscriber)) FROM subscriber_by_cat sc WHERE sc.id_categoria = nc.id) AS total FROM `newsletter_categorias` nc INNER JOIN user_permissions up ON up.group_id = nc.id WHERE user_id = {$_SESSION["user"]->id}";

$res = mysql_query( $query ) or die( mysql_error() );
$i = 0;
?>

<?php if(mysql_num_rows($res) > 0): ?>

	<table id="datasort" class="table">
		<thead>
			<th>Grupo</th>
			<th>Data de Cria&ccedil;&atilde;o</th>
			<th>Total de subscritores</th>
			<th>A&ccedil;&otilde;es</th>
		</thead>
		<tbody>

			<?php while( $row = mysql_fetch_object($res) ): $total += $row->total; ?>
			<tr>
				<td><a href="?mod=newsletter&amp;view=categorias&amp;id=<?php echo $row->id ?>"><?php echo $row->categoria ?></a></td>
				<td><?php echo $row->date_created ?></td>
				<td><?php echo $row->total ?></td>
				<td><a class="btn btn-danger" href="?mod=newsletter&amp;view=categorias&amp;remove=<?php echo $row->id?>"><i class="icon-white icon-remove"></i> Remover</a></td>
			</tr>
		<?php endwhile; ?>
	</tbody>
</table>

<?php else: ?>
	
	<div class="alert alert-info">N&atilde;o existem grupos definidos ou n&atilde;o tem permiss&atilde;o para os visualizar.</div>
	
<?php endif; ?>

<?php
endif;
}


function enviar($id = -1){
	if($id == -1){
		echo '<div class="error">Não encontrado.</div>';
		echo '<meta http-equiv="refresh" content="1; url=?mod=newsletter&view=messages">';
		die('');
	}

	$mensagem = $this->mod->get_mensagem_by_id($id);
	if(!$mensagem){
		echo '<div class="error">Erro: Mensagem não encontrada.</div>';
		echo '<meta http-equiv="refresh" content="1; url=?mod=newsletter&view=messages">';
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

	<form action="?mod=mass_email" method="post">
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
				
				
				<?php $sender_permissions = $this->get_sender_permissions($_SESSION["user"]->id) ?>

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
<?php
}


function pre_visualizar($id = -1){
	if(1){
		echo '<div class="alert alert-error">Em falta: template de newsletter Rar&iacute;ssimas.</div>';
		echo '<meta http-equiv="refresh" content="3; url=?mod=newsletter&view=messages">';
		die('');
	}
	$mensagem = $this->mod->get_mensagem_by_id($id);
	if(!$mensagem){
		echo '<div class="error">Erro: Mensagem não encontrada.</div>';
		echo '<meta http-equiv="refresh" content="1; url=?mod=newsletter&view=messages">';
	}
	?>
	<h1>A pré-visualiar mensagem</h1>
	<ul class="form-ul">
		<li class="form-li">
			<span class="label">Assunto</span>
			<?php echo $mensagem->assunto; ?>
		</li>
		<li class="form-li">
			<span class="label">Mensagem</span>
			<?php echo $mensagem->mensagem; ?>
		</li>
	</ul>
	<?php

}

//listar os grupos a quais os utilizadores têm permissão
public function get_users_with_permission_in_newsletter($newsletter_id){

	//um utilizador is_admin tem acesso a tudo
	$sql = "SELECT u.first_name, u.last_name, upn.id_user FROM user_permissions_newsletter upn RIGHT JOIN users u ON upn.id_user = u.id where id_newsletter = {$newsletter_id}";
	$query = mysql_query($sql);

	if($query){
		while ($row = mysql_fetch_object($query))
			$users[$row->id_user] = $row->first_name . $row->last_name;

		return $users;
	}

	return false;

}


function add_mensagem($id = -1, $mensagem = false){
	//var_dump($_SESSION);
	if(!$mensagem){
		$mensagem = $this->mod->initialize_mensagem($id);
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
	<div class="main_div">
		<?php if ( $id==-1 ) { ?>
		<h1>A adicionar newsletter</h1>
		<?php }else{ ?>
		<h1>A editar mensagem</h1>
		<?php } ?>
		<form action="?mod=newsletter&amp;view=messages" method="post" accept-charset="utf-8">
			<input type="hidden" name="mensagem_id" value="<?php echo $mensagem->id; ?>" />

			<a href="?mod=newsletter&amp;view=messages" class="btn"><?php echo _('Voltar');?></a>
			<a class="btn btn-primary" href="?mod=newsletter&amp;view=pre_send&amp;id=<?php echo $mensagem->id ?>">Preparar envio <i class="icon-white icon-share"></i></a>
			<input type="submit" class="btn btn-success" name="submit" value="Inserir / Editar" />
			<br />

			<label for="assunto">Assunto: </label>
			<input id="assunto" maxlength="500" required="required" type="text" class="input-long required" name="assunto" value="<?php echo $mensagem->assunto; ?>" />
			<!--label for="url">Url: </label>
			<input id="url" maxlength="128" required="required" type="text" class="input-long required" name="url" value="<?php echo $mensagem->url; ?>"/-->

			<label>Garantir permiss&otilde;es a:</label>
			<?php $users_with_permission = $this->get_users_with_permission_in_newsletter($mensagem->id) ?>
			<?php $users = $this->get_users(); ?>

			<ul>
				<?php foreach ($users as $user): ?>
				<?php $checked = (@array_key_exists($user->id, $users_with_permission)) ? "checked=\"checked\"":""; ?>
				<?php if($user->id == $_SESSION["user"]->id) $force_checked = "checked=\"checked\""; else $force_checked = ""; ?>
				<li><input <?php echo $checked ?> <?php echo $force_checked; ?> type="checkbox" name="user_permissions[]" value="<?php echo $user->id?>" /> <?php echo $user->first_name . " " . $user->last_name ?></li>
			<?php endforeach ; ?>
		</ul>

		<label for="mensagem" style="clear:both;">Mensagem</label>
		<small>Esta ser&aacute; a mensagem enviada por email. <br />Se utilizar a diretriz <b>{ver_no_browser}</b> tal será substituida pelo link para visualização no browser.<br />A diretriz <b>{remover_email}</b> tal serrá substituida pelo link para remover o email da lista de subscritores.</small>
		<?php $this->getCkEditor()->editor("mensagem", ($mensagem->mensagem)); ?>

		<br />
		<br />

		<label for="mensagem_browser">Mensagem Browser</label>

		<br />
		<br />

		<?php $this->getCkEditor()->editor("mensagem_browser", ($mensagem->mensagem_browser)); ?>

		<br />

		<label for="mensagem_text">Mensagem texto</label>
		<textarea cols="80" rows="5" name="mensagem_text"><?php echo $mensagem->mensagem_text ?></textarea>

		<div class="clear"></div>

		<br />
		<br />

		<a href="?mod=newsletter&view=messages" class="btn"><?php echo _('Voltar');?></a>
		<a class="btn btn-primary" href="?mod=newsletter&amp;view=pre_send&amp;id=<?php echo $mensagem->id ?>">Preparar envio <i class="icon-white icon-share"></i></a>
		<input type="submit" class="btn btn-success" name="submit" value="Inserir / Editar" />
	</ul>
</form>
</div>

<?php
}

function pre_send($id = -1){
	$tools = $this->getTools();

	echo '<div class="main_div">';
	function return_to_messages($string_error){
		echo '<div class="error">'.$string_error.'</div>';
		echo '<meta http-equiv="refresh" content="1; url=?mod=newsletter$view=messages">';
	}
	if($id == -1){
		return_to_messages('Mensagem não encontrada. A voltar para a listagem...');
	}
	$mensagem = $this->mod->get_mensagem_by_id($id);
	if(!$mensagem){
		return_to_messages('Mensagem não encontrada. A voltar para a listagem...');
	}
		//A processar mensagem: 
	echo '<h1>A processar mensagem: <br />'.$mensagem->assunto.'</h1>';

	try {
		//$pdo = new PDO($pdo_string);

		$pdo = new PDO('mysql:host=195.200.253.230;dbname=brightmi_mail_stats', "brightmi_mstats", "Bright#$91", array(
			PDO::ATTR_PERSISTENT => false
			));
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	} catch (PDOException $ex) {
		echo 'Connection failed: ' . $ex->getMessage();
		$pdo = false;
	}		


	//Tenho que ir buscar o client_id
	$sql = "SELECT * FROM clients where `domain` = 'holmesplacenews.pt'";
	//echo $sql;

	$query = $pdo->query( $sql );
	$client = $query->fetchObject();





	?>
	<!-- Acções -->
	<p style="text-align:left;" class="action_messages">
		<a class="btn" href="../inc/visualize_news.php/<?php echo $client->id; ?>/<?php echo $mensagem->id; ?>/admin" target="_blank"><i class="icon-eye-open"></i> Pré-visualizar</a>
		<a class="btn" href="?mod=newsletter&view=add_mensagem&id=<?php echo $id; ?>"><i class="icon-pencil"></i> Editar</a>
		<a class="btn btn-info newsletter_toggle" href="#">Enviar teste <i class="icon-share icon-white"></i></a>
		<a class="btn btn-success" href="?mod=newsletter&view=enviar&id=<?php echo $id; ?>">Enviar <i class="icon-share icon-white"></i></a>
	</p>
	<!-- POP UP Actions forms -->
	<!-- Enviar teste -->        
	<form action="?mod=mass_email" method="post" id="send_test" class="newsletter_expand">
		<!--p style="text-align:left; background-color:whitesmoke; border:1px solid grey; margin:30px;" id="text_email_p"-->
		<div class="well">
			<div>

				<label>Remetente</label>
				
				<?php $sender_permissions = $this->get_sender_permissions($_SESSION["user"]->id) ?>

				<select name="sender_id">
					<option>Seleccione um remetente</option>
					<?php foreach ($sender_permissions as $key => $email): ?>
					<option value="<?php echo $key ?>"><?php echo $email["email_from"] ?> - <?php echo $email["email"] ?></option>
					<?php endforeach ?>
				</select>

				<p><i class="icon-info-sign"></i> <small>Ir&aacute; enviar esta newsletter para o endere&ccedil;o introduzido abaixo</small></p>
				<input type="hidden" name="mensagem_id" value="<?php echo $_GET["id"] ?>" />
				<label class="" for="text_email">Email de destino: </label>
			</div>
			<input type="text" name="text_email" id="text_email" />
			<!--input type="submit" class="submit btn btn-info" value="Enviar" /-->
			<input type="button" class="mandril-btn submit btn btn-info" value="Enviar" />
		</div>
		<!--/p-->
	</form>

	<h2 style="text-align:left;">Informação geral: </h2>
	<div class="well">
		<!-- Informação -->
		<p style="text-align:left;"><span>Estado: </span> <span class="label label-info"><?php echo $mensagem->estado; ?></span></p>
		<p style="text-align:left;"><span>Último Update:</span> <b><?php echo $tools->timestamp_to_jan($mensagem->data_update); ?></b></p>
		<p style="text-align:left;"><span>Data Criação:</span> <b><?php echo $tools->timestamp_to_jan($mensagem->data_criada); ?></b></p>
	</div>


	<h2 style="text-align:left;">Emails teste já enviados: </h2>

	<?php $res = $this->mod->get_send_test($_GET['id']); ?>
	

	<?php if(mysql_num_rows($res) > 0): ?>

	<table class="table">
		<tr>
			<th>Destinatário</th>
			<th>Data</th>
		</tr>

		<?php 
		while($row = mysql_fetch_object($res)){
			if($row->output == 'sucesso'){
				$style ="background-color:lightgreen;";
			}else{
				$style ="background-color:red;";
			}
			echo "
			<tr style=\"".$style."\">
			<td>".$row->destino."</td>
			<td>".$tools->timestamp_to_jan($row->hora)."</td>
			</tr>";

				//echo '<p style="text-align:left; '.$style.'"><span class="label">'.$row->assunto.'</span><span class="label">'.$row->destino.'</span>'.$tools->timestamp_to_jan($row->hora).'</p>';
				//echo '<p style="text-align:left; '.$style.'"><span class="label">'.$row->assunto.'</span><span class="label">'.$row->destino.'</span>'.$tools->timestamp_to_jan($row->hora).'</p>';
		}
		
		?>

	</table>

<?php else: ?>

	<div class="alert alert-info">Ainda n&atilde;o foram efectuados testes de envio.</div>

<?php endif; ?>

</div>

<?php

}

function show_statistics_general(){
		//Vamos buscar os valores todos aqui no inicio

		#Número de newsletters
	$query = "SELECT * FROM `mensagens`";
	$res = mysql_query($query) or die( mysql_error() );
	$num_news = mysql_num_rows( $res );

		#Núemro de newsletters enviadas
	$query = "SELECT * FROM `mensagens` WHERE `estado_code` = '1'";
	$res = mysql_query($query) or die( mysql_error() );
	$num_news_enviadas = mysql_num_rows( $res );

		#Evolução dos subscritores
	$query = "SELECT COUNT(email) as num, MONTH(date_created) as month FROM `subscribers` WHERE `is_active` = '1' GROUP BY MONTH(date_created)";

	$res = mysql_query( $query ) or die( mysql_error() );
	while( $row = mysql_fetch_object( $res ) ) {
		$evo[(int)$row->month] = $row;
	}

	?>

	<h1>Estat&iacute;sticas</h1>

	<!-- Navegação lateral, Uma para cada estatística -->
	<ul class="nav nav-tabs" id="tabThis">
		<li><a href="#home" data-toggle="tab">Newsletters</a></li>
		<li class="active"><a href="#profile" data-toggle="pill">Subscritores por m&ecirc;s</a></li>
		<li><a href="#bounces" data-toggle="tab">Bounces</a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane" id="home">
			<table class="table table-condensed table-bordered">
				<caption>Newsletters</caption>
				<thead>
					<tr>
						<th></th>
						<th>Número</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th>Número de newsletters</th>
						<td><?php echo $num_news; ?></td>
					</tr>
					<tr>
						<th>Número de newsletters enviadas</th>
						<td><?php echo $num_news_enviadas; ?></td>
					</tr>
				</tbody> 
			</table>

			<div class="alert alert-info">Seleccione uma das newsletters para visualizar dados relativos ao envio da mesma</div>

			<div>
				<?php $mensagens_res = $this->mod->get_messages(0, 30); ?>
				<table class="table table-bordered">
					<tr>
						<th>#</th>
						<th>Newsletter</th>
						<th>Data Cria&ccedil;&atilde;o</th>
						<th>Estado</th>
					</tr>
					<?php while ($newsletter = mysql_fetch_object($mensagens_res)): ?>
					<tr>
						<td><?php echo $newsletter->id ?></td>
						<td><a href="?mod=newsletter&amp;view=statistics&amp;id=<?php echo $newsletter->id ?>"><?php echo $newsletter->assunto ?></a></td>
						<td><?php echo $newsletter->data_criada ?></td>
						<td><span class="label label-info"><?php echo $newsletter->estado ?></span></td>
					</tr>	
				<?php endwhile ?>
				<tr>

				</tr>
			</table>
		</div>
	</div>

		<!--div class="tab-pane" id="messages">
	</div-->

	<?php
	$sql = "SELECT COUNT(id) as total, MONTH(date_created) AS month_created, YEAR(date_created) AS year_created FROM subscribers GROUP BY year_created, month_created";
	$query = mysql_query($sql);

	while ($row = mysql_fetch_object($query)) {
		$subscribers_per_month[$row->month_created] = $row->total;
	}

	for ($i=1; $i < 13; $i++) { 
		$subscribers_per_all_months[$i] = (int) $subscribers_per_month[$i];
	}

	?>

	<div class="tab-pane bright_graph active" id="profile">
		<table id="evo-subscritores" class="table table-condensed table-bordered discrete">
			<caption>Evolução dos subscritores</caption>
			<thead>
				<tr>
					<td></td>
					<th class="data-header">Jan</th>
					<th class="data-header">Fev</th>
					<th class="data-header">Mar</th>
					<th class="data-header">Abr</th>
					<th class="data-header">Mai</th>
					<th class="data-header">Jun</th>
					<th class="data-header">Jul</th>
					<th class="data-header">Ago</th>
					<th class="data-header">Set</th>
					<th class="data-header">Out</th>
					<th class="data-header">Nov</th>
					<th class="data-header">Dez</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<th>Número de subscritores</th>
					<td class="data-value"><?php echo $subscribers_per_all_months[1] ?></td><td class="data-value"><?php echo $subscribers_per_all_months[2] ?></td><td class="data-value"><?php echo $subscribers_per_all_months[3] ?></td><td class="data-value"><?php echo $subscribers_per_all_months[4] ?></td><td class="data-value"><?php echo $subscribers_per_all_months[5] ?></td><td class="data-value"><?php echo $subscribers_per_all_months[6] ?></td><td class="data-value"><?php echo $subscribers_per_all_months[7] ?></td><td class="data-value"><?php echo $subscribers_per_all_months[8] ?></td><td class="data-value"><?php echo $subscribers_per_all_months[9] ?></td><td class="data-value"><?php echo $subscribers_per_all_months[10] ?></td><td class="data-value"><?php echo $subscribers_per_all_months[11] ?></td><td class="data-value"><?php echo $subscribers_per_all_months[12] ?></td></tr>
				</tbody>
			</table>

			<div id="morris-bars-subscritores" style="height:150px;"></div>

		</div>


		<!-- bounces -->
		<div class="tab-pane" id="bounces">
			<?php 
			$sql = "select * from subscribers where hard_bounces_count > 0";
			$query = mysql_query($sql);
			$total_bounces = mysql_num_rows($query);

			?>

			<div class="alert alert-info">
				<span>De um total de <b><?php echo $total ?></b> subscritores,  <b><?php echo $total_bounces ?></b> est&atilde;o automaticamente exclu&iacute;dos da lista de envio devido a mensagens anteriores terem sido devolvidas.</span>
			</div>
			<table class="table table-bordered">
				<tr>
					<th>E-mail</th>
				</tr>
				<?php while ($bounce = mysql_fetch_object($query)): ?>
				<tr>
					<td><?php echo $bounce->email ?></td>
				</tr>
			<?php endwhile ?>
		</table>
	</div>
	<!-- bounces -->

</div>



<?php
}



function show_statistics($id){
	$bmm  = new BRIGHT_mail_feedback();
	
	$query ="SELECT * FROM `mensagens` WHERE `id` = '".$id."'";
	$res = mysql_query( $query ) or die(mysql_error());
	$mensagem = mysql_fetch_object($res);
	if( ! $mensagem ) {
		?>
		<h1>Erro:</h1>
		<p>A mensagem não foi encontrada.</p>
		<?php
		return false;
	}

	$client_id = $bmm->get_client_id();
	$num_aberturas = $bmm->get_num_opened_from_newsletter($client_id, $id);

	if( $mensagem->estado_code == 0 || $mensagem->estado == "Não utilizada" || $num_aberturas === 0 ){
		?>
		<h1>Estat&iacute;sticas para a mensagem: <b><?php echo $mensagem->assunto; ?></b></h1>
		<p>Ainda não existem dados suficientes para aceder a este módulo.</p>
		<?php
		return false;
	}
	?>
	
	<h2>Estatisticas para a mensagem: <?php echo $mensagem->assunto; ?></h2>

	<!-- Navegação lateral, Uma para cada estatística -->
	<ul class="nav nav-tabs" id="tabThis">
		<li><a href="#home" data-toggle="tab">Dados gerais</a></li>
		<li><a href="#profile" data-toggle="pill">Listagem aberturas</a></li>
		<li><a href="#clicks" data-toggle="pill">Listagem de cliques</a></li>
		<li class="active"><a href="#messages" data-toggle="tab">Gráficos</a></li>
	</ul>

	<div class="tab-content">

		<div class="tab-pane" id="home">
			<table class="table table-condensed table-bordered">
				<thead>
					<tr>
						<td>-</td>
						<th>#</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$query = "SELECT * FROM `mensagens_enviadas` WHERE `mensagem_id`= '".$id."' AND `output` = '3'"; 
					$res = mysql_query( $query ) or die( mysql_error() );
					$envios_espera = mysql_num_rows($res);

					//$query = "SELECT COUNT(id) as total FROM `subscribers` WHERE hard_bounces_count > 0";
					$query = "SELECT COUNT(id) as total from subscribers where hard_bounces_count > 0 AND FIND_IN_SET($id, bounced_on);";

					$res = mysql_query($query);
					if ( $res ) {
						$bounces = mysql_fetch_object($res);
						$bounces_count =  intval($bounces->total);
						# code...
					}else{
						$bounces_count = 0;
					}


					$query = "SELECT * FROM `mensagens_enviadas` WHERE `mensagem_id`= '".$id."' AND `output` = '0'"; 
					$res = mysql_query( $query ) or die( mysql_error() );
					$envios_falhados = mysql_num_rows($res);

					$client_id = $bmm->get_client_id();
					$evios_sucesso = $bmm->get_num_send($client_id, $id);
					$envios = $envios_espera + $envios_falhados + $evios_sucesso;

					?>

					<tr>
						<th>Número de envios </th>
						<?php 
						$query = "SELECT * FROM `mensagens_enviadas` WHERE `mensagem_id`= '".$id."'"; 
						$res = mysql_query( $query ) or die( mysql_error() );
						$num_envios = mysql_num_rows($res);						
						?>
						<td><?php echo $envios ?></td>
					</tr>
					<tr>
						<th>Envios bem sucedidos</th>
						<td>
							<?php echo $evios_sucesso - $bounces_count; ?>
						</td>
					</tr>
					<tr>
						<th>Envios em espera </th>
						<td>
							<?php echo $envios_espera; ?>
						</td>
					</tr>
					<tr>
						<th>Envios falhados </th>
						<td>
							<?php echo $envios_falhados; ?>
						</td>
					</tr>
					<tr>
						<th>Envios com retorno de erro por parte do destinat&aacute;rio (bounces) </th>
						<td>
							<?php echo $bounces_count; ?>
						</td>
					</tr>
					<tr>
						<th>Total de aberturas</th>
						<?php

						$client_id = $bmm->get_client_id();
						$num_aberturas = $bmm->get_num_opened_from_newsletter($client_id, $id);
						$num_pessoas_abriram = $bmm->get_num_distinct_opened_from_newsletter($client_id, $id);

						?>
						<td>
							<?php echo $num_aberturas; ?>
						</td>
					</tr>
					<tr>
						<th>Visualiza&ccedil;&otilde;es (&uacute;nicas)</th>
						<td>
							<?php echo $num_pessoas_abriram; ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- cliques -->
		<?php
		//info dos cliques
		$bmm = new BRIGHT_mail_feedback();
		$clicks = $bmm->get_clicks_from_client($client_id, $id);

		//definir as colunas
		$columns = array("email", "url", "date", "referer", "ip");

		//carregar para tabela temporaria
		$bmm->load_clicks_into_table($clicks, $columns);

		//processar os dados
		//links mais visitados
		$top_clicks = $bmm->get_top_clicks();

		//utilizadors mais activos
		$top_active_users = $bmm->get_top_active_users();

		//remover a tabela temporári
		$bmm->clear_clicks_table();

		?>
		<div class="tab-pane" id="clicks">
			<table class="table data-sort" id="clicks-table">
				<thead>
					<tr>
						<th>Email</th>
						<th>URL</th>
						<th>Data</th>
						<th>Origem</th>
						<th>IP</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($clicks as $click): ?>
					<tr>
						<td><?php echo $click->email ?></td>
						<td><?php echo $click->url ?></td>
						<td><?php echo $click->date ?></td>
						<td><?php echo $click->referer ?></td>
						<td><?php echo $click->ip ?></td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</div>
	<!-- cliques -->

	<!-- Listagem de aberturas -->
	<div class="tab-pane" id="profile">
		<?php
		$bmm = new BRIGHT_mail_feedback();
		$list = $bmm->get_opened_from_client($client_id, $id);

		?>

		<table class="table" id="datasort">
			<thead>
				<tr>
					<th>Email</th>
					<th>Navegador</th>
					<th>IP</th>
					<th>Origem</th>
					<th>Data de visualiza&ccedil;&atilde;o</th>                    
				</tr>
			</thead>
			<tbody>

				<?php 
				foreach ($list as $item) { ?>
				<tr>
					<td><a href="mailto: <?php echo $item->email ?>"><?php echo $item->email ?></a></td>
					<td>
						<a href="#" class="tooltip2" rel="tooltip" data-original-title="<?php echo $item->user_agent; ?>">
							<?php echo substr( $item->user_agent, 0, 50); echo ( $item->user_agent > 50 )? '...' : ''; ?>
						</a>
					</td>
					<td><?php echo $item->ip ?></td>
					<td>
						<a href="#" class="tooltip2" rel="tooltip" data-original-title="<?php echo $item->user_agent; ?>">
							<?php echo substr( $item->referer, 0, 50); echo ( $item->referer > 50 )? '...' : ''; ?>
						</a>
					</td>
					<td><?php echo ($item->date_in) ?></td>
				</tr>

				<?php 
			}
			?>
		</tbody>
	</table>


</div>
<!-- graficos -->
<div class="tab-pane active bright_graph" id="messages">
	
	<div class="row-fluid">
		<div class="span6">
			<div id="morris-pie-aberturas" style="height:200px;"></div>
		</div>
		<div class="span6">
			<table class="table table-condensed table-bordered  discrete">
				<caption>Percentagem de aberturas</caption>
				<thead>
					<tr>
						<th></th>
						<th>Número</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th>Entregues sem abertura de imagens</th>
						<td id="morris-recebidas-nao-lidas"><?php echo ($evios_sucesso - $num_pessoas_abriram - $bounces_count); ?></td>
					</tr>
					<tr>
						<th>Entregues com abertura de imagens</th>
						<td id="morris-recebidas-lidas"><?php echo $num_pessoas_abriram; ?></td>
					</tr>
					<tr>
						<th>Devolvidos</th>
						<td id="morris-bounces"><?php echo $bounces_count ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	
	<div id="morries-line-aberturas" style="height:250px;"></div>
	

	<!-- Tabela com as aberturas em função do tempo -->

	<?php
				#Pre-Processing
	unset($list);
	$bmm = new BRIGHT_mail_feedback();
	$list = $bmm->get_statistics_by_day($client_id, $id);

	?>

	<?php if ($list): ?>

	<table style="display:none;" id="table-aberturas-dia" class="table table-condensed table-bordered discrete">
		<caption>N&uacute;mero de aberturas por dia</caption>
		<thead>
			<tr>
				<td></td>
				<?php 
				$counter = 0;
				foreach ($list as $item) { ?>
				<?php if ($counter < 7): ?>
				<th class="data-header"><?php echo substr($item->day, 0, 2) ?>/<?php echo substr($item->day, 2, 4) ?></th>
			<?php endif ?>

			<?php 
			$counter ++;

		}
		?>
		<th class="data-header">&gt; 7</th>
	</tr>
</thead>
<tbody>
	<tr>
		<th># de aberturas</th>
		<?php 
		$counter = 0;
			$total = 0; //total acima de 7
			foreach ($list as $item) { ?>
			<?php if ($counter < 7): ?>
			<td class="data-value"><?php echo $item->num; ?></td>
		<?php else: ?>
		<?php $total += $item->num ?>
	<?php endif ?>
	<?php 
			$counter++; //incrementar a contagem, limitar a 7
		}
		?>
		<td class="data-value"><?php echo $total ?></td>
	</tr>
</tbody>
</table>
<?php endif ?>
</div>
<!-- graficos -->

<br />
<br />

<div class="clear"></div>

<!-- clicks -->
<div class="row-fluid">

	<?php if (!empty($top_active_users)): ?>
	
	<div class="well most-viewed">

		<div class="span6">
			<table class="table table-condensed table-bordered">
				<caption>Conte&uacute;do mais visualizado</caption>
				<thead>
					<tr>
						<td>URL</td>
						<td>Total de cliques</td>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($top_clicks as $click): ?>
					<tr>
						<td><a href="<?php echo $click->url ?>"><?php echo $click->url ?></a></td>
						<td><?php echo $click->total_clicks ?></td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</div>

	<div class="span6">
		<table class="table table-condensed table-bordered">
			<caption>Utilizadores mais activos</caption>
			<thead>
				<tr>
					<td>E-mail</td>
					<td>Total de cliques</td>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($top_active_users as $user): ?>
				<tr>
					<td><a href="mailto:<?php echo $user->email ?>"><?php echo $user->email ?></a></td>
					<td><?php echo $user->total_clicks ?></td>
				</tr>
			<?php endforeach ?>
		</tbody>
	</table>
</div>

<div class="clear"></div>

</div>

<?php else: ?>

	<div class="alert alert-info">N&atilde;o h&aacute; ainda dados suficientes para gerar estat&iacute;sticas sobre cliques</div>

<?php endif ?>

</div>

</div>
			<!--
			<div class="tab-pane" id="settings">
			</div>
		-->

	</div>



	<?php

}

function show_statistics2(){
		//listar todas as visualizações da newsletter
	$bmm = new BRIGHT_mail_feedback();
	$client_id = $bmm->get_client_id();
	$list = $bmm->get_opened_from_client($client_id);
	$num_subs = $bmm->get_distinct_opened($client_id);
	
	
		//info de subscritores
	$subscribers_info = $this->mod->get_subscribers_info_year(date("Y"));

	?>

	<div class="row-fluid">
		<div class="well">
			<h4>&Uacute;ltimas newsletters enviadas</h4>

			<div class="bright_graph">
				<?php
				$query = "SELECT * FROM `mensagens` WHERE `estado` = 'enviada' ORDER BY `data_criada` ASC";
				$res_main = mysql_query($query) or die(mysql_error());
				while( $row = mysql_fetch_row($res_main) ) {
					?>
					<table class="discrete pie_graph table table-bordered table-condensed">
						<caption class="lead">Newsletter: <b><?php echo $mensagem->assunto; ?></b></caption>
						<thead>
							<tr>
								<th></th>
								<th>Número</th>
							</tr>
						</thead>

						<tbody>
							<tr>
								<th>Pessoas que não abriram a newsletter</th>
								<td><?php
								$query = "SELECT * FROM `subscribers` WHERE `is_active` = '1'";
								$res = mysql_query($query) or die(mysql_error());
								echo (mysql_num_rows($res)-$num_subs[0]->num);
								?></td>
							</tr>
							<tr>
								<th>Pessoas que abriram a newsletter</th>
								<td>
									<?php
									echo $num_subs[0]->num;
									?>
								</td>
							</tr>
						</tbody>
					</table>

					<?php
				}

				?>

			</div>

		</div>

		<h4>A visualizar todas as aberturas de newsletters</h4>
		<div class="row-fluid">
			
			<div class="dataTables_wrapper" id="datasort_wrapper">
				<table class="table" id="datasort">
					<thead>
						<tr>
							<th>Email</th>
							<th>Navegador</th>
							<th>IP</th>
							<th>Origem</th>
							<th>Data de visualiza&ccedil;&atilde;o</th>                    
						</tr>
					</thead>
					<tbody>

						<?php 
						foreach ($list as $item) { ?>
						<tr>
							<td><a href="mailto: <?php echo $item->email ?>"><?php echo $item->email ?></a></td>
							<td><?php echo $item->user_agent ?></td>
							<td><?php echo $item->ip ?></td>
							<td><?php echo $item->referer ?></td>
							<td><?php echo date("d-m-Y h:m", time($item->date_in)) ?></td>
						</tr>

						<?php 
					}
					?>
				</tbody>
			</table>
		</div>

	</div>

	<?php
}


function showMessages(){


	if(isset($_GET["remove"]))
		$this->remove_message($_GET["remove"]);

	if(isset($_GET["duplicate"]))
		$this->duplicate_message($_GET["duplicate"]);

	?>
	<div class="main_div">
		<h1>Newsletters</h1>
		<a href="?mod=newsletter&view=add_mensagem" class="add-new-news btn btn-success"><i class="icon-white icon-plus-sign"></i> <?php echo _('Adicionar newsletter');?></a>

		<div class="clear"></div>

		<br />

		<?php
		if(!isset($_GET['start'])){
			$start = 0;
		}else{
			$start = $_GET['start'];
		}
		$num_mensagens = $this->mod->get_num_mensagens();
		if($start != 0){
			?>
			<a href="?mod=newsletter&view=messages&start=<?php echo ($start-30); ?>">Página anterior</a>
			<?php
		}
		if($num_mensagens >= ($start+30)){
			?>
			<a href="?mod=newsletter&view=messages&start=<?php echo ($start+30); ?>">Página seguinte</a>
			<?php
		}
		?>
		<table class="addpadding table" id="datasort">
			<thead>
				<tr>
					<th>#</th>
					<th>Assunto</th>
					<th>Data Criação</th>
					<th>Estado</th>
					<th class="nosort">A&ccedil;&otilde;es</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$num_mensagens = $this->mod->get_num_mensagens();
				$mensagens_res = $this->mod->get_messages();
				if(mysql_num_rows($mensagens_res) < 1){
					?>
					<tr>
						<td colspan="5"><div class="alert alert-info">N&atilde;o est&atilde;o criadas newsletters ou n&atilde;o tem permiss&atilde;o para as visualizar</div></td>
					</tr>
					<?php
				}else{   
					while($row = mysql_fetch_array($mensagens_res)){
						?>
						<tr>
							<td><?php echo $row['id']; ?></td>
							<td class="alignleft"><a href="?mod=newsletter&amp;view=add_mensagem&amp;id=<?php echo $row['id']?>"><?php echo $row['assunto']; ?></td>
							<td><?php echo $row['data_criada']; ?></td>
							<td>
								<?php 
								if( $row['estado_code'] == 0 ) {
									echo '<span class="estado">';$row['estado']."</span>";
								}else
								echo '<span class="estado label label-success">'.$row['estado'].'</span>';
								?>
							</td>
									<!--
									<td><?php echo $row['estado']; ?></td>
								-->
								
									<td>
										<div class="table-actions">
											<a rel="tooltip-top" data-original-title="Editar" class="btn" href="?mod=newsletter&amp;view=add_mensagem&amp;id=<?php echo $row['id']?>"><i class="icon icon-pencil"></i></a>
											<a rel="tooltip-top" data-original-title="Estat&iacute;sticas" href="?mod=newsletter&amp;view=statistics&amp;id=<?php echo $row['id']; ?>" class="btn"><i class="icon icon-align-left"></i></a>
											<a rel="tooltip-top" data-original-title="Replicar" class="btn" href="?mod=newsletter&amp;view=messages&amp;duplicate=<?php echo $row["id"] ?>"><i class="icon icon-resize-full"></i></a>
											<a rel="tooltip-top" data-original-title="Remover" class="btn btn-danger" href="?mod=newsletter&amp;view=messages&amp;remove=<?php echo $row["id"] ?>"><i class="icon-white icon-remove"></i></a>
											<a rel="tooltip-top" data-original-title="Preparar envio" class="btn btn-primary" href="?mod=newsletter&amp;view=pre_send&amp;id=<?php echo $row['id']; ?>"><i class="icon-white icon-share"></i></a>
										</div>
									</td>
								</tr>
								<?php
							}
						}
						?>
					</tbody>
				</table>

			</div>
			<?php

		}

		function get_utilizador($id){
			$sql = "SELECT * FROM users WHERE id = ".$id;
			$query = mysql_query($sql);

			if($query)
				return mysql_fetch_object($query);

			return false;
		}

		function get_user_groups(){
			$sql = "SELECT id, name FROM user_groups";
			$query = mysql_query($sql);

			if($query){
				while($row = mysql_fetch_object($query))
					$output[] = $row;

				return $output;
			}

			return false;

		}

		function render_user_groups($group_id){
			$groups = $this->get_user_groups();

			foreach ($groups as $group) {
				$selected = ($group->id == $group_id) ? "selected=\"selected\"" : "";
				echo "<option ".$selected." value=\"".$group->id."\">".$group->name."</option>";
			}
		}

		function update_user_permissions($user_id, $permissions){

			if(!empty($user_id) && count($permissions) > 0){
			//apagar tudo primeiro
				$sql = "DELETE FROM user_permissions WHERE user_id = {$user_id}";
				$delete = mysql_query($sql);

				foreach ($permissions as $garbagekey => $group_id) {
					$sql = "INSERT INTO user_permissions (user_id, group_id) VALUES ({$user_id}, {$group_id})";
					$insert = mysql_query($sql);
				}
				return true;
			}

			else
				tools::notify_add("N&atilde;o foram definidas permissões de mailing lists para o utilizador", "info");

		}

		function update_sender_permissions($user_id, $permissions){

			//apagar tudo primeiro
			$sql = "DELETE FROM user_sender_permissions WHERE user_id = {$user_id}";
			$delete = mysql_query($sql);

			if(!empty($user_id) && count($permissions) > 0){

				foreach ($permissions as $garbagekey => $sender_id) {
					$sql = "INSERT INTO user_sender_permissions (user_id, sender_id) VALUES ({$user_id}, {$sender_id})";
					$insert = mysql_query($sql);
				}

				tools::notify_add("Definições do utilizador actualizadas com sucesso", "success");

				return true;
			}

			else
				tools::notify_add("Não foram definidas permissões para o utilizador", "info");		
		}

		function update_user($id){

			$user_username = $_POST["user_username"];
			$user_first_name = $_POST["user_first_name"];
			$user_last_name = $_POST["user_last_name"];
			$user_email = $_POST["user_email"];
			$is_active = isset($_POST["is_active"]) ? 1:0;
			$user_password = $_POST["user_password"];
			$user_group = $_POST["user_group"];
			$user_group_permissions = $_POST["user_group_permissions"];
			$user_sender_permissions = $_POST["user_sender_permissions"];


		//update à password
			if(!empty($user_password))
				$sql_password = "`password` = '".md5($user_password)."', ";

		//update ao user
			$sql = "UPDATE `users` SET `first_name` = '{$user_first_name}', `last_name` = '{$user_last_name}', `username` = '{$user_username}', `email` = '{$user_email}', ".$sql_password." `is_active` = {$is_active}, `user_group` = {$user_group} WHERE id = {$id}";

			$query = mysql_query($sql);

		//update as permissoes
			$this->update_user_permissions($id, $user_group_permissions);
			$this->update_sender_permissions($id, $user_sender_permissions);

		//
			unset($_POST);

			$this->show_utilizador($id);

		}

		function insert_user(){

			$user_username = $_POST["user_username"];
			$user_first_name = $_POST["user_first_name"];
			$user_last_name = $_POST["user_last_name"];
			$user_email = $_POST["user_email"];
			$is_active = isset($_POST["is_active"]) ? 1:0;
			$user_password = $_POST["user_password"];
			$user_group = $_POST["user_group"];
			$user_group_permissions = $_POST["user_group_permissions"];

		//insert do user
			$sql = "INSERT INTO `users` (`first_name`, `last_name`, `username`, `email`, `password`, `is_active`, `user_group`) VALUES ( '{$user_first_name}', '{$user_last_name}', '{$user_username}', '{$user_email }', '".md5($user_password)."', {$is_active}, {$user_group})";
			$query = mysql_query($sql);

			if($query)
				tools::notify_add("Dados de utilizador gravados com sucesso", "success");				

		//buscar user id
			$sql = "SELECT MAX(id) AS id FROM users";
			$query = mysql_query($sql);

			$user_id = mysql_fetch_object($query)->id;

		//update as permissoes do user criado
			$this->update_user_permissions($user_id, $user_group_permissions);

		//limpar
			unset($_POST);

			$this->show_utilizador($user_id);

		}

		function show_utilizador($id){

			if(isset($_POST["save"])):
				switch ($id) {
					case 0:
					$this->insert_user();
					break;

					default:
					$this->update_user($id);
					break;
				}			

				else:

			//user info
					$user = $this->get_utilizador($id);

				?>

				<h1>A editar utilizador</h1>

				<a class="btn" href="?mod=newsletter&amp;view=utilizadores">Voltar</a>

				<br />
				<br />

				<div class="well">
					<form action="" method="post">

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

						<label>Tipo de utilizador:</label>
						<select name="user_group">
							<?php $this->render_user_groups($user->user_group); ?>
						</select>

						<h2>Permiss&otilde;es sobre as seguintes mailing lists</h2>

						<?php 
							$groups = $this->get_grupos(); 
							$senders = $this->get_senders();
						?>

						<ul>

							<?php foreach($groups as $grupo): ?>
							<?php $user_permissions = $this->get_group_permissions($user->id); ?>
							<?php $checked = (@array_key_exists($grupo->id, $user_permissions)) ? "checked=\"checked\"":""; ?>

							<li><input value="<?php echo $grupo->id ?>"  type="checkbox" name="user_group_permissions[]" <?php echo $checked ?> /> <span><?php echo $grupo->categoria ?></span></li>

							<?php endforeach ?>

						</ul>

						<h2>Pode enviar de</h2>

						<ul>
							<?php foreach ($senders as $sender): ?>
							<?php $sender_permissions = $this->get_sender_permissions($user->id); ?>
							<?php $checked = (@array_key_exists($sender->id, $sender_permissions)) ? "checked=\"checked\"":""; ?>
							<li><input value="<?php echo $sender->id ?>"  type="checkbox" name="user_sender_permissions[]" <?php echo $checked ?> /> <span><?php echo $sender->email_from ?> (<b><?php echo $sender->email ?></b>)</span></li>
							<?php endforeach ?>
							
						</ul>

					<input class="btn btn-primary" type="submit" name="save" value="Inserir / Editar" />
					<a class="btn" href="?mod=newsletter&amp;view=utilizadores">Voltar</a>

				</form>
			</div>

		<?php endif; ?>

		<?php 
	}

	function remove_utilizador($id){
		$id = intval($id);

		$sql = "DELETE FROM users WHERE id = {$id}";
		$query = mysql_query($sql);

		$sql = "DELETE FROM user_permissions WHERE user_id = {$id}";
		$query = mysql_query($sql);

		echo "<div class=\"alert alert-success\">Utilizador e permiss&otilde;es associadas removidas com sucesso</div> ";
	}

	function show_utilizadores(){

		if(isset($_GET["remove"]))
			$this->remove_utilizador($_GET["remove"]);

		if($_GET["id"] == 0 && isset($_GET["id"]))
			$this->show_utilizador(0);

		if(($_GET["id"]) > 0)
			$this->show_utilizador($_GET["id"]);

		else{
			$users = $this->get_users();

			?>
			<div class="main_div">
				<h1>Utilizadores</h1>
				<a href="?mod=newsletter&amp;view=utilizadores&amp;id=0" class="add-new-news btn btn-success"><i class="icon-white icon-plus-sign"></i> <?php echo _('Adicionar Utilizador');?></a>

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
						<?php $group_permissions = $this->get_group_permissions($user->id); ?>
						<tr>
							<td><a href="?mod=newsletter&amp;view=utilizadores&amp;id=<?php echo $user->id ?>"><?php echo $user->first_name . " " . $user->last_name ?></a></td>
							<td><?php echo $user->date_joined ?></td>
							<td><?php $this->render_group_permissions( intval($user->is_admin), $group_permissions); ?> </td>
							<td><?php Core::draw_boolean_status($user->is_active) ?></td>
							<td><?php Core::draw_boolean_status($user->is_admin) ?></td>
							<td><a class="btn btn-danger" href="?mod=newsletter&amp;view=utilizadores&amp;remove=<?php echo $user->id ?>"><i class="icon-white icon-remove"></i> Remover</a></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

		</div>

		<?php

	}
}

function show(){

		//get info
	$mod = $this->getMod();
	$tools = $this->getTools();
	$list = $mod->getSubscribers();
	?>

	<div class="main_div">
		<h1>Subscritores da Newsletter</h1>

		<?php 

				//como os grupos são um array do tipo grupo[id_grupo] = "Nome grupo", iterar com for i = 0
		if($_SESSION["user"]->is_admin)
			$grupos = $this->get_admin_group_permissions();
		else
			$grupos = $this->get_group_permissions($_SESSION["user"]->id);

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

					<form method="post" action="" enctype="multipart/form-data">
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

					<form method="post" action="" enctype="multipart/form-data">

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
						<!-- importar de csv -->

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

		<div class="alert-large alert alert-info">A lista de exclusão é constituida por endereços que solicitaram remoção da mailing list, ou pelos endereços que foram excluídos através da detecção automática de bounces (<b><?php echo $this->core->settings->remove_bounces_count ?></b> e-mails de retorno).<br />Pode alterar esta op&ccedil;&atilde;o, visitando a p&aacute;gina de <a href="?mod=settings">defini&ccedil;&otilde;es</a></div>

		<div class="well">
			<h2>Adicionar endere&ccedil;os &agrave; lista de exclus&atilde;o</h2>
			<p>Os endere&ccedil;os adicionados a esta lista jamais receber&atilde;o mensagens independentemente do grupo seleccionado</p>
			<form name="" action="" method="post">
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
	}

	function editSliders($slider_id) {

		//get info
		$mod = $this->getMod();
		$resQ = $mod->getSliders($slider_id);
		$tools = $this->getTools();
		?>
		<div class="editSliders">
			<div id="right">
				<?php
				if(($resQ!=FALSE && mysql_num_rows($resQ)>0) || $id == 0){
					if($id!=0)
						$row = mysql_fetch_object($resQ);
					else{
						$row->id = 0;
						$row->lang = $_SESSION['lang'];
						$row->is_active = 1;
					}
					?>
					<a class="view_link back_link" href="<?php echo _ROOT;?>/?mod=slider"><?php echo _('Voltar');
					?></a>
					<div class="clear"></div>
					<form method="post" action="" enctype="multipart/form-data">
						<table width="80%" class="edit">
							<tbody>
								<tr>
									<td valign="top" class="left section" colspan="2"><?php echo _('Slider:');?></td>
								</tr>
								<tr>
									<td>
										<strong><?php echo _('ID:');?></strong>
									</td>
									<td style="width:80%;">
										<input type="text" readonly="readonly" value="<?php echo $row->id;?>" name="id_menu"/>
									</td>
								</tr>
								<tr>
									<td>
										<strong><?php echo _('Língua:');?></strong>
									</td>
									<td>
										<input type="text" readonly="readonly" value="<?php echo $row->lang;?>" name="lang"/>
									</td>
								</tr>
								<?php
								if($slider_id == 0){
									?>
									<tr>
										<td>
											<strong><?php echo _('Tipo:');?></strong>
										</td>
										<td style="width:80%;">
											<?php $this->getTiposSelect();?>
										</td>
									</tr>
									<?php
								}
								else{
									?>
									<tr>
										<td>
											<strong><?php echo _('Tipo:');?></strong>
										</td>
										<td>
											<input type="text" readonly="readonly" value="<?php echo $row->tipo_name;?>"
											name="tipo_name"/>
										</td>
									</tr>
									<?php
								}
								?>
								<tr>
									<td>
										<strong><?php echo _('Nome:');?></strong>
									</td>
									<td style="width:80%;">
										<input type="text" value="<?php echo $row->name;?>" name="name"/>
									</td>
								</tr>
								<tr>
									<td>
										<strong><?php echo _('Activo:');?></strong>
									</td>
									<td>
										<?php $tools->createActiveSelect($row->is_active,'is_active');?>
									</td>
								</tr>
							</tbody>
						</table>
						<a class="view_link back_link" href="<?php echo _ROOT;?>/?mod=slider"><?php echo _('Voltar');?></a>

						<p class="alignright" style="margin-right: 5%;"><input name="submit-slider" type="submit"
							value="Guardar" /></p>
							<div class="clear"></div>
						</form>
						<?php
					}else{?>
					<div class="error" style="margin-top:10px;"><?php echo _('ERRO: Não foi possível apresentar o slider
					solicitado!');?></div>
					<?php
				}?>
			</div>
			</div><?php
		}

		function showSlides($id){

		//get info
			$mod = $this->getMod();
			$tools = $this->getTools();
			$list = $mod->getSlides($id);
			?>

			<div id="right">
				<a href="<?php echo _ROOT;?>/?mod=<?php echo $this->modName;?>&amp;option=edit-slides&amp;slider_id=<?php echo $id;?>&id=0" class="add-new-news floatleft"><?php echo _('Adicionar Novo');?></a>
				<?php
				if ($list != false) {
					$tools->createPager();
					?>
					<div class="clear"></div>
					<form method="post" action="" enctype="multipart/form-data">
						<table cellpadding="0" cellspacing="0" width="100%" class="tablesorter slides_sort" id="sorter">
							<thead>
								<tr>
									<th class="rtl nosort">
										<input type="checkbox" name="select_items">
									</th>
									<th>
										<?php echo _('ID'); ?>
									</th>
									<th>
										<?php echo _('Título'); ?>
									</th>
									<th  class="rtr">
										<?php echo _('Activo'); ?>
									</th>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<td class="rbl">
										<input type="checkbox" name="select_items">
									</td>
									<td>
										<?php echo _('ID'); ?>
									</td>
									<td>
										<?php echo _('Título'); ?>
									</td>
									<td  class="rbr">
										<?php echo _('Activo'); ?>
									</td>
								</tr>
							</tfoot>
							<tbody>
								<?php
								while ($row = mysql_fetch_object($list)) {
									if($row->is_active){
										$active = '<img src="../inc/img/admin/yes.png" alt="Sim" />';
									}
									else{
										$active = '<img src="../inc/img/admin/no.png" alt="N&atilde;o" />';
									}
									?>
									<tr>
										<td>
											<input type="checkbox" name="items[]" value="<?php echo $row->id; ?>">
										</td>
										<td>
											<?php echo $row->id; ?>
										</td>
										<td>
											<?php
											echo '<a href="' . _ROOT . '/?mod=slider&amp;option=edit-slides&slider_id='.$id.'&id='
											.$row->id . '">' . $row->name . '</a>';
											?>
										</td>
										<td class="active">
											<?php echo $active; ?>
										</td>
									</tr>
									<?php
						}//while
						?>
					</tbody>
				</table>
				<div class="doActions">
					<?php $tools->createActions(); ?>
					<input type="submit" value="<?php echo _('Submeter'); ?>"/>
				</div>
			</form>
			<?php
			$tools->createPager();
		}//if results
		else { ?>
		<div class="clear"></div>
		<div class="error"><?php echo _('Sem resultados') ?></div>
		<?php } ?>
		<div class="clear"></div>
	</div>
	<?php
}

function editSlides($slider_id,$id){

		//get info
	$mod = $this->getMod();
	$resQ = $mod->getSlides($slider_id,$id);
	$tools = $this->getTools();

	?>
	<div class="editSlides">
		<div id="right">
			<?php
			if(($resQ!=FALSE && mysql_num_rows($resQ)>0) || $id == 0){
				if($id!=0)
					$row = mysql_fetch_object($resQ);
				else{
					$row->id = 0;
					$row->lang = $_SESSION['lang'];
					$row->is_active = 1;
					$row->mod_id = $mod->modType($slider_id);
				}
				?>
				<a class="view_link back_link" href="<?php echo _ROOT;
				?>/?mod=slider&option=list-slides&slider_id=<?php echo $slider_id;?>"><?php echo _
				('Voltar');?></a>
				<div class="clear"></div>
				<form method="post" action="" enctype="multipart/form-data">
					<table width="100%" class="edit">
						<tbody>
							<tr>
								<td valign="top" class="left section" colspan="2"><?php echo _('Slide:') ?></td>
							</tr>
							<tr>
								<td>
									<strong><?php echo _('ID:');?></strong>
								</td>
								<td style="width:80%;">
									<input type="text" readonly="readonly" value="<?php echo $row->id;?>" name="id_menu"/>
								</td>
							</tr>
							<tr>
								<td>
									<strong><?php echo _('Título:');?></strong>
								</td>
								<td>
									<input type="text" value="<?php echo $row->name;?>" name="name"/>
								</td>
							</tr>
							<tr>
								<td>
									<strong><?php echo _('Foto:');?></strong><br/>
								</td>
								<td>
									<input type="hidden" value="<?php echo $row->img;?>" name="photo"/>
									<?php
									if($row->mod_id == 1){
										echo '<div id="image" onclick="openKCFinder(this)" style="width:570px;;height:318px">';
									}
									else
										echo '<div id="image" onclick="openKCFinder(this)" style="width:220px;height:164px">';
									if(!empty($row->img)){
										if($row->mod_id == 1){
											echo '<img src="../media'.$row->img.'" style="width:570px;
											height:318px"/>';
										}
										else
											echo '<img src="../media'.$row->img.'" style="width:220px;
										height:164px"/>';
									}?>

									<div style="margin:5px;cursor: pointer;" class="add-image-button">
										<input type="button" value="<?php echo _('Escolher Imagem');?>"/>
									</div>
								</div>
								<br/>
								<br/>
								<?php
								if($row->mod_id == 1){
									?>
									<div class="notice edit-table-notice"><strong><?php echo _('Medidas Recomendadas:');?></strong> <?php echo _('Largura: 570px Altura: 318px');?></div>
									<?php
								}else {
									?>
									<div class="notice edit-table-notice"><strong><?php echo _('Medidas Recomendadas:');?></strong> <?php echo _('Largura: 220px Altura: 164px');?></div>
									<?php
								}
								?>
							</td>
						</tr>
						<?php
						if($row->mod_id != 1){
							?>
							<tr>
								<td>
									<strong><?php echo _('Lead:');?></strong>
								</td>
								<td>
									<?php
									$this->getCkEditor()->config['toolbar'] = 'Basic';
									$this->getCkEditor()->editor("lead", html_entity_decode($row->lead));
									?>
								</td>
							</tr>
							<?php
						}
						?>
						<tr>
							<td>
								<strong><?php echo _('Link:');?></strong>
							</td>
							<td>
								<input type="text" value="<?php echo $row->link;?>" name="link"/>
							</td>
						</tr>
						<tr>
							<td>
								<strong><?php echo _('Activo:');?></strong>
							</td>
							<td>
								<?php $tools->createActiveSelect($row->is_active,'is_active');?>
							</td>
						</tr>

					</tbody>
				</table>
				<a class="view_link back_link" href="<?php echo _ROOT;?>/?mod=slider&option=list-slides&slider_id=<?php echo $slider_id;?>"><?php echo _('Voltar');?></a>

				<p class="alignright" style="margin-right: 5%;"><input name="submit-slide-item" type="submit" value="Guardar" /></p>
				<div class="clear"></div>
			</form>
			<?php
		}else{?>
		<div class="error" style="margin-top:10px;"><?php echo _('ERRO: Não foi possível apresentar a notícia solicitada!');?></div>
		<?php
	}?>
</div>
</div><?php
}

function getTiposSelect(){
	$res = $this->getMod()->getModsList();

	?>
	<select name="tipo_name">
		<?php
		while($row = mysql_fetch_object($res)){
			echo '<option value="'.$row->id.'" >'.$row->name.'</option>';
		}
		?>
	</select>
	<?php
}

function getMod() {
	return $this->mod;
}

function setMod($mod) {
	$this->mod = $mod;
}

function getCore() {
	return $this->core;
}

function setCore($core) {
	$this->core = $core;
}

function getDebug() {
	return $this->debug;
}

function setDebug() {
	$this->debug = new Debug();
}

function setTools($tools) {
	$this->tools = $tools;
}

function getTools() {
	return $this->tools;
}

function setCkEditor(){
	$this->ckeditor = new CKEditor();
	$this->ckeditor->basePath = get_include_path().'/libs/ckeditor/';
	$this->ckeditor->config['extraPlugins'] = "autogrow";
	$this->ckeditor->config['autoGrow_onStartup'] = true;
	$this->ckeditor->config['autoGrow_maxHeight'] = 500;
	$this->ckeditor->config['jqueryOverrideVal'] = true;
	$this->ckeditor->config['enterMode'] = "CKEDITOR.ENTER_BR";
	$this->ckeditor->config['fullPage'] = true;

}

function getCkEditor(){
	return $this->ckeditor;
}

}
?>