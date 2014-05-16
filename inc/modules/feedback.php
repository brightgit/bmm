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

	$_SERVER["HTTP_HOST"] = "holmesplacesnews.pt";


	//definição de vars importantes provenientes das settings
	define("FEEDBACK_URL", "http://pme24.net/holmes/bmm/inc/modules/feedback.php");
	define("NEWSLETTER_VIEW_URL", "http://pme24.net/holmes/bmm/inc/visualize_news.php");
	define("SUBSCRIPTION_REMOVE_URL", "http://pme24.net/holmes/bmm/inc/remove.php");
	define("LINK_REDIRECT_URL", "http://pme24.net/holmes/bmm/inc/link.php");
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
		public static $db_host = "server9.brightminds.pt";
		public static $db_username = "brightmi_mstats";
		public static $db_password = "Bright#$91";
		public static $db_name = "brightmi_mail_stats";
		public $email;
		public $client;
		public $ip;
		public $mensagem_id;
		public $user_agent;

		function __construct(){


			$this->set_base();


			require_once(base_path()."/inc/file_class.php");
			$this->email = strip_tags($_GET['email']);
			$this->mensagem_id = strip_tags($_GET['mensagem_id']);
			$this->client = intval( strip_tags($_GET["client"]) );
			$this->ip = $_SERVER["REMOTE_ADDR"];
			$this->user_agent = $_SERVER['HTTP_USER_AGENT'];
			$this->referer = $_SERVER["HTTP_REFERER"];

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

		public static function db_connect(){

			$db_name = self::$db_name;
			$db_host = self::$db_host;
			$db_username = self::$db_username;
			$db_password = self::$db_password;

			try {
				//$pdo = new PDO($pdo_string);

				$pdo = new PDO('mysql:host=195.200.253.230;dbname=brightmi_mail_stats', $db_username, $db_password, array(
					PDO::ATTR_PERSISTENT => false
				));
				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			} catch (PDOException $ex) {
				echo 'Connection failed: ' . $ex->getMessage();
				$pdo = false;
			}		

			if($pdo)
				return $pdo;
			else
				mail("franco.silva@bright.pt", "Erro Bright Mail Module", "Ocorreu um erro na ligação à db. Verificar MYSQL.");
		}

		function insert(){

			$bcsv = new bcsv();
			$bcsv->initiate();


			$pdo = self::db_connect();
			$sql = "SELECT * FROM `clients` where `id` = '".$this->client."'";

			$query = $pdo->query( $sql );
			$client = $query->fetchObject();

			$visit[] = $client->name;	//Client
			$visit[] = $this->mensagem_id;	//Id da mensagem
			$visit[] = $this->email;	//Email
			$visit[] = $this->user_agent;	//user_agent
			$visit[] = $this->ip;	//ip
			$visit[] = $this->referer;	//referer
			$visit[] = date("Y-m-d H:m:s");	//data

			$this->client = $client->id;

			//var_dump($this);

			//adicionado 16/07 . estava a crashar o feedback
			$log_info["client"] =  (int) $this->client;
			$log_info["mensagem_id"] = (int) $this->mensagem_id;


			$bcsv->open_visits( "write", $log_info);

			$bcsv->add_visit( $visit );
			$bcsv->close();
		}

		//vai buscar o id de cliente pelo domínio do site
		public static function get_client_id(){

			//conectar à db
			$pdo = self::db_connect();
			//$host = str_replace("www.", "", $_SERVER["SERVER_NAME"]);
			$host = "holmesplacenews.pt";

			//para funcionar em localhost, temos que fornecer um domain válido // podia estar melhor e termos uma pasta "localhost"
			switch ($_SERVER["HTTP_HOST"]) {
				case 'localhost':
					$sql = "SELECT id FROM `clients` WHERE domain = 'brightminds.pt' " ;
					break;
				
				default:
					$sql = "SELECT id FROM `clients` WHERE domain = '{$host}' " ;
					break;
			}
		

			$query = $pdo->query($sql);
			$client = $query->fetchObject();

			return !empty($client->id) ? intval($client->id) : false;
		}

		public static function get_client( $id ){

			//conectar à db
			$pdo = self::db_connect();
			$sql = "SELECT * FROM `clients` WHERE id = '".$id."'";
			$query = $pdo->query($sql);
			$client = $query->fetchObject();

			return $client;
		}

		//recebe uma string de texto e injecta os parametros necessários a fazer o tracking
		static function inject($html_body, $email, $mensagem_id){
			
			//remove token
			$salt = "bright";
			$remove_token = md5($email.$salt);
			$client_id = self::get_client_id();

			if(is_numeric($client_id)){

				//cria o link de topo o link URL tem de apontar para o visualize_news.php
				//$html_body = str_replace("{ver_no_browser}", "<a href=\"".self::$visualize_url."?client=".$client_id."&mensagem_id=".$mensagem_id."&email=".$email."\">ver no browser</a>", $html_body);
				$html_body = str_replace("{ver_no_browser}", "<a href=\"".self::$visualize_url."/".$client_id."/".$mensagem_id."/".$email."\">clique aqui para ver no browser</a>", $html_body);
				
				//cria o link de remoção automática
				$html_body = str_replace("{remover_email}", "<a href=\"".self::$remove_url."?remove_token=".$remove_token."\">remover</a>", $html_body);


				//a partir daqui, substituir todos os links <a> para um counter que faz um redirect
				$html_body = preg_replace('/href="(?!mailto)/', 'href="'.self::$link_count_url.'?client='.$client_id.'&amp;mensagem_id='.$mensagem_id.'&amp;email='.$email.'&url=', $html_body);

				//cria a imagem escondida
				$html_body = str_replace("</body>", " <img width=\"1\" height=\"1\" src=\"".self::$url."?client=".$client_id."&amp;mensagem_id=".$mensagem_id."&amp;email=".$email."\" alt=\"Imagem\" /></body>", $html_body);

				//$html_body = $html_body . " <img width=\"1\" height=\"1\" src=\"".self::$url."?client=".$client_id."&amp;mensagem_id=".$mensagem_id."&amp;email=".$email."\" alt=\"Imagem\" />";
				return $html_body;
			}
				
			else
				return false;
		}

		static function inject_browser($html_body, $email, $mensagem_id){
			
			//remove token
			$salt = "bright";
			$remove_token = md5($email.$salt);
			$client_id = self::get_client_id();

			if(is_numeric($client_id)){

				//cria o link de topo o link URL tem de apontar para o visualize_news.php
				//$html_body = str_replace("{ver_no_browser}", "<a href=\"".self::$visualize_url."?client=".$client_id."&mensagem_id=".$mensagem_id."&email=".$email."\">ver no browser</a>", $html_body);
				//$html_body = str_replace("{ver_no_browser}", "<a href=\"".self::$visualize_url."/".$client_id."/".$mensagem_id."/".$email."\">clique aqui para ver no browser</a>", $html_body);
				
				//cria o link de remoção automática
				$html_body = str_replace("{remover_email}", "<a href=\"".self::$remove_url."?remove_token=".$remove_token."\">remover</a>", $html_body);


				//a partir daqui, substituir todos os links <a> para um counter que faz um redirect
				//$html_body = preg_replace('/href="(?!mailto)/', 'href="'.self::$link_count_url.'?client='.$client_id.'&amp;mensagem_id='.$mensagem_id.'&amp;email='.$email.'&url=', $html_body);

				//cria a imagem escondida
				//$html_body = $html_body . " <img width=\"1\" height=\"1\" src=\"".self::$url."?client=".$client_id."&amp;mensagem_id=".$mensagem_id."&amp;email=".$email."\" alt=\"Imagem\" />";
				return $html_body;
			}
				
			else
				return $html_body;
		}

		//regista um click num link numa determinada newsletter
		function click_register(){

			//get url
			$request = urldecode($_SERVER["REQUEST_URI"]);
			$url = substr($request, strpos($request, "url"), strlen($request));
			$url = str_replace("url=", "", $url);

			$bcsv = new bcsv();
			$bcsv->initiate( $_GET["client"] );
			$bcsv->open_clicks( "write", $_GET );


			$client_id = $_GET["client"];
			$message_id = $_GET["mensagem_id"];
			$click_a[] = $_GET["email"];
			$click_a[] = $_GET["url"];
			$click_a[] = date("Y-m-d H:m");
			$click_a[] = $_SERVER["HTTP_REFERER"];
			$click_a[] = $_SERVER["REMOTE_ADDR"];

			$bcsv->add_click( $click_a );
			$bcsv->close();

			$bcsv->open_visits( "write", $_GET );


			$visit[] = $_SERVER["SERVER_NAME"];	//Client aproximation
			$visit[] = $_GET["mensagem_id"];	//Id da mensagem
			$visit[] = $_GET["email"];	//Email
			$visit[] = $this->user_agent;	//user_agent
			$visit[] = $this->ip;	//ip
			$visit[] = $this->referer;	//referer
			$visit[] = date("Y-m-d H:m:s");	//data

			$bcsv->add_visit( $visit );
			$bcsv->close();



			header( "Location: ".$url );
		}

		function render_fake_image(){
			$my_img = imagecreate(1, 1);
			$background = imagecolorallocate( $my_img, 255, 255, 255 );
			header( "Content-type: image/png" );
			imagepng( $my_img );
			imagedestroy( $my_img );
		}



		//esta função deve devolver estatísticas pré processadas e o ínicio de ver o ficheiro
		function load_statistics( $only_part = FALSE ){
			$return = FALSE;
			return $return;
		}

		//devolve todos os emails que abriram a newsletter
		function get_opened_from_client($client_id, $message_id){
			$bcsv = new bcsv();
			$pre = $this->load_statistics( "visits" );	//Carregar estatíscas para visitas
			if( $pre ) {
				//TODO Here
				die("--1");
			}

			$bcsv->initiate( $client_id );
			$bcsv->open_visits("read", array('mensagem_id' => $message_id, 'client' => $client_id ) );
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
		function get_clicks_from_client($client_id, $message_id){

			$bcsv = new bcsv();
			$pre = $this->load_statistics( "visits" );	//Carregar estatíscas para visitas
			if( $pre ) {
				//TODO Here
				die("Aqui deve devolver coisas que não está a devolver");
			}
			$bcsv->initiate( $client_id );
			$bcsv->open_clicks("read", array('mensagem_id' => $message_id, 'client' => $client_id ) );
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
		
		function get_num_send($client_id, $newsletter_id){
			$bcsv = new bcsv();
			$pre = $this->load_statistics( "visits" );	//Carregar estatíscas para visitas
			if( $pre ) {
				//TODO Here
				die("Aqui deve devolver coisas que não está a devolver");
			}
			$bcsv->initiate( $client_id );
			$bcsv->open_enviadas("read", array('mensagem_id' => $newsletter_id, 'client' => $client_id ) );

			$lines = $bcsv->count_lines();
			$bcsv->close();

			return $lines;
		}

	
		function get_statistics_by_day( $client_id, $newsletter_id ){

			$bcsv = new bcsv();
			$pre = $this->load_statistics( "visits" );	//Carregar estatíscas para visitas
			if( $pre ) {
				//TODO Here
				die("Aqui deve devolver coisas que não está a devolver");
			}
			$bcsv->initiate( $client_id );
			$bcsv->open_visits("read", array('mensagem_id' => $newsletter_id, 'client' => $client_id ) );
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
		function get_num_opened_from_newsletter($client_id, $newsletter_id){
			$bcsv = new bcsv();
			$pre = $this->load_statistics( "visits" );	//Carregar estatíscas para visitas
			if( $pre ) {
				//TODO Here
				die("Aqui deve devolver coisas que não está a devolver");
			}
			$bcsv->initiate( $client_id );

			$bcsv->open_visits("read", array('mensagem_id' => $newsletter_id, 'client' => $client_id ) );
			$lines = $bcsv->count_lines();


			//echo $newsletter_id;
			

			$bcsv->close();
			return $lines;
		}

		function get_num_distinct_opened_from_newsletter($client_id, $newsletter_id){
			$bcsv = new bcsv();
			$pre = $this->load_statistics( "visits" );	//Carregar estatíscas para visitas
			if( $pre ) {
				//TODO Here
				die("Aqui deve devolver coisas que não está a devolver");
			}
			$bcsv->initiate( $client_id );
			$bcsv->open_visits( "read", array('mensagem_id' => $newsletter_id, 'client' => $client_id ) );
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
			$pdo = self::db_connect();

			$sql_columns = implode(" VARCHAR(255) NULL, ", $columns);
			$sql_columns .= " VARCHAR(255) NULL";

			$sql = "CREATE TABLE IF NOT EXISTS `temp_clicks` ( ".$sql_columns." ) COLLATE='utf8_general_ci' ENGINE=MyISAM;";
			
			//criar tabelas dinamicamente, todas em varchar 255
			$query = $pdo->query($sql);

			//preencher a table com os dados de clicks, tem que estar a condição true, caso não se apague a tabela
			if($query){
				if($clicks){
					foreach ($clicks as $click) {
						//inserir as colunas dinamicamente
						$insert_sql = "INSERT INTO `temp_clicks` (".implode(",", $columns).") VALUES ('".$click->email."', '".$click->url."', '".$click->date."', '".$click->referer."', '".$click->ip."')";
						$insert = $pdo->query($insert_sql);
					}
				}
			}
		}

		//limpa a tabela temporária de clicks
		function clear_clicks_table(){
			//connect
			$pdo = self::db_connect();
			$query = $pdo->query("truncate temp_clicks");

			return $query;
		}

		/* estatisticas de cliques */
		function get_top_clicks(){
			//query
			$sql = "select url, count(url) as total_clicks from temp_clicks group by url order by total_clicks DESC LIMIT 100";

			//connect
			$pdo = self::db_connect();
			$query = $pdo->query($sql);
			$result = $query->fetchAll(PDO::FETCH_OBJ);

			return $result;
		}

		function get_top_active_users(){
			//query
			$sql = "select email, count(email) as total_clicks from temp_clicks group by email order by total_clicks DESC LIMIT 100";

			//connect
			$pdo = self::db_connect();
			$query = $pdo->query($sql);
			$result = $query->fetchAll(PDO::FETCH_OBJ);

			return $result;
		}

		/* estatisticas de cliques */
		
		function get_distinct_opened($client_id){
			$pdo = self::db_connect();
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

		//regista uma newsletter
		function insert_newsletter($id){

			require_once(base_path()."/inc/file_class.php");

			$bcsv = new bcsv();
			$bcsv->initiate();

			$log_info["mensagem_id"] = $id;
			$log_info["client"] = BRIGHT_mail_feedback::get_client_id();

			$bcsv->open_enviadas("write", $log_info);


			$client_id = self::get_client_id();
			$sql = "INSERT IGNORE INTO newsletters (id, client_id) VALUES (".$id.", ".$client_id.")";

			//conexão
			$pdo = self::db_connect();
			$insert = $pdo->exec($sql);

			return $insert;
		}

	}

	function base_path(){
		//chroot/home/brightmi/brightminds.pt/html/bmm/ « isto tem de ser o base path - todos têm chroot/home/<conta_unix>/<domain>/html/<pasta onde está o bmm>
		$homedir = getcwd();
		$base_dir = str_replace('/admin', "", $homedir);

		//implementar o base_path das settings
		//$base_dir = "/Users/bright/Documents/htdocs/bmm";
		$base_dir = "C:/xampp/htdocs/bmm";

		return $base_dir."/";
	}
	
?>
