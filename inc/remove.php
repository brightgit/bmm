<?php

//ini settings
header("Content-Type: text/html; charset=utf-8");
error_reporting(0);
date_default_timezone_set("Europe/Lisbon");

//include core
require_once('Core.php');
$core = new Core('bo');

//dados consoante o cliente

//$client_id = BRIGHT_mail_feedback::get_client_id();
//$client = BRIGHT_mail_feedback::get_client($client_id);

if ( isset($_GET["send_id"]) ) {	//envio Real
	$query ="select * from users left join envios on envios.user_id = users.id where envios.id = '".$_GET["send_id_teste"]."'";
	$res = mysql_query($query) or die_sql( $query );
	$client = mysql_fetch_object($res);
}elseif ( isset($_GET["send_id_teste"]) ){	//Envio teste
	$query ="select * from users where id = '".$_GET["send_id_teste"]."'";
	$res = mysql_query($query) or die_sql( $query );
	$client = mysql_fetch_object($res);
}else{
	mail( "hugo.silva@bright.pt", "BMM - remove.php - no user_id", var_export($_SESSION, true) );
}


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
		$query = "select group_concat( DISTINCT user_permissions.group_id separator ',' ) as ids from user_permissions
			left join envios on user_permissions.user_id = envios.user_id
			 where envios.id = '".$_POST["send_id"]."'
			 group by user_permissions.group_id
			 ";
		$res = mysql_query($query) or die( mysql_error() );
		$envio = mysql_fetch_array($res);

		$query = "select * from subscribers where email = '".$email."'";
		$res = mysql_query($query) or die( mysql_error() );
		$subs = mysql_fetch_array($res);

		//incomplete
		$query = "delete from subscriber_by_cat where id_subscriber = '".$subs["id"]."' and id_categoria = ''";

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
	<title><?php echo ucfirst($client->sender_host) ?> - Remover subscri&ccedil;&atilde;o</title>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url("inc/js/bootstrap/css/bootstrap.css") ?>" />
	<link href='http://fonts.googleapis.com/css?family=Open+Sans|Numans' rel='stylesheet' type='text/css' />
	<link rel="stylesheet" type="text/css" href="<?php echo base_url("inc/css/remove.css") ?>" />
	<style type="text/css">

	</style>
</head>
<body>
<?php if (isset($_GET["send_id_teste"])): ?>
	<form action="remove.php?send_id_teste=<?php echo $_GET["send_id_teste"]; ?>" method="post">
<?php else: ?>
	<form action="remove.php?send_id_teste=<?php echo $_GET["send_id"]; ?>" method="post">
<?php endif ?>

		<div class="container main">

			<div class="breathe">

				<div class="logo-wrap"><img alt="Logo" src="data:image/jpg;base64, <?php echo base64_encode( $client->image_blob ) ?>" /></div>
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
					<input type="hidden" name="send_id" value="<?php echo $_GET["send_id"]; ?>" />
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

