<?php 
/**
* 
*/
class Newsletters
{
	public $view = "newsletters/newsletters";
	public $ckeditor = FALSE;
	
	function __construct()
	{
		if ( !empty($_GET["view"]) ) {
			$this->$_GET["view"]();
		}
	}

	/* Controllers */
	function messages()  { }
	function add_mensagem() { $this->view = "newsletters/add_mensagem"; }
	function replicar()  {
		$id = $_GET["duplicate"];
		$sql = "INSERT INTO mensagens (`id`, `url`, `assunto`, `mensagem_text`, `mensagem_browser`, `mensagem`) ( SELECT  NULL, `url`, `assunto`, `mensagem_text`, `mensagem_browser`, `mensagem` FROM mensagens WHERE id = {$id})";
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

			tools::notify_add( "A newsletter foi duplicada com sucesso. O seu n&uacute;mero actual &eacute; <b>#".$mensagem->id."</b>. Pode agora <a href=\"?mod=newsletter&view=add_mensagem&id=".$mensagem->id."\"><b>editar a newsletter duplicada</b></a>", "success" );
		}

		else{
			tools::notify_add( "Ocorreu um erro. Por favor tente novamente.", "error" );
		}
		redirect( "admin/index.php?mod=newsletters&view=messages" );
	}
	function remover()  {
		$sql = "DELETE FROM mensagens WHERE id = ".$_GET["remove"];
		$query = mysql_query($sql);

		$sql = "delete from envios where mensagem_id = '".$_GET["remove"]."'";
		mysql_query($sql) or die_sql( $sql );

		tools::notify_add( "Newsletter removida com sucesso.", "success" );

		redirect( "admin/index.php?mod=newsletters&view=messages" );
	}


	/* Models */
	function get_num_mensagens(){
		$query = "SELECT * FROM `mensagens`";
		$res = mysql_query($query) or die(mysql_error());
		return mysql_num_rows($res);
	}

	function get_messages($start = 0, $limit = 30){	//Copy of this function in statistics.mod

		if($_SESSION["user"]->is_admin){
			$query = "SELECT mensagens.*, count(envios.id) as total_envios 
				from mensagens
				left join envios on envios.mensagem_id = mensagens.id 
				group by mensagens.id
				ORDER BY envios.date_sent  DESC
				LIMIT ".$start.", ".$limit;
		}else{
			$query = "SELECT mensagens.*, count(envios.id) as total_envios 
				from mensagens
				left join envios on envios.mensagem_id = mensagens.id 
				left join user_permissions_newsletter on user_permissions_newsletter.id_newsletter = mensagens.id 
				where user_permissions_newsletter.id_user = '".$_SESSION["user"]->id."'
				group by mensagens.id
				ORDER BY envios.date_sent 
				DESC LIMIT ".$start.", ".$limit;
		}

		//echo $query;

		$res = mysql_query($query) or die_sql( $query );
		return $res;
	}

	function initialize_mensagem_from_post(){	//Moved to newsletters.mod
		$return = new stdClass;
		$return->id = $_POST['mensagem_id'];
		//$core = new Core();
		//$tools = $core->getTools();

		$return->assunto = tools::getPost('assunto');
		$return->mensagem = tools::get_page('mensagem');
		$return->mensagem_browser = tools::get_page('mensagem_browser');
		$return->mensagem_text = tools::get_page('mensagem_text');
		$return->url = tools::getPost('url');

		$return->data_criada = NULL;
		$return->data_update = tools::get_timestamp();
		$return->estado = 'Não utilizada';
		return $return;
	}

	function initialize_mensagem($id = -1){	//Moved to newsletters.mod
		$return = new stdClass;
		if($id == -1){
			$return->id = -1;
			$return->assunto = '';
			$return->mensagem = '';
			$return->mensagem_browser = '';
			$return->data_criada = '0000-00-00 00:00:00';
			$return->data_update = '0000-00-00 00:00:00';
			$return->estado = '';
		}else{
			if(!($return = $this->get_mensagem_by_id($id))){
				$return = $this->initialize_mensagem();
			}
		}
		return $return;
	}
	function get_mensagem_by_id($id){	//acho que há um cópia noutro lado
		$query = "SELECT * FROM `mensagens` WHERE `id` = '".$id."'";
		$res = mysql_query($query) or die(mysql_error());
		if( mysql_num_rows($res) < 1 ){
			return false;
		}else{
			return mysql_fetch_object($res);
		}
	}

	//listar os grupos a quais os utilizadores têm permissão
	function get_users_with_permission_in_newsletter($newsletter_id){	//Moved to newsletters.mod

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


	/* Ckeditor */
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
		if (!$this->ckeditor) {
			$this->setCkEditor();
		}
		return $this->ckeditor;
	}
	function insert_mensagem($mensagem){

		//gravar a mensagem
		$query = "INSERT INTO `mensagens` (`assunto`, `url`, `mensagem`, `mensagem_browser`, `mensagem_text`, `data_update`, `estado`) VALUES ('".$mensagem->assunto."', '".$mensagem->url."', '".htmlspecialchars_decode( ( addslashes($mensagem->mensagem) ) )."', '".htmlspecialchars_decode( ( addslashes($mensagem->mensagem_browser) ) )."', '".htmlspecialchars_decode( ( $mensagem->mensagem_text ) )."', '".$mensagem->data_update."', '".$mensagem->estado."')";
		$result = mysql_query($query) or die(mysql_error().$query);

		//buscar o id da mensagem
		$sql = "SELECT MAX(id) AS id FROM mensagens";
		$result = mysql_query($sql);
		$mensagem = mysql_fetch_object($result);

		//gravar as permissoes
		$save_permissions = $this->save_newsletter_permissions($_POST["user_permissions"], $mensagem->id);
		tools::notify_add( "Mensagem adicionada com sucesso.", "success" );

		return true;
	}


	function update_mensagem($mensagem){


		//update a newsletter
		$query ="UPDATE `mensagens` SET 
		`assunto` = '".$mensagem->assunto."',
		`url` = '".$mensagem->url."',
		`mensagem` = '".htmlspecialchars_decode( htmlspecialchars_decode( (addslashes($mensagem->mensagem)) ) )."',
		`mensagem_browser` = '".htmlspecialchars_decode( htmlspecialchars_decode( (addslashes($mensagem->mensagem_browser)) ) )."',
		`mensagem_text` = '".htmlspecialchars_decode( htmlspecialchars_decode( ($mensagem->mensagem_text) ) )."',
		`data_update` = '".$mensagem->data_update."'
		WHERE `id`='".$mensagem->id."'";

		//update a permissões
		$save_permissions = $this->save_newsletter_permissions($_POST["user_permissions"], $mensagem->id);

		mysql_query($query) or die(mysql_error().$query);

		tools::notify_add( "Newsletter actualizada com sucesso", "success" );
		return true;
	}
	function save_newsletter_permissions($users, $newsletter_id){	//Moved to newsletters.mod


		//nesta situação, remover todas as permissões
		if(!$users){
			$sql = "DELETE FROM user_permissions_newsletter WHERE id_newsletter = {$newsletter_id}";
			$query = mysql_query($sql);
			$errors .= mysql_error();
		}

		else{
			$users_string = implode(",", $users);

			//apagar as permissões já definidas
			$sql = "DELETE FROM user_permissions_newsletter WHERE id_newsletter = {$newsletter_id}";
			$delete = mysql_query($sql);

			foreach ($users as $user_id) {
				$sql = "INSERT INTO user_permissions_newsletter (id_user, id_newsletter) VALUES ({$user_id}, {$newsletter_id})";
				$insert = mysql_query($sql);

				$errors .= mysql_error();
			}
		}

		if(empty($errors)) {
			tools::notify_add( "Permiss&otilde;es actualizadas com sucesso", "success" );
		}else{
			tools::notify_add( "Permiss&otilde;es não foram actualizadas com sucesso. <b>".$errors."</b>", "error" );
		}
		
	}



}

 ?>