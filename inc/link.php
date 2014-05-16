<?php 

//ini settings
header("Content-Type: text/html; charset=utf-8");
error_reporting(0);
date_default_timezone_set("Europe/Lisbon");

//include core
require_once('Core.php');

$core = new Core('bo');

if(!empty($_GET["envio_id"]) && !empty($_GET["url"]) && !empty($_GET["email"]) ){

	//instanciar feedback e registar o click
	$feedback = new BRIGHT_mail_feedback;
	$feedback->click_register();

}

?>