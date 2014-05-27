<?php

class BRIGHT_User{

	public $id;
	public $first_name;
	public $last_name;
	public $user_name;
	public $is_admin;
	public $permissions;


	function __construct(){

		if(!empty($_SESSION["user"]->id)){
			$this->id = (int) $_SESSION["user"]->id;
			$this->first_name = $_SESSION["user"]->first_name;
			$this->last_name = $_SESSION["user"]->last_name;
			$this->username = $_SESSION["user"]->username;
			$this->is_admin = (int) $_SESSION["user"]->is_admin;

			//group permissions - tem que retornar um array com os ids dos grupos a que tem acesso
			
		}

	}

}

?>
