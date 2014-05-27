<?php

class Backup{
	public $view = "backup/do_backup";


	function __construct(){
		if (!empty($_GET["view"])) {
			$this->$_GET["view"]();
		}
	}

	function do_backup() {
		//Inicialização dos dados do utilizador
		$query = "select * from users where id = '".$_GET["user_id"]."'";
		$res = mysql_query($query);
		$user = mysql_fetch_array($res);

		//Inicialização da pasta de backup


		//Copiar media


	}

}

?>
