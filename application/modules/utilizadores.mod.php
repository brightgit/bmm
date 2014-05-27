<?php 
/**
* 
*/
class Utilizadores
{
	public $view = "utilizadores/utilizadores";
	
	function __construct()
	{
		if (!empty($_GET["view"])) {
			$this->$_GET["view"]();
		}

	}

	/* Controllers */
	function utilizadores() { }
	function remove_utilizador() {
		$id = intval($_GET["id"]);

		$sql = "DELETE FROM user_permissions WHERE user_id = {$id}";
		$query = mysql_query($sql) or die_sql( $sql );
		
		$sql = "DELETE FROM user_permissions_newsletter WHERE id_user = {$id}";
		$query = mysql_query($sql) or die_sql( $sql );

		$sql = "DELETE FROM users WHERE id = {$id}";
		$query = mysql_query($sql) or die_sql( $sql );


		tools::notify_add("Utilizador e permiss&otilde;es associadas removidas com sucesso", "success" );
		redirect( "admin/index.php?mod=utilizadores&view=utilizadores" );
	 }
	function add_utilizador() {	//Antigo "show_utilizador"
		$this->view = "utilizadores/add_utilizador";
	}
	function add_update_utilizador() {	//Gravar
		if ( $_GET["id"] == 0  ) {	//Inserir
			$this->insert_user();
		}else{	//Actualizar
			$this->update_user( $_GET["id"] );
		}
		tools::notify_add( "Utilizador gravado com sucesso.", "success" );
		redirect( "admin/index.php?mod=utilizadores&view=utilizadores" );
	}




	 /* Models */
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
	function get_utilizador($id){
		$sql = "SELECT * FROM users WHERE id = ".$id;
		$query = mysql_query($sql);

		if($query)
			return mysql_fetch_object($query);

		return false;
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
		$sender_host = $_POST["sender_host"];

		//insert do user
		$sql = "INSERT INTO `users` (`first_name`, `last_name`, `username`, `email`, `password`, `is_active`, `user_group`, `sender_host`) VALUES ( '{$user_first_name}', '{$user_last_name}', '{$user_username}', '{$user_email }', '".md5($user_password)."', {$is_active}, {$user_group}, '".$sender_host."')";
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

		return true;

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



	function update_user($id){

		

		//user logo - if uploaded
		if(!empty($_FILES["image"])){
			$file_path = $_FILES["image"]["tmp_name"];
			$fp = fopen($file_path, 'r');
			$image_data = fread($fp, filesize($file_path));
			$image_data = addslashes($image_data);
			fclose($fp);
		}

		$user_username = $_POST["user_username"];
		$user_first_name = $_POST["user_first_name"];
		$user_last_name = $_POST["user_last_name"];
		$user_email = $_POST["user_email"];
		$is_active = isset($_POST["is_active"]) ? 1:0;
		$user_password = $_POST["user_password"];
		$user_group = $_POST["user_group"];
		$user_group_permissions = $_POST["user_group_permissions"];
		$user_sender_permissions = $_POST["user_sender_permissions"];
		$sender_host = $_POST["sender_host"];


		//update à password
		if(!empty($user_password))
			$sql_password = "`password` = '".md5($user_password)."', ";

		//update ao user
		$sql = "UPDATE `users` SET `first_name` = '{$user_first_name}', `last_name` = '{$user_last_name}', `username` = '{$user_username}', `email` = '{$user_email}', ".$sql_password." `is_active` = {$is_active}, `user_group` = {$user_group}, `sender_host` = '".$sender_host."', `image_blob` = '{$image_data}' WHERE id = {$id}";
		$query = mysql_query($sql);

		//update as permissoes
		$this->update_user_permissions($id, $user_group_permissions);
		$this->update_sender_permissions($id, $user_sender_permissions);

		//
		unset($_POST);

		tools::notify_add( "Utilizador actualizado com sucesso.", "success" );
		//redirect( "admin/index.php?mod=utilizadores&view=utilizadores" );

	}

	//listar os grupos a quais os utilizadores têm permissão
	public function get_group_permissions($user_id){	//also on subscribers mod

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
	function render_user_groups($group_id){
		$groups = $this->get_user_groups();

		$ret = "";

		foreach ($groups as $group) {
			$selected = ($group->id == $group_id) ? "selected=\"selected\"" : "";
			$ret .= "<option ".$selected." value=\"".$group->id."\">".$group->name."</option>";
		}
		return $ret;

	}
	function get_user_groups(){	//Moved to utilizadores
		$sql = "SELECT id, name FROM user_groups";
		$query = mysql_query($sql);

		if($query){
			while($row = mysql_fetch_object($query))
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

	//listar os grupos a quais os utilizadores têm permissão
	public function get_sender_permissions($user_id){	//compy on send.mod

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

	function update_sender_permissions($user_id, $permissions){	//moved to users

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




}

 ?>