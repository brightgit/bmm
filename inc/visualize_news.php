<?php

//var_dump($_GET);

//ini settings
header("Content-Type: text/html; charset=utf-8");
error_reporting(E_ALL);

date_default_timezone_set("Europe/Lisbon");

//include core
require_once('Core.php');

$core = new Core('bo');


if ( $_GET["url"] != "mensagem-teste" ) {
	//get the message
	$query = "SELECT mensagem_browser, assunto 
		FROM `mensagens`
		left join envios on envios.mensagem_id = mensagens.id WHERE envios.id = {$_GET["envio_id"]}";
}else{
	$query = "SELECT mensagem_browser, assunto 
		FROM `mensagens` WHERE id = {$_GET["envio_id"]}";	
}
//echo $query;

//var_dump( $_GET );
//die();


$res = mysql_query($query) or die(mysql_error().$query);
$news = mysql_fetch_object($res);

//var_dump($news);

//if there's no message
if( !$news ){
	die("Erro: Newsletter não encontrada");

}else{

	$feedback = new BRIGHT_mail_feedback;

	//if there's tracking information, register
	if(!empty($_GET["envio_id"]) && !empty($_GET["url"]) && !empty($_GET["email"]) && $_GET["url"] != "mensagem-teste") {
		$feedback->insert( );
	}
	?>

	<html>
		<head>
			<title><?php echo $news->assunto ?></title>
		</head>
		<body>
			<?php echo strip_tags( $feedback->inject_browser( $news->mensagem_browser, $_GET["email"], $_GET["envio_id"] ), "<p><strong><caption><span><h1><h2><h3><h4><h5><h6><div><a><img><br><table><tr><td><thead><tbody><body><head><title><meta>"); ?>
			<?php //echo strip_tags($news->mensagem_browser, "<p><div><a><img><br><table><tr><td><thead><tbody><body><head><title><meta>"); ?>
		</body>
	</html>

<?php

}

//that's a wrap
$core->__destruct();

?>
