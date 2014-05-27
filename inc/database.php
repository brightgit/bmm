<?php
	switch($_SERVER['HTTP_HOST']){
		//development
		case "localhost":
			$host = 'server9.brightminds.pt';
			$user = 'implanto_bmm_3';
			$password = 'SpoilsOutingMuslinFalls77';
			$database = 'implanto_bmm_3';
		break;
		//production
		default:
			$host = 'localhost';
			$user = 'implanto_bmm_3';
			$password = 'SpoilsOutingMuslinFalls77';
			$database = 'implanto_bmm_3';
		break;
	}

	//$debug = new Debug();


	$connection = mysql_pconnect($host, $user, $password) or die( mysql_error() );
	$db = mysql_select_db($database) or die( mysql_error() );
	mysql_query('SET NAMES \'utf8\';');

?>