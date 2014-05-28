<?php

	//$abs_path = "/home/pmenet/public_html/holmes/bmm";
	//$abs_path = "/Users/bright/Documents/htdocs/pme24/bmm";
	//$abs_path = "C:/xampp/htdocs/bmm";
	$abs_path = base_path();

	//requires
	require_once($abs_path . "/inc/modules/settings.mod.php");
	require_once($abs_path . "/inc/database.php");

	//settings object
	$settings = new Settings;

	//$_SERVER["HTTP_HOST"] = "holmesplacesnews.pt";


	//definição de vars importantes provenientes das settings
	define("FEEDBACK_URL", base_url("inc/modules/feedback.php"));
	define("NEWSLETTER_VIEW_URL", base_url("inc/visualize_news.php"));
	define("SUBSCRIPTION_REMOVE_URL", base_url("inc/remove.php"));
	define("LINK_REDIRECT_URL", base_url("inc/link.php"));
	//define("BASE_DIR", $settings->base_path);
	define("BASE_DIR", $abs_path);


	//guardar email e iniciar o feedback
	if(!empty($_GET["email"])){
		/**
			Como o feedback.php é incluido no core, sempre que houver &email=<email> no url, vai tentar fazer um track
			Não existindo esta condição iria sempre dar ao ficheiro de track, sem nunca mostrar a newsletter.
			Utiliza-se o REQUEST_URI para determinar quem chamou este script (feedback.php || visualize.php)
		*/
		if( strpos($_SERVER["REQUEST_URI"], "visualize_news.php") === false && strpos($_SERVER["REQUEST_URI"], "link.php") === false){
			$feedback = new BRIGHT_mail_feedback();
			$feedback->run();
		}
		
	}

	class BRIGHT_mail_feedback{

		public $settings;
		public $base;
		public static $url = FEEDBACK_URL;
		public static $visualize_url = NEWSLETTER_VIEW_URL;
		public static $remove_url = SUBSCRIPTION_REMOVE_URL;
		public static $link_count_url = LINK_REDIRECT_URL;
		public $email;
		public $envio_id;
		public $ip;
		public $url_newsletter;
		public $user_agent;

		function __construct(){


			$this->set_base();


			require_once(base_path()."/inc/file_class.php");
			$this->email = strip_tags($_GET['email']);
			$this->url_newsletter = strip_tags($_GET['url']);
			$this->envio_id = intval( strip_tags($_GET["envio_id"]) );
			$this->ip = $_SERVER["REMOTE_ADDR"];
			$this->user_agent = $_SERVER['HTTP_USER_AGENT'];
			if (isset($_SERVER["HTTP_REFERER"])) {
				$this->referer = $_SERVER["HTTP_REFERER"];
			}else{
				$this->referer = "";
			}

		}

		function set_base(){

			//trocou-se o $base para que pudessem ser feitos testes em localhost, mesmo não podendo fazer o envio de e-mail
			switch ($_SERVER["HTTP_HOST"]) {
				case 'localhost':
					$this->base = "../";
					break;
				
				default:
					$this->base = base_path();
					break;
			}
		}

		function run(){
			//echo $this->client;
			//$this->db_connect();
			$this->insert();
			$this->render_fake_image();
		}


		function insert(  ){

			$query = "select users.* from envios
				left join users on users.id = envios.user_id
				 where envios.id = '".$this->envio_id."'";
			$res = mysql_query($query) or die( mysql_error() );
			$row = mysql_fetch_array($res);

			//echo '<hr />';
			//echo $query;


			$bcsv = new bcsv();
			$bcsv->initiate( $row["id"] );



			$visit[] = $row["sender_host"];	//host
			$visit[] = $row["id"];	//Id do user, acho que não está certo
			$visit[] = $this->email;	//Email
			$visit[] = $this->user_agent;	//user_agent
			$visit[] = $this->ip;	//ip
			$visit[] = $this->referer;	//referer
			$visit[] = date("Y-m-d H:m:s");	//data

			$this->client = $client->id;

			//var_dump($this);


			$bcsv->open_visits( "write", $this->envio_id);

			$bcsv->add_visit( $visit );
			$bcsv->close();
		}

		//recebe uma string de texto e injecta os parametros necessários a fazer o tracking
		static function inject($mensagem){


			$email = $mensagem->email;
			$html_body = $mensagem->mensagem;
			$envio_id = $mensagem->envio_id;
			$url = $mensagem->url;
		
			if ( $url == "mensagem-teste" ) {
				$query = "select senders.email from senders where senders.id = '".$_POST["sender_id"]."'";

				$res = mysql_query($query);
				$row = mysql_fetch_array($res);

				$email_a = explode( "@", $row["email"] );
				$domain = $email_a[1];
			}else{
				$query = "select senders.email from envios 
					left join senders on envios.sender_id = senders.id where envios.id = '".$mensagem->envio_id."'";
				$res = mysql_query($query);
				$row = mysql_fetch_array($res);

				$email_a = explode( "@", $row["email"] );
				$domain = $email_a[1];				
			}

			$sender_url = "http://www.".$domain."/bmm/inc/modules/feedback.php";
			$sender_visualize_url = "http://www.".$domain."/bmm/inc/visualize_news.php";
			$sender_remove_url = "http://www.".$domain."/bmm/inc/remove.php";
			$sender_link_count_url = "http://www.".$domain."/bmm/inc/link.php";


			//remove token
			$salt = "bright";
			$remove_token = md5($email.$salt);

			if(is_numeric($envio_id)){
				//a partir daqui, substituir todos os links <a> para um counter que faz um redirect
				$html_body = preg_replace('/href="(?!mailto)/', 'href="'.$sender_link_count_url.'?envio_id='.$envio_id.'&amp;url='.$url.'&amp;email='.$email.'&url_f=', $html_body);

				//cria o link de topo o link URL tem de apontar para o visualize_news.php
				$html_body = str_replace("{ver_no_browser}", "<a href=\"".$sender_visualize_url."/".$envio_id."/".$url."/".$email."\">clique aqui para ver no browser</a>", $html_body);
				
				if ( $url == "mensagem-teste" ) {
					//cria o link de remoção automática
					$html_body = str_replace("{remover_email}", "<a href=\"".$sender_remove_url."?remove_token=".$remove_token."&send_id_teste=".$_SESSION["user"]->id."\">remover</a>", $html_body);
				}else {
					//cria o link de remoção automática
					$html_body = str_replace("{remover_email}", "<a href=\"".$sender_remove_url."?remove_token=".$remove_token."&send_id=".$envio_id."\">remover</a>", $html_body);					
				}


				//cria a imagem escondida
				$html_body = str_replace("</body>", " <img width=\"1\" height=\"1\" src=\"".$sender_url."?envio_id=".$envio_id."&amp;url=".$url."&amp;email=".$email."\" alt=\"Imagem\" /></body>", $html_body);

				//substituição dos matches do tipo {campo:<fieldname>}
				preg_match_all("/{campo:([a-z-_0-9]*)}/", $html_body, $reg_exp_matches);

				foreach ($reg_exp_matches[0] as $key => $value) {
					
					$replace = "subscriber_" . $reg_exp_matches[1][$key];				
					$html_body = str_replace($value, $mensagem->$replace, $html_body);

				}

				//substituição dos matches pré-feitos {saudacao}
				//if(strpos($html_body, "{saudacao}") && !empty($mensagem->subscriber->sexo)){	//Old line, $mensagem->subscriber->sexo doesnt exist
				if(strpos($html_body, "{saudacao}") && !empty($mensagem->subscriber_sexo)){
					//determinar se é M ou F
					$saudacao = $mensagem->subscriber_sexo == "M" ? "Caro Sr. " . $mensagem->subscriber_nome : "Cara Srª. " . $mensagem->subscriber_nome;
					$html_body = str_replace("{saudacao}", $saudacao, $html_body); //subsctituir
				}
				
				if(strpos($html_body, "{idade}") && !empty($mensagem->subscriber_data_nascimento)){					
					$idade = floor((time() - strtotime($mensagem->subscriber_data_nascimento)) /  (60 * 60 * 24 * 365));
					$html_body = str_replace("{idade}", $idade, $html_body); //subsctituir
				}


				//$html_body = $html_body . " <img width=\"1\" height=\"1\" src=\"".self::$url."?client=".$client_id."&amp;mensagem_id=".$mensagem_id."&amp;email=".$email."\" alt=\"Imagem\" />";
				return $html_body;
			}else{
				return false;
			}
		}

		static function inject_browser($html_body, $email, $mensagem_id){


			$email = $email;
			$html_body = $html_body;	
			$envio_id = $mensagem_id;	//Isto pode ser apenas o id da newsletter caso seja uma mensagem de teste.
			$url = $_GET["url"];


			//var_dump( $url );


			if ( $url == "mensagem-teste" ) {
				//Aqui temos um problema pois se não houver sender não há dominio
				$domain = $_SERVER["HTTP_HOST"];

			}else{
				$query = "select senders.email from envios 
					left join senders on envios.sender_id = senders.id where envios.id = '".$envio_id."'";
				$res = mysql_query($query);
				$row = mysql_fetch_array($res);

				$email_a = explode( "@", $row["email"] );
				$domain = "www.".$email_a[1];				
			}


			$sender_url = "http://".$domain."/bmm/inc/modules/feedback.php";
			$sender_visualize_url = "http://".$domain."/bmm/inc/visualize_news.php";
			$sender_remove_url = "http://".$domain."/bmm/inc/remove.php";
			$sender_link_count_url = "http://".$domain."/bmm/inc/link.php";


			//remove token
			$salt = "bright";
			$remove_token = md5($email.$salt);

			if(is_numeric($envio_id)){

				//cria o link de topo o link URL tem de apontar para o visualize_news.php
				$html_body = str_replace("{ver_no_browser}", "<a href=\"".$sender_visualize_url."/".$envio_id."/".$url."/".$email."\">clique aqui para ver no browser</a>", $html_body);
				
				if ( $url == "mensagem-teste" ) {
					//cria o link de remoção automática
					$html_body = str_replace("{remover_email}", "<a href=\"#\">remover</a>", $html_body);
				}else {
					//cria o link de remoção automática
					$html_body = str_replace("{remover_email}", "<a href=\"".$sender_remove_url."?remove_token=".$remove_token."&send_id=".$envio_id."\">remover</a>", $html_body);					
				}


				//cria a imagem escondida
				$html_body = str_replace("</body>", " <img width=\"1\" height=\"1\" src=\"".$sender_url."?envio_id=".$envio_id."&amp;url=".$url."&amp;email=".$email."\" alt=\"Imagem\" /></body>", $html_body);


				//Para este replace precisamos dos dados do subscritor
				$query = "select subscribers.id as subscriber_id, subscribers.email as email, subscribers.hard_bounces_count as subscriber_hard_bounces_count, subscribers.nome as subscriber_nome, subscribers.data_nascimento as subscriber_data_nascimento, subscribers.sexo as subscriber_sexo, subscribers.telefone_1, subscribers.telefone_2, 
 						from subscribers where email = '".$email."'";
				$res = mysql_query($query);
				if ( mysql_num_rows($res) > 0 ) {
					$mensagem = mysql_fetch_object($res);	//É falso dizer que isto é a mensagem pois é o subscritor.
				}


				//substituição dos matches do tipo {campo:<fieldname>}
				preg_match_all("/{campo:([a-z-_0-9]*)}/", $html_body, $reg_exp_matches);

				foreach ($reg_exp_matches[0] as $key => $value) {
					
					$replace = "subscriber_" . $reg_exp_matches[1][$key];				
					$html_body = str_replace($value, $mensagem->$replace, $html_body);

				}

				//substituição dos matches pré-feitos {saudacao}
				//if(strpos($html_body, "{saudacao}") && !empty($mensagem->subscriber->sexo)){ //Old line $mensagem->subscriber->sexo doesnt exist
				if(strpos($html_body, "{saudacao}") && !empty($mensagem->subscriber_sexo)){
					//determinar se é M ou F
					$saudacao = $mensagem->subscriber_sexo == "M" ? "Caro Sr. " . $mensagem->subscriber_nome : "Cara Srª. " . $mensagem->subscriber_nome;
					$html_body = str_replace("{saudacao}", $saudacao, $html_body); //subsctituir
				}
				
				if(strpos($html_body, "{idade}") && !empty($mensagem->subscriber_data_nascimento)){					
					$idade = floor((time() - strtotime($mensagem->subscriber_data_nascimento)) /  (60 * 60 * 24 * 365));
					$html_body = str_replace("{idade}", $idade, $html_body); //subsctituir
				}


				//$html_body = $html_body . " <img width=\"1\" height=\"1\" src=\"".self::$url."?client=".$client_id."&amp;mensagem_id=".$mensagem_id."&amp;email=".$email."\" alt=\"Imagem\" />";
				return $html_body;
			}else{
				return false;
			}
		}
		//regista um click num link numa determinada newsletter
		function click_register(){

			//get url
			$request = urldecode($_SERVER["REQUEST_URI"]);
			$url = substr($request, strpos($request, "url"), strlen($request));
			$url = str_replace("url=", "", $url);

			$query = "select users.* from envios
				left join users on users.id = envios.user_id
				 where envios.id = '".$this->envio_id."'";
			$res = mysql_query($query) or die( mysql_error() );
			$row = mysql_fetch_array($res);

			//echo '<hr />';
			//echo $query;


			$bcsv = new bcsv();
			$bcsv->initiate( $row["id"] );
			$bcsv->open_clicks( "write", $_GET["envio_id"] );


			//$client_id = $id["id"];
			//$message_id = $_GET["mensagem_id"];
			$click_a[] = $_GET["email"];
			$click_a[] = $_GET["url_f"];
			$click_a[] = date("Y-m-d H:m");
			$click_a[] = $_SERVER["HTTP_REFERER"];
			$click_a[] = $_SERVER["REMOTE_ADDR"];

			$bcsv->add_click( $click_a );
			$bcsv->close();



			$bcsv->open_visits( "write", $_GET["envio_id"] );


			$visit[] = $_SERVER["SERVER_NAME"];	//Client aproximation
			$visit[] = $_GET["envio_id"];	//Id da mensagem
			$visit[] = $_GET["email"];	//Email
			$visit[] = $this->user_agent;	//user_agent
			$visit[] = $this->ip;	//ip
			$visit[] = $this->referer;	//referer
			$visit[] = date("Y-m-d H:m:s");	//data

			$bcsv->add_visit( $visit );
			$bcsv->close();


			header( "Location: ".$_GET["url_f"] );
		}

		function render_fake_image(){
			$my_img = imagecreate(1, 1);
			$background = imagecolorallocate( $my_img, 255, 255, 255 );
			header( "Content-type: image/png" );
			imagepng( $my_img );
			imagedestroy( $my_img );
		}



		//devolve todos os emails que abriram a newsletter
		function get_opened_from_client($envio_id){

			$query = "select * from envios where id = '".$envio_id."'";
			$res = mysql_query($query) or die( mysql_error() );
			$envio = mysql_fetch_array($res);

			$bcsv = new bcsv();


			$bcsv->initiate( $envio["user_id"] );
			$bcsv->open_visits("read", $envio["id"] );
			$lines = $bcsv->lines();
			//$lines = $bcsv->remove_quotes( $lines );
			foreach ($lines as $key => $value) {
				$a = explode( '","', $value );
				$a = $bcsv->remove_quotes( $a );

				$tmp_obj = new stdClass();
				$tmp_obj->email = $a[2];
				$tmp_obj->user_agent = $a[3];
				$tmp_obj->ip = $a[4];
				$tmp_obj->referer = $a[5];
				$tmp_obj->date_in = $a[6];
				$return[] = $tmp_obj;
				unset($tmp_obj);
			}
			//echo $lines;
			$bcsv->close();
			return $return;
		}

		//devolve todos os emails que abriram a newsletter
		function get_clicks_from_client($envio_id){
			$query = "select * from envios where id = '".$envio_id."'";
			$res = mysql_query($query) or die( mysql_error() );
			$envio = mysql_fetch_array($res);

			$bcsv = new bcsv();


			$bcsv->initiate( $envio["user_id"] );
			$bcsv->open_clicks("read", $envio["id"] );
			$lines = $bcsv->lines();

			if($lines){
				foreach ($lines as $key => $value) {
				$a = explode( '","', $value );
				$a = $bcsv->remove_quotes( $a );

				$tmp_obj = new stdClass();
				$tmp_obj->email = $a[0];
				$tmp_obj->url = $a[1];
				$tmp_obj->date = $a[2];
				$tmp_obj->referer = $a[3];
				$tmp_obj->ip = $a[4];
				$return[] = $tmp_obj;
				unset($tmp_obj);
				}
				//echo $lines;
				
			}
			else
				$return = false;

			$bcsv->close();
			return $return;
			
		}
		
		function get_num_send($envio_id){
			$query = "select * from envios where id='".$envio_id."'";
			$res = mysql_query($query) or die( mysql_error() );
			$envio = mysql_fetch_array($res);

			$bcsv = new bcsv();
			$bcsv->initiate( $envio["user_id"] );
			$bcsv->open_enviadas("read", $envio["id"] );

			$lines = $bcsv->count_lines();
			$bcsv->close();

			return $lines;
		}

	
		function get_statistics_by_day( $envio_id ){

			$query = "SELECT * FROM envios where id = '".$envio_id."'";
			$res = mysql_query($query);
			$envio = mysql_fetch_array($res);

			$bcsv = new bcsv();
			$bcsv->initiate( $envio["user_id"] );
			$bcsv->open_visits("read", $envio["id"] );
			$lines = $bcsv->lines();


			foreach ($lines as $i => $line) {
				$visit = explode('","', $line);

				$visit[6] = $bcsv->remove_quotes( $visit[6] );

				$day = date("dm", strtotime($visit[6]));

				if( !isset( $pre_return[$day] ) ) {
					$pre_return[$day] = 1;
				}else{
					$pre_return[$day]++;
				}
			}
			$bcsv->close();

			if($pre_return){
				foreach ($pre_return as $key => $value) {
					$obj = new stdClass();
					$obj->num = $value;
					$obj->day = $key;
					$return[] = $obj;
					unset($obj);
				}
			}
			else
				$return = false;
			
			return $return;
		}
		
		//Confirmar que isto é mesmo get_opened_from_client.
		function get_num_opened_from_newsletter($envio_id){
			$query = "select * from envios where id = '".$envio_id."'";
			$res = mysql_query($query) or die( mysql_error() );

			$envio = mysql_fetch_array($res);


			$bcsv = new bcsv();
			$bcsv->initiate( $envio["user_id"] );

			$bcsv->open_visits("read", $envio["id"] );
			$lines = $bcsv->count_lines();


			//echo $newsletter_id;
			

			$bcsv->close();
			return $lines;
		}

		function get_num_distinct_opened_from_newsletter($envio_id){
			$query = "select * from envios where id = '".$envio_id."'";
			$res = mysql_query($query) or die( mysql_error() );
			$envio = mysql_fetch_array($res);

			$bcsv = new bcsv();
			$bcsv->initiate( $envio["user_id"] );
			$bcsv->open_visits( "read", $envio["id"] );
			$lines = $bcsv->lines();

			foreach ($lines as $i => $line) {
				$visit = explode(",", $line);
				$visit[2] = $bcsv->remove_quotes( $visit[2] );
				if( !isset( $pre_return[$visit[2]] ) ) {
					$pre_return[$visit[2]] = 1;
				}else{
					$pre_return[$visit[2]]++;
				}
			}
			$bcsv->close();
			return count($pre_return);
		}

		//a partir das $columns, criar uma tabela temporaria que contém os registos
		function load_clicks_into_table($clicks , $columns){

			//connect

			$sql_columns = implode(" VARCHAR(255) NULL, ", $columns);
			$sql_columns .= " VARCHAR(255) NULL";

			$sql = "CREATE TABLE IF NOT EXISTS `temp_clicks` ( ".$sql_columns." ) COLLATE='utf8_general_ci' ENGINE=MyISAM;";
			
			$query = mysql_query($sql) or die( mysql_error().$sql );
			while ( $row = mysql_fetch_array($query) ) {
				$clicks[] = $row;
			}

			
			//preencher a table com os dados de clicks, tem que estar a condição true, caso não se apague a tabela
			if($query){
				if($clicks){
					foreach ($clicks as $click) {
						//inserir as colunas dinamicamente
						$insert_sql = "INSERT INTO `temp_clicks` (".implode(",", $columns).") VALUES ('".$click->email."', '".$click->url."', '".$click->date."', '".$click->referer."', '".$click->ip."')";
						$insert = mysql_query($insert_sql) or die( mysql_error().$sql );
					}
				}
			}
		}

		//limpa a tabela temporária de clicks
		function clear_clicks_table(){
			//connect
			$query = mysql_query("truncate temp_clicks");

			return $query;
		}

		/* estatisticas de cliques */
		function get_top_clicks(){
			//query
			$sql = "select url, count(url) as total_clicks from temp_clicks group by url order by total_clicks DESC LIMIT 100";

			//connect
			//$pdo = self::db_connect();
			//$query = $pdo->query($sql);
			//$result = $query->fetchAll(PDO::FETCH_OBJ);

			$res = mysql_query($sql) or die_sql( $sql );
			while ( $row = mysql_fetch_array($res) ) {
				$result[] = $row;
			}

			return $result;
		}
		function get_all_clicks(){
			//query
			$sql = "select url, email, url, `date`, referer, ip from temp_clicks order by date asc";

			//connect
			//$pdo = self::db_connect();
			//$query = $pdo->query($sql);
			//$result = $query->fetchAll(PDO::FETCH_OBJ);

			$res = mysql_query($sql) or die_sql( $sql );
			while ( $row = mysql_fetch_array($res) ) {
				$result[] = $row;
			}

			return $result;
		}

		function get_top_active_users(){
			//query
			$sql = "select email, count(email) as total_clicks from temp_clicks group by email order by total_clicks DESC LIMIT 100";

			//connect
			//$pdo = self::db_connect();
			//$query = $pdo->query($sql);
			//$result = $query->fetchAll(PDO::FETCH_OBJ);

			$res = mysql_query($sql);
			while ( $row = mysql_fetch_array($res) ) {
				$result[] = $row;
			}

			return $result;
		}

		/* estatisticas de cliques */
		
		function get_distinct_opened($client_id){
			die("Está a entrar aqui");
			//$pdo = self::db_connect();
			$sql = "SELECT * FROM `newsletters` where `client_id` = ".$client_id;
			//$sql = "SELECT count(distinct(email)) as num FROM visits WHERE `client_id` = ".$client_id;
			$query = $pdo->query($sql);
			$results = $query->fetchAll(PDO::FETCH_OBJ);

			$total = 0;

			foreach ($results as $key => $news) {
				$total += $this->get_num_distinct_opened_from_newsletter( $client_id, $news->id );
			}

			return $total;

		}


	}

?>
