<?php
	switch($_SERVER['HTTP_HOST']){
		//development
		case "localhost":
			$host = 'pme24.net';
			$user = 'pmenet_pmenet';
			$password = 'xk(s*64tVvTX';
			$database = 'pmenet_bmm_v3';
		break;
		//production
		default:
			$host = 'pme24.net';
			$user = 'pmenet_pmenet';
			$password = 'xk(s*64tVvTX';
			$database = 'pmenet_bmm_v3';
		break;
	}

	$debug = new Debug();

	$connection = mysql_pconnect($host, $user, $password) or $debug->dbErrors();
	$db = mysql_select_db($database) or $debug->dbErrors();
	mysql_query('SET NAMES \'utf8\';');

?>