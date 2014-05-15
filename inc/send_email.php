<?php

//mail("franco.silva@bright.pt", "CRON ".$_SERVER["HTTP_HOST"], "Cron done from " . $_SERVER["REMOTE_ADDR"]);
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once('Core.php');
date_default_timezone_set("Europe/Lisbon");
//TODO: Start the session or include core
$core = new Core('bo');

#Settings
#API KEY para este dominio
$api_key = $core->settings->sender_api_key;

$emails_from = array( $core->settings->sender_email_from => $core->settings->sender_name);

switch ($_SERVER["HTTP_HOST"]) {
	case 'localhost':
		require_once("libs/Swift-4.2.2/lib/swift_required.php");
		break;
	
	default:
		require_once($core->settings->swift_absolute_path);
		break;
}



#O nosso servidor não tem esta função
if (!function_exists('http_response_code')) {
	function http_response_code($code = NULL) {

		if ($code !== NULL) {

			switch ($code) {
				case 100: $text = 'Continue'; break;
				case 101: $text = 'Switching Protocols'; break;
				case 200: $text = 'OK'; break;
				case 201: $text = 'Created'; break;
				case 202: $text = 'Accepted'; break;
				case 203: $text = 'Non-Authoritative Information'; break;
				case 204: $text = 'No Content'; break;
				case 205: $text = 'Reset Content'; break;
				case 206: $text = 'Partial Content'; break;
				case 300: $text = 'Multiple Choices'; break;
				case 301: $text = 'Moved Permanently'; break;
				case 302: $text = 'Moved Temporarily'; break;
				case 303: $text = 'See Other'; break;
				case 304: $text = 'Not Modified'; break;
				case 305: $text = 'Use Proxy'; break;
				case 400: $text = 'Bad Request'; break;
				case 401: $text = 'Unauthorized'; break;
				case 402: $text = 'Payment Required'; break;
				case 403: $text = 'Forbidden'; break;
				case 404: $text = 'Not Found'; break;
				case 405: $text = 'Method Not Allowed'; break;
				case 406: $text = 'Not Acceptable'; break;
				case 407: $text = 'Proxy Authentication Required'; break;
				case 408: $text = 'Request Time-out'; break;
				case 409: $text = 'Conflict'; break;
				case 410: $text = 'Gone'; break;
				case 411: $text = 'Length Required'; break;
				case 412: $text = 'Precondition Failed'; break;
				case 413: $text = 'Request Entity Too Large'; break;
				case 414: $text = 'Request-URI Too Large'; break;
				case 415: $text = 'Unsupported Media Type'; break;
				case 500: $text = 'Internal Server Error'; break;
				case 501: $text = 'Not Implemented'; break;
				case 502: $text = 'Bad Gateway'; break;
				case 503: $text = 'Service Unavailable'; break;
				case 504: $text = 'Gateway Time-out'; break;
				case 505: $text = 'HTTP Version not supported'; break;
				default:
				$text = '';
				break;
			}

			$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

			header($protocol . ' ' . $code . ' ' . $text);

			$GLOBALS['http_response_code'] = $code;

		} else {

			$code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);

		}

		return $code;

	}
}


#Settings do swift
$transport = Swift_SmtpTransport::newInstance($core->settings->sender_host, $core->settings->sender_smtp_port)
			->setUsername($core->settings->sender_username)
			->setPassword($core->settings->sender_password)
			;

$mailer = Swift_Mailer::newInstance($transport);
$mailer->registerPlugin(new Swift_Plugins_AntiFloodPlugin(100));
$mailer->registerPlugin(new Swift_Plugins_AntiFloodPlugin(100, 5));

if( !isset($_GET['api']) || $_GET['api'] != $api_key ){
	http_response_code(404);
	die('');
}


$query = "SELECT mensagens_enviadas.*, `subscribers`.`nome`, `subscribers`.`email` 
FROM `mensagens_enviadas` 
LEFT JOIN `subscribers` on `subscribers`.`id` = `mensagens_enviadas`.`destino`  
WHERE `output` = 3 
	LIMIT 100"; //Limitado a 100 emails
	

	$res = mysql_query( $query ) or die( mysql_error() );
	if(mysql_num_rows($res) == 0 ){
		http_response_code(207);
		die("");
	}

	//adicionar doctype devido a Outlook
	$doctype = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";

	while( $mensagem = mysql_fetch_object($res) ) {
		//echo '1';
		//Mensagem
		$to = array($mensagem->email => $mensagem->email);
		$html_body = BRIGHT_mail_feedback::inject($mensagem->mensagem, $mensagem->email, $mensagem->mensagem_id);

		//incrementar no enviados
		$query = "UPDATE stats SET mensagens_enviadas = mensagens_enviadas + 1";
		mysql_query($query);

		//Mandrill
		if (true) {
			require_once 'mandrill-api-php/src/Mandrill.php'; //Not required with Composer
				$mandrill = new Mandrill('jo8Bhu48xPYosSwJooS0Gg');

				$message = array(
			        'html' => $html_body,
			        'text' => $mensagem->mensagem_text,
			        'subject' => $mensagem->assunto,
			        'from_email' => $core->settings->sender_email_from,
			        'from_name' => $core->settings->sender_name,
			        'to' => array(
			            array(
			                'email' => $mensagem->email,
			                'name' => false,
			                'type' => 'to'
			            )
			        ),
			        'headers' => array('Reply-To' => $core->settings->return_path),
			        'important' => false,
			        'track_opens' => null,
			        'track_clicks' => false,
			        'auto_text' => null,
			        'auto_html' => null,
			        'inline_css' => null,
			        'url_strip_qs' => null,
			        'preserve_recipients' => null,
			        'view_content_link' => null,
			        'bcc_address' => NULL,
			        'tracking_domain' => null,
			        'signing_domain' => null,
			        'return_path_domain' => null,
			        'merge' => true,
			        'global_merge_vars' => NULL,
			        'merge_vars' => NULL,
			        'tags' => NULL,
			        'subaccount' => NULL,
			        'google_analytics_domains' => NULL,
			        'google_analytics_campaign' => NULL,
			        'metadata' => NULL,
			        'recipient_metadata' => NULL,
			        'attachments' => NULL,
			        'images' => NULL
			    );

			    $async = false;
			    $ip_pool = 'Main Pool';
			    $send_at = false;
			    $result = $mandrill->messages->send($message, $async, $ip_pool, $send_at);

				//estado final, a ser salvo no ficheiro		
				$reject = $result[0]["reject_reason"];
			    $sent_mandrill = empty($reject) ? true : false;

			    //adicionar hard-bounce
			    if($reject == "hard-bounce"){
			    	$bounce_query = "UPDATE subscribers set is_active = 0, hard_bounces_count = hard_bounces_count + 1, date_updated = NOW() WHERE email = '".$mensagem->email."'";
			    	mysql_query($bounce_query);
			    }

			    //inactivar, foi marcado como spam
			    if($reject == "spam"){
			    	$spam_query = "UPDATE subscribers SET is_active = 0, requested_exclusion = 1, date_updated = NOW() WHERE email = '".$mensagem->email."'";
			    	mysql_query($spam_query);
			    }
			    	
			    $sent_mandrill = true; //é preferível passar a true por forma a seguir para o próximo passo. Se amanhã o Mandrill enviar um novo code de refuse que não esteja aqui, vai empacar o CRON
			    
			    //die("tried sending via mandrill");


		}
		//End Mandrill
		else{

			$message = Swift_Message::newInstance()
			->setSubject( $mensagem->assunto )
			->setFrom( $emails_from )
			->setTo($to)
			->addPart($mensagem->mensagem)
			->setBody( $doctype.$html_body, 'text/html')
			->setReturnPath($core->settings->return_path)
			->setCharset('utf-8');

			$headers = $message->getHeaders();

			$headers->get('Subject')->setValue($mensagem->assunto);
			$headers->get('Content-Type')->setvalue('text/html');
			$headers->get('Content-Type')->setParameter('charset', 'utf-8');
			$headers->get('Date')->setTimestamp(time());
			$headers->get('From')->setNameAddresses($emails_from);
			$headers->get('To')->setNameAddresses($to);
			$mail_id = time().'.'.md5($mensagem->email) . '@'.$core->settings->sender_domain;
			$headers->get('Message-ID')->setId($mail_id);
			$headers->get('Return-Path')->setAddress($core->settings->return_path);

			$send_mail = $mailer->send($message);
		}
		

		if ($send_mail || $sent_mandrill) //temporário até determinar o problema com o Mandrill
		{
			require_once("file_class.php");
			$bcsv = new bcsv();


			$query = "SELECT *, subscribers.email FROM mensagens_enviadas LEFT JOIN subscribers on mensagens_enviadas.destino = subscribers.id WHERE `mensagens_enviadas`.`id` = ".$mensagem->id;
			$res2 = mysql_query($query) or die( mysql_error() );
			$row = mysql_fetch_object( $res2 );

			$client_id = BRIGHT_mail_feedback::get_client_id();
			$bcsv->initiate( $client_id );

			$log_info["client"] = $client_id;
			$log_info["mensagem_id"] = $mensagem->mensagem_id;

			$bcsv->open_enviadas("write", $log_info);

			$insert["mail_id"] = $row->mail_id;

			$insert["email"] = $row->email;

			$insert["hora"] = $row->hora;

			$insert["output"] = "Sucesso";
			$bcsv->add_mensagem_enviada( $insert );
			$bcsv->close();

			$query = "DELETE from `mensagens_enviadas` WHERE id = ".$mensagem->id;
			//echo $query;
			//echo $query . "<hr />";
			if( mysql_query($query) ) {
				
			}else{
				//http_response_code(500);
				//die("");
			}
		}
		else
		{

			require_once("file_class.client.php");
			$bcsv = new bcsv_client();


			$query = "SELECT *, subscribers.email FROM mensagens_enviadas LEFT JOIN subscribers on mensagens_enviadas.destino = subscribers.id WHERE `mensagens_enviadas`.`id` = ".$mensagem->id;
			$res2 = mysql_query($query) or die( mysql_error() );
			$row = mysql_fetch_object( $res2 );

			$bcsv->initiate( $row->mensagem_id );
			$insert["mail_id"] = $row->mail_id;

			$insert["email"] = $row->email;

			$insert["hora"] = $row->hora;

			$insert["output"] = "Erro no envio";
			$bcsv->add_mensagem_enviada( $insert );
			$bcsv->close();

			$query = "DELETE from `mensagens_enviadas` WHERE id = ".$mensagem->id;
			if( mysql_query($query) ) {
				
			}else{
				//http_response_code(500);
				//die("");
			}
		}

		unset($message);

	}

	$core->__destruct();

?>