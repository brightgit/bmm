<?php

class Settings{

	public $sender_host;
	public $sender_smtp_port;
	public $sender_username;
	public $sender_password;
	public $sender_name;
	public $sender_api_key;
	public $sender_email_from;
	public $return_path;
	public $return_path_password;
	public $sender_domain;
	public $ck_upload_url;
	public $ck_upload_dir;
	public $swift_absolute_path;
	public $remove_bounces;
	public $remove_bounces_count;
	public $unsubscribe_automatically;
	public $alternate_remove_email;
	public $base_path;

	//added
	public $base_dir;

	function __construct($mode = '') {
		$this->get();

		//act upon act
		if(!empty($_GET["act"])){
			$act = $_GET["act"];
			$this->$act();
		}
	}

	function delete_sender(){
		$sender_id = (int) $_GET["id"];
		$sql = "DELETE FROM senders WHERE id = " . $sender_id;
		return $query = mysql_query($sql);
	}

	function get(){
		$sql = "SELECT * FROM settings LIMIT 1";
		$query = mysql_query($sql);

		if($query){
			$settings = mysql_fetch_object($query);
			$this->sender_host = $settings->sender_host;
			$this->sender_smtp_port = (int) $settings->sender_smtp_port;
			$this->sender_username = $settings->sender_username;
			$this->sender_password = $settings->sender_password;
			$this->sender_name = $settings->sender_name;
			$this->sender_api_key = $settings->sender_api_key;
			$this->sender_email_from = $settings->sender_email_from;
			$this->return_path = $settings->return_path;
			$this->return_path_password = $settings->return_path_password;
			$this->sender_domain = $settings->sender_domain;
			$this->ck_upload_url = $settings->ck_upload_url;
			$this->ck_upload_dir = $settings->ck_upload_dir;
			$this->swift_absolute_path = $settings->swift_absolute_path;
			$this->remove_bounces = (int) $settings->remove_bounces;
			$this->remove_bounces_count = (int) $settings->remove_bounces_count;
			$this->unsubscribe_automatically = (int) $settings->unsubscribe_automatically;
			$this->alternate_remove_email = $settings->alternate_remove_email;
			$this->base_path = $settings->base_path;

		}
		else{
			echo "<div class=\"alert alert-warning\">Failed to load settings</div>";
		}

	}

	function update_senders($emails){
		
		foreach ($emails as $key => $email_info) {

			//o array emails contém [1,2,3 - update] ou [new - insert] submetidos via POST
			if(is_numeric($key)){

				$sql = "UPDATE senders SET ";
				$sql .= "`email` = '".$email_info["email"]."', ";
				$sql .= "`email_from` = '".$email_info["email_from"]."', ";
				$sql .= "`return_path` = '".$email_info["return_path"]."'";
				$sql .= " WHERE id = " .$key;

				$query = mysql_query($sql); //update
			}
			//novo endereço - inserir
			else{
				foreach ($email_info as $email) {
					
					$sql = "INSERT INTO senders SET ";
					$sql .= "`email` = '".$email["email"]."', ";
					$sql .= "`email_from` = '".$email["email_from"]."', ";
					$sql .= "`return_path` = '".$email["return_path"]."'";

					$query = mysql_query($sql); //insert
				}
			}

		}

	}

	function bind_from_post(){

		//update senders
		$this->update_senders = $this->update_senders($_POST["sender"]);
		

		$this->sender_host = $_POST["sender_host"]; 
		$this->sender_smtp_port = (int) $_POST["sender_smtp_port"];
		$this->sender_username = $_POST["sender_username"]; 
		$this->sender_password = $_POST["sender_password"]; 
		$this->sender_api_key = $_POST["sender_api_key"]; 
		$this->sender_name = $_POST["sender_name"]; 
		$this->sender_email_from = $_POST["sender_email_from"]; 
		$this->return_path = $_POST["return_path"]; 
		$this->return_path_password = $_POST["return_path_password"]; 
		$this->sender_domain = $_POST["sender_domain"]; 
		$this->ck_upload_url = $_POST["ck_upload_url"]; 
		$this->ck_upload_dir = $_POST["ck_upload_dir"]; 
		$this->swift_absolute_path = $_POST["swift_absolute_path"]; 
		$this->remove_bounces = $_POST["remove_bounces"]; 
		$this->remove_bounces_count = $_POST["remove_bounces_count"]; 
		$this->unsubscribe_automatically = $_POST["unsubscribe_automatically"];
		$this->alternate_remove_email = $_POST["alternate_remove_email"];
		$this->base_path = $_POST["base_path"];


	}

	function save(){

		//file upload
		$client_logo = $_FILES["client_logo"];

		//upload client logo
		if($client_logo["size"] > 0){
			$destination = $this->base_path . "/inc/img/admin/client_logo.png";
			$upload = move_uploaded_file($client_logo["tmp_name"], $destination);
		}
		
		//load from post
		$this->bind_from_post();

		//só contém um registo
		mysql_query("DELETE FROM settings");

	        //como pode não existir o registo ainda, usar REPLACE
		$sql = "REPLACE `settings` SET `user_id` = 1, `sender_host` = '{$this->sender_host}', `sender_smtp_port` = '{$this->sender_smtp_port}', `sender_username` = '{$this->sender_username}', `sender_password` = '{$this->sender_password}', `sender_name` = '{$this->sender_name}', `sender_api_key` = '{$this->sender_api_key}', `sender_email_from` = '{$this->sender_email_from}', `return_path` = '{$this->return_path}', `return_path_password` = '{$this->return_path_password}', `sender_domain` = '{$this->sender_domain}', `ck_upload_url` = '{$this->ck_upload_url}', `ck_upload_dir` = '{$this->ck_upload_dir}', `swift_absolute_path` = '{$this->swift_absolute_path}', `remove_bounces` = {$this->remove_bounces}, `remove_bounces_count` = {$this->remove_bounces_count}, `unsubscribe_automatically` = {$this->unsubscribe_automatically}, `base_path` = '{$this->base_path}', `alternate_remove_email` = '{$this->alternate_remove_email}'";
		
		$query = mysql_query($sql);

		if($query)
			echo "<div class=\"alert alert-success\">Defini&ccedil;&otilde;es actualizadas com sucesso</div>";
		
		else{
			echo "<div class=\"alert alert-success\">Erro na actualiza&ccedil;&atilde;o das defini&ccedil;&otilde;es ".mysql_error()."</div>";
			return false;
		}



	}

}

?>