<?php

//mail("franco.silva@bright.pt", "CRON ".$_SERVER["HTTP_HOST"], "Cron done from " . $_SERVER["REMOTE_ADDR"]);
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once('Core.php');
date_default_timezone_set("Europe/Lisbon");
//TODO: Start the session or include core
$core = new Core('bo');

//Logs an aplication erro
function log_error( $e )
{
	$query = "insert into send_email_errors set error = '".mysql_real_escape_string($e)."'";
	$res = mysql_query($query);

	//Vamos enviar email?
	$query = "select * from send_email_errors";

	if ( is_int( mysql_num_rows( mysql_query($query) ) / 100 ) ) {
		mail("hugo.silva@bright.pt", "BMM - 100 erros", "Dominio: ".$_SERVER["HTTP_HOST"]);
	}
	die("");
	exit("");
}

#Settings
#API KEY para este dominio
#$api_key = $core->settings->sender_api_key;

//$emails_from = array( $core->settings->sender_email_from => $core->settings->sender_name);

$query  = "SELECT mensagens_enviadas.id, mensagens_enviadas.mensagem_id, mensagens_enviadas.envio_id, subscribers.id as subscriber_id, subscribers.email as email, subscribers.hard_bounces_count as subscriber_hard_bounces_count, subscribers.nome as subscriber_nome, subscribers.data_nascimento as subscriber_data_nascimento, subscribers.sexo as subscriber_sexo, subscribers.telefone_1, subscribers.telefone_2, mensagens.mensagem, mensagens.mensagem_text, mensagens.assunto, mensagens.url, mensagens.user_id, senders.email as sender_email, senders.email_from as sender_name, senders.return_path as sender_return_path
	from mensagens_enviadas 
	inner join mensagens on mensagens_enviadas.mensagem_id = mensagens.id 
	inner join subscribers on mensagens_enviadas.destino = subscribers.id 
	inner join senders on senders.id = mensagens_enviadas.sender_id 
	where mensagens.id is not null
	limit 100
	";

	// echo '<hr />';
	// echo $query;
	// echo '<hr />';

	$res = mysql_query( $query ) or die( mysql_error() );
	if(mysql_num_rows($res) == 0 ){	//No messages to be sent.
		die("Sem mensagens em espera.");
	}

	//adicionar doctype devido a Outlook
	$doctype = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";

	while( $mensagem = mysql_fetch_object($res) ) {

		//echo '<hr />';
		//var_dump($mensagem);
		//echo '<hr />';

		//echo '1';
		//Mensagem
		//$to = array($mensagem->subscriber_email => $mensagem->subscriber_email);
		$html_body = BRIGHT_mail_feedback::inject($mensagem); //passa a receber apenas um parâmetro (informação da newsletter + informação do subscriber num object)

		//Mandrill
		if (true) {
			require_once 'mandrill-api-php/src/Mandrill.php'; //Not required with Composer
				$mandrill = new Mandrill('jo8Bhu48xPYosSwJooS0Gg');

				$message = array(
			        'html' => $html_body,
			        'text' => $mensagem->mensagem_text,
			        'subject' => $mensagem->assunto,
			        'from_email' => $mensagem->sender_email,
			        'from_name' => $mensagem->sender_name,
			        'to' => array(
			            array(
			                'email' => $mensagem->email,
			                'name' => false,
			                'type' => 'to'
			            )
			        ),
			        'headers' => array('Reply-To' => $mensagem->sender_return_path),
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
			    	$spam_query = "UPDATE subscribers SET is_active = 0, requested_exclusion = 0, date_updated = NOW() WHERE email = '".$mensagem->email."'";
			    	mysql_query($spam_query);
			    }
			    	
			    $sent_mandrill = true; //é preferível passar a true por forma a seguir para o próximo passo. Se amanhã o Mandrill enviar um novo code de refuse que não esteja aqui, vai empacar o CRON
			    
			    //die("tried sending via mandrill");


		}

		//$sent_mandrill = true;
		require_once("file_class.php");
		$bcsv = new bcsv();


		$query = "SELECT mensagens_enviadas.*, subscribers.email, envios.user_id
			FROM mensagens_enviadas 
			LEFT JOIN subscribers on mensagens_enviadas.destino = subscribers.id 
			left join envios on envios.id = mensagens_enviadas.envio_id
			WHERE `mensagens_enviadas`.`id` = ".$mensagem->id;

		$res2 = mysql_query($query) or die( mysql_error() );
		$row = mysql_fetch_object( $res2 );

		//echo '<hr />';
		//echo $query;
		//echo '<hr />';
		//var_dump($row);
		//echo '<hr />';
		

		/* Bloco extra aqui no meio*/
		//Meter nos stats
		$query = "select * from stats where `month` = month( now() ) and `year` = year( now() ) and user_id = '".$row->user_id."'";
		$res_stats = mysql_query( $query ) or log_error( mysql_error().$query );
		if ( $row2 = mysql_fetch_array($res_stats) ) {	//temos vamos incrementar
			$query ="update stats set mensagens_enviadas = mensagens_enviadas + 1 where id = '".$row2["id"]."'";
			mysql_query($query) or log_error( mysql_error().$query );
		}else{
			$query = "insert into stats 
			set mensagens_enviadas = 1, mensagens_abertas = 0, user_id = '".$row->user_id."', month = '".date("n")."', year = '".date("Y")."', sender_id = '".$row->sender_id."'";
			mysql_query($query) or log_error( mysql_error().$query );
		}
		/* END Bloco extra aqui no meio*/


		
		//echo $query;
		//$client_id = BRIGHT_mail_feedback::get_client_id();
		$bcsv->initiate( $row->user_id );

		$bcsv->open_enviadas("write", $mensagem->envio_id);

		$insert["mail_id"] = $row->destino;

		$insert["email"] = $row->email;

		$insert["hora"] = $row->hora;

		if ( $sent_mandrill)
		{
			$insert["output"] = "Sucesso";
		}else{
			$insert["output"] = "Erro no envio";
			
		}
		$bcsv->add_mensagem_enviada( $insert );
		$bcsv->close();

		$query = "DELETE from `mensagens_enviadas` WHERE id = ".$mensagem->id;

		if( mysql_query($query) ) {
			
		}else{
			//http_response_code(500);
			//die("");
		}
		unset($message);

	}

	$core->__destruct();

?>