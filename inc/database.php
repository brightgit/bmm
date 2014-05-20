<?php
	switch($_SERVER['HTTP_HOST']){
		//development
		case "localhost":
			//$host = 'pme24.net';
			//$user = 'pmenet_bmmv3';
			//$password = 'Hugo#$12';
			//$database = 'pmenet_bmm_v3';
			$host = 'localhost';
			$user = 'root';
			$password = '';
			$database = 'bmm';
		break;
		//production
		default:
			$host = 'pme24.net';
			$user = 'pmenet_bmmv3';
			$password = 'Hugo#$12';
			$database = 'pmenet_bmm_v3';
		break;
	}

	//$debug = new Debug();


	$connection = mysql_pconnect($host, $user, $password) or die( mysql_error() );
	$db = mysql_select_db($database) or die( mysql_error() );
	mysql_query('SET NAMES \'utf8\';');

?>