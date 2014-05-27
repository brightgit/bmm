<?php 
/**
* 
*/
class Settings_list
{
	public $view = "settings/settings_list";
	
	function __construct()
	{
		if (!empty($_GET["view"])) {
			$this->$_GET["view"]();
		}

	}

	/* Controllers */

	function listar() { }
	function delete_sender() {
		$query = "delete from senders where id = '".$_GET["id"]."'";
		mysql_query($query) or die_sql( $query );
		tools::notify_add( "Sender eliminado com sucesso.", "success" );
		redirect( "admin/index.php?mod=settings_list" );
	}



	/* MODELS */
	function get_senders(){
		$query = "SELECT * FROM senders";
		$result = mysql_query($query);

		while ($row = mysql_fetch_object($result)) {
			$output[] = $row;
		}

		return $output;
	}

	function save(){

		//file upload
		//$client_logo = $_FILES["client_logo"];

		//upload client logo
		/* if($client_logo["size"] > 0){
			$destination = $this->base_path . "/inc/img/admin/client_logo.png";
			$upload = move_uploaded_file($client_logo["tmp_name"], $destination);
		} */
		
		//load from post
		$this->bind_from_post();

		//só contém um registo
		mysql_query("DELETE FROM settings where user_id = '".$_SESSION["user"]->id."'");


        //como pode não existir o registo ainda, usar REPLACE
        //Save this as a backup
		//$sql = "REPLACE `settings` SET `user_id` = 1, `sender_host` = '{$this->sender_host}', `sender_smtp_port` = '{$this->sender_smtp_port}', `sender_username` = '{$this->sender_username}', `sender_password` = '{$this->sender_password}', `sender_name` = '{$this->sender_name}', `sender_email_from` = '{$this->sender_email_from}', `return_path` = '{$this->return_path}', `return_path_password` = '{$this->return_path_password}', `sender_domain` = '{$this->sender_domain}', `ck_upload_url` = '{$this->ck_upload_url}', `ck_upload_dir` = '{$this->ck_upload_dir}', `swift_absolute_path` = '{$this->swift_absolute_path}', `remove_bounces` = {$this->remove_bounces}, `remove_bounces_count` = {$this->remove_bounces_count}, `unsubscribe_automatically` = {$this->unsubscribe_automatically}, `base_path` = '{$this->base_path}', `alternate_remove_email` = '{$this->alternate_remove_email}'";
		$sql = "REPLACE `settings` SET `user_id` = '".$_SESSION["user"]->id."', `ck_upload_url` = '{$this->ck_upload_url}', `ck_upload_dir` = '{$this->ck_upload_dir}', `base_path` = '{$this->base_path}'";
		
		$query = mysql_query($sql);

		if($query)
			tools::notify_add("Definições actualizadas com sucesso", "success");
		
		else{
			tools::notify_add("Erro na actualização das definições");
			return false;
		}



	}

	function bind_from_post(){

		//update senders
		$this->update_senders = $this->update_senders($_POST["sender"]);
		

		//$this->sender_host = $_POST["sender_host"]; 
		//$this->sender_smtp_port = (int) $_POST["sender_smtp_port"];
		//$this->sender_username = $_POST["sender_username"]; 
		//$this->sender_password = $_POST["sender_password"]; 
		//$this->sender_api_key = $_POST["sender_api_key"]; 	//Removed from here, query and form
		//$this->sender_name = $_POST["sender_name"]; 
		//$this->sender_email_from = $_POST["sender_email_from"]; 
		//$this->return_path = $_POST["return_path"]; 
		//$this->return_path_password = $_POST["return_path_password"]; 
		//$this->sender_domain = $_POST["sender_domain"]; 
		$this->ck_upload_url = $_POST["ck_upload_url"]; 
		$this->ck_upload_dir = $_POST["ck_upload_dir"]; 
		//$this->swift_absolute_path = $_POST["swift_absolute_path"]; 
		//$this->remove_bounces = $_POST["remove_bounces"]; 
		//$this->remove_bounces_count = $_POST["remove_bounces_count"]; 
		//$this->unsubscribe_automatically = $_POST["unsubscribe_automatically"];
		//$this->alternate_remove_email = $_POST["alternate_remove_email"];
		$this->base_path = $_POST["base_path"];


	}
	function update_senders($emails){
		

		foreach ($emails as $key => $email_info) {

			//o array emails contém [1,2,3 - update] ou [new - insert] submetidos via POST
			if(is_numeric($key)){

				if ( empty($email_info["email"]) || empty($email_info["email_from"]) || empty($email_info["return_path"])) {
					continue;
				}
				$sql = "UPDATE senders SET ";
				$sql .= "`email` = '".$email_info["email"]."', ";
				$sql .= "`email_from` = '".$email_info["email_from"]."', ";
				$sql .= "`return_path` = '".$email_info["return_path"]."', ";
				$sql .= "`return_path_password` = '".$email_info["return_path_password"]."'";
				$sql .= " WHERE id = " .$key;
				$query = mysql_query($sql) or die_sql( $query ); //update
			}
			//novo endereço - inserir
			else{
				foreach ($email_info as $email) {
					
				if ( empty($email["email"]) || empty($email["email_from"]) || empty($email["return_path"])) {
						continue;
					}
					$sql = "INSERT INTO senders SET ";
					$sql .= "`email` = '".$email["email"]."', ";
					$sql .= "`email_from` = '".$email["email_from"]."', ";
					$sql .= "`return_path` = '".$email["return_path"]."', ";
					$sql .= "`return_path_password` = '".$email["return_path_password"]."'";
					$query = mysql_query($sql) or die_sql( $query ); //insert
				}
			}

		}

		Tools::notify_add("Informação sobre remetentes actualizada com sucesso");
		redirect( "admin/index.php?mod=settings_list" );

	}



}

 ?>