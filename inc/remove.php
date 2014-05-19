<?php

//ini settings
header("Content-Type: text/html; charset=utf-8");
error_reporting(0);
date_default_timezone_set("Europe/Lisbon");

//include core
require_once('Core.php');
$core = new Core('bo');

//dados consoante o cliente
$client_id = BRIGHT_mail_feedback::get_client_id();
$client = BRIGHT_mail_feedback::get_client($client_id);

class Subscriber{

	function check($email, $token){

		$salt = "bright";
		$sql = "SELECT id, is_active, email, md5(concat(email, \"".$salt."\")) as token from subscribers having token = '".$token."'";
		//echo $sql;

		$query = mysql_query($sql);

		if($query){
			$results = mysql_num_rows($query);

			//aqui já existe confirmação de que podemos remover o subscritor
			if($results > 0){
				$subscriber = mysql_fetch_object($query);

				if($subscriber->email == $email)
					return $subscriber;
				else
					return false;
			}

		}

		return false;
	}

	//recebe um objecto subscriber e insere-o na lista de exclusão
	//o subscritor será colocado numa lista à parte para evitar exportações erradas no futuro...é fácil esquecer uma flag que indica que foi excluído
	function add_to_exclusion_list($email){
		$sql = "UPDATE subscribers SET is_active = 0, requested_exclusion = 1 WHERE email = '{$email}'";
		$query = mysql_query($sql);

		return $query;
	}

}

//0 - sacar as vars essenciais
$remove_token = $_GET["remove_token"];
$email = $_GET["email"]; ?>


<html>
<head>
	<title><?php echo utf8_encode($client->name) ?> - Remover subscri&ccedil;&atilde;o</title>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url("inc/js/bootstrap/css/bootstrap.css") ?>" />
	<link href='http://fonts.googleapis.com/css?family=Open+Sans|Numans' rel='stylesheet' type='text/css' />
	<link rel="stylesheet" type="text/css" href="<?php echo base_url("inc/css/remove.css") ?>" />
	<style type="text/css">

	</style>
</head>
<body>
	<form action="" method="post">

		<div class="container main">

			<div class="breathe">

				<div class="logo-wrap"><a href="http://www.<?php echo $client->domain ?>"><img alt="Logo" src="bmm/inc/img/admin/client_logo.png" /></a></div>
				<h2 class="text-center">Remover subscri&ccedil;&atilde;o</h2>

				<!-- content starts here -->
				<?php if(empty($_POST)): ?>

				<div class="well text-center">
					<p>Por forma confirmar a remo&ccedil;&atilde;o da nossa mailing list, por favor insira o seu endere&ccedil;o de email abaixo.</p>
					<p>Assim que o fizer, deixar&aacute; de receber as nossas mensagens no seu endere&ccedil;o de email</p>
				</div>

			</div>

			<div class="slightly-greyish">
				<div class="breathe force-center">
					<input type="text" name="email" value="" />
					<input type="hidden" name="remove_token" value="<?php echo $remove_token ?>" />
					<br />
					<br />
					<input type="submit" value="Confirmar" class="btn btn-primary" />
				</div>
			</div>

			<!-- content ends here -->
			<?php else: ?>

			<?php 

			//data
			$remove_token = $_POST["remove_token"];
			$email = $_POST["email"];

			//check
			$subscriber = new Subscriber;
			$check = $subscriber->check($email, $remove_token); ?>

			
			<?php if($check): $add_to_exclusion = $subscriber->add_to_exclusion_list($email); ?>
				<div class="text-center">
					<div class="alert alert-success">Removido com sucesso</div>
					<p>O endereço <b><?php echo $email ?></b> foi removido completamente dos nossos registos.</p>
				</div>
			<?php else: ?>
				<div class="text-center">
					<p>Os dados inseridos n&atilde;o coincidem com a chave de remo&ccedil;&atilde;o ou o endere&ccedil;o j&aacute; n&atilde;o se encontra nos nossos registos.</p>
					<p>Pode optar por <a href="<?php echo $_SERVER["HTTP_REFERER"] ?>">tentar novamente</a>.</p>
					<p>Em caso de dificuldade favor utilize o endereço <a href="mailto:<?php echo $core->settings->alternate_remove_email ?>?subject=Remover"><?php echo $core->settings->alternate_remove_email ?></a> onde lhe responderemos t&atilde;o brevemente quanto poss&iacute;vel</p>
				</div>
		<?php endif;

			endif; ?>

		</div>

	</form>
</body>
</html>

