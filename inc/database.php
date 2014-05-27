<?php
	switch($_SERVER['HTTP_HOST']){
		//development
		case "localhost":
			$host = 'pme24.net';
			$user = 'pmenet_hugo';
			$password = 'Hugo#$12';
			$database = 'pmenet_bmmv3_t';
		break;
		//production
		default:
			$host = 'pme24.net';
			$user = 'pmenet_hugo';
			$password = 'Hugo#$12';
			$database = 'pmenet_bmmv3_t';
		break;
	}

	//$debug = new Debug();


	$connection = mysql_pconnect($host, $user, $password) or die( mysql_error() );
	$db = mysql_select_db($database) or die( mysql_error() );
	mysql_query('SET NAMES \'utf8\';');

?>