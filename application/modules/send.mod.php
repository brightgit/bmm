<?php 
/**
* 
*/
class Send
{
	public $view = "send/pre_send";
	public $data = FALSE;
	
	function __construct()
	{
		if ( !empty($_GET["view"]) ) {
			$this->$_GET["view"]();
		}
	}

	/* Controllers */
	function pre_send() { }
	function enviar() { $this->view = "send/enviar"; }
	function send_to_all() {
		$grupos = $_POST["groups"];

		//existindo grupos
		if(count($grupos) > 0){

			//partir para SQL
			$grupos_imploded = implode(",", $grupos);

			//buscar a mensagem
			$query = "SELECT * FROM `mensagens` WHERE `id`='".$_POST['mensagem_id']."'";
			$res = mysql_query($query) or die(mysql_error());
			if($mensagem = mysql_fetch_object($res)){
				//Adicionar envio
				$query = "insert into envios values(null, '".$mensagem->id."', '".$_SESSION["user"]->id."', '".$_POST["sender_id"]."', now())";
				mysql_query($query) or die( mysql_error() . $query );
				$res_envio = mysql_query("select * from envios order by id desc limit 1") or die( mysql_error() );
				$envio = mysql_fetch_array($res_envio);

				//BRIGHT_mail_feedback::insert_newsletter($_POST['mensagem_id']); //inserir na db
				$query ="INSERT INTO `mensagens_enviadas` (SELECT NULL, ".$mensagem->id.", '".$envio["id"]."', `id_subscriber`, '".$_POST["sender_id"]."', NULL, 3 FROM `subscriber_by_cat` inner join subscribers on subscribers.id = subscriber_by_cat.id_subscriber WHERE `subscriber_by_cat`.`id_categoria` IN (".$grupos_imploded.") and subscribers.is_active = 1 group by subscriber_by_cat.id_subscriber)";


				//echo "<textarea>" . $query . "</textarea>";
				$totals = mysql_query( $query ) or die( mysql_error() );
				$total_num = mysql_affected_rows();

				//increment sent count
				$month = date("m");
				$year = date("Y");
				//get month and year id
				$sql = "SELECT * FROM stats WHERE month = " . $month . " AND year = " . $year . " and user_id  = '".$_SESSION["user"]->id."'";
				$query = mysql_query($sql);
				$result = mysql_fetch_object($query);

				//update
				if($result){
					$sql = "UPDATE stats SET mensagens_enviadas = mensagens_enviadas + ".$total_num." WHERE id = ".$result->id;
					$query = mysql_query($sql);
				}
				//inserir novo mês
				else{
					$sql = "REPLACE stats SET mensagens_enviadas = mensagens_enviadas + 1, month = ". $month . ", year = " . $year . ", user_id  = '".$_SESSION["user"]->id. "'";
					$query = mysql_query($sql);
				}

				/* Removido na versão 3.0.0 pois é substituido pela tabela envios.
				//inserir nas estatisticas de newsletter o envio
				$sql = "INSERT INTO stats_newsletters (newsletter_id, date_sent) VALUES(".$mensagem->id.", CURRENT_TIMESTAMP)";
				$query = mysql_query($sql);
				*/

				
				//definir a mensagem como enviada - isto na realidade só acontece depois de ser feito o CRON...
				$query = "UPDATE `mensagens` SET `estado`='Enviada', `estado_code`='1' WHERE `id` = '".$mensagem->id."'";			
				mysql_query( $query ) or die(mysql_error());

				tools::notify_add( "A sua newsletter foi colocada em lista de espera. Por favor, aguarde...", "success" );
				redirect( "index.php?mod=newsletters&view=messages" );

			}else{
				return false;
			}	
		}

		//não existindo grupos...
		return false;


	}

	function send_test_email() {

		//Recebe os valores por POST;
		$query = "SELECT mensagens.* FROM `mensagens` WHERE `id`='".$_POST['mensagem_id']."'";
		$res = mysql_query($query) or die(mysql_error());
		if($mensagem = mysql_fetch_object($res)){

			//envio via Mandril
			if( true){

				//obter dados de envio (sender && user)
				$user_id = $_SESSION["user"]->id; //info do user que submeteu o envio
				$sender_id = $_POST["sender_id"]; //info do sender

				$sql = "SELECT * FROM senders WHERE id = " . $sender_id;
				$query = mysql_query($sql);
				$result = mysql_fetch_object($query);

				$return_path = $result->return_path;
				$from_email = $result->email;
				$from_name = $result->email_from;

				//adaptar 
				$mensagem->email = $_POST["text_email"];
				$mensagem->url = "mensagem-teste";
				$mensagem->envio_id = $mensagem->id; //falsear o envio
				
			
				require_once 'mandrill-api-php/src/Mandrill.php'; //Not required with Composer
				$mandrill = new Mandrill('jo8Bhu48xPYosSwJooS0Gg');

				$html_body = BRIGHT_mail_feedback::inject($mensagem);

				$message = array(
			        'html' => $html_body,
			        'text' => $mensagem->mensagem_text,
			        'subject' => $mensagem->assunto,
			        'from_email' => $from_email,
			        'from_name' => $from_name,
			        'to' => array(
			            array(
			                'email' => $_POST['text_email'],
			                'name' => false,
			                'type' => 'to'
			            )
			        ),
			        'headers' => array('Reply-To' => $return_path),
			        'important' => false,
			        'track_opens' => false,
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
			    $sent_mandrill = empty($result[0]["reject_reason"]) ? true : false;

			    if ($sent_mandrill)
				{
				
					//BRIGHT_mail_feedback::insert_newsletter($_POST['mensagem_id']); //inserir na db

					$sql = "INSERT INTO `mensagens_teste_enviadas` (`mensagem_id`, `assunto`, `mensagem_text`, `mensagem`, `destino`, `hora`, `output`) VALUES ('".$_POST['mensagem_id']."', '".$mensagem->assunto."', '".$mensagem->mensagem_text."', '".mysql_real_escape_string( $mensagem->mensagem )."', '".$_POST['text_email']."', '".date("Y-m-d H:i:s")."', 'sucesso')";
					$query = mysql_query($sql);
					tools::notify_add("Mensagem de teste enviada com sucesso", "success");
					redirect( "admin/index.php?mod=send&view=pre_send&id=".$_POST["mensagem_id"] );
				}
				else
				{
					$sql = "INSERT INTO `mensagens_teste_enviadas` (`mensagem_id`, `assunto`, `mensagem_text`, `mensagem`, `destino`, `hora`, `output`) VALUES ('".$_POST['mensagem_id']."', '".$mensagem->assunto."', '".$mensagem->mensagem_text."', '".mysql_real_escape_string($mensagem->mensagem)."', '".$_POST['text_email']."', '".date("Y-m-d H:i:s")."', 'erro')";
					$query = mysql_query($sql);
					tools::notify_add("Ocorreu um erro ao enviar o email de teste", "error");
					redirect( "admin/index.php?mod=send&view=pre_send&id=".$_POST["mensagem_id"] );
				}


			}

		}else{
			tools::notify_add("Mensagem não encontrada.", "error");
			redirect( "admin/index.php?mod=send&view=pre_send&id=".$_POST["mensagem_id"] );
		}
	}


	/* Models */
	function get_mensagem_by_id($id){	//Cópias:  newsletters.mod, send.mod acho que há um cópia noutro lado
		$query = "SELECT * FROM `mensagens` WHERE `id` = '".$id."'";
		$res = mysql_query($query) or die(mysql_error());
		if( mysql_num_rows($res) < 1 ){
			return false;
		}else{
			return mysql_fetch_object($res);
		}
	}
	//listar os grupos a quais os utilizadores têm permissão
	public function get_sender_permissions($user_id){

		//um utilizador is_admin tem acesso a tudo
		$sql = "SELECT s.id, s.email, s.`email_from` FROM user_sender_permissions usp
		LEFT JOIN users u ON u.id = usp.user_id
		LEFT JOIN senders s ON s.id = usp.sender_id
		WHERE usp.user_id = ".$user_id;	

		$query = mysql_query($sql);

		if($query){
			while ($row = mysql_fetch_object($query))
				$output[$row->id] = array("email_from" => $row->email_from, "email" => $row->email);

			return $output;
		}

		return false;

	}
	function get_send_test( $id ){
		$query = "SELECT * FROM `mensagens_teste_enviadas` WHERE `mensagem_id` = '".$id."' ORDER BY `hora` DESC";
		$res = mysql_query($query) or die(mysql_error());
		return $res;
	}


}

 ?>