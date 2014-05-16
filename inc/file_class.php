<?php

class bcsv extends BRIGHT_mail_feedback
{

	public $folder_prefix;
	public $folder_prefix_small;
	public $folder = '';	//Folder EX: brightminds.pt
	public $filename = ''; //Name do the file to write EX: 20131121-32.csv (AAAAMMDD-newsletterID.csv)
	public $conn = ''; // Connection to file
	public $conn_file = ''; // Path to the file
	public $client;

/*
	public $email;
	public $client;
	public $ip;
	public $mensagem_id;
	public $user_agent;
*/

	function __construct(){
		switch ($_SERVER["HTTP_HOST"]) {
			case 'localhost':
				$this->folder_prefix = "../clients/";
				$this->folder_prefix_small = "../../";
				break;
			
			default:
				//$this->folder_prefix = "/home/brightmi/brightminds.pt/html/bmm/clients/";
				//$this->folder_prefix_small = "/home/brightmi/brightminds.pt/html/bmm/";
				$this->folder_prefix = base_path()."clients/";
				$this->folder_prefix_small = base_path();
				break;
		}
	}

	//Esta função vai apenas para a página do cliente
	public function initiate( $user_id = FALSE ){


		$query = "select * from users where id = '".$user_id."'";

		$res = mysql_query($query) or die( mysql_error() );

		if( $row = mysql_fetch_array($res) ) {

		}else{
			echo $query;
			echo '<hr />';
			die("Settings no found AXDDEE");
		}

		//echo $this->folder_prefix.$client->domain;

		if (file_exists( $this->folder_prefix.$row["sender_host"])) {
			
		}else{

			mkdir( $this->folder_prefix.$row["sender_host"], 0777, true);
			mkdir( $this->folder_prefix.$row["sender_host"].'/visits', 0777, true);
			mkdir( $this->folder_prefix.$row["sender_host"].'/clicks', 0777, true);
			mkdir( $this->folder_prefix.$row["sender_host"].'/mensagens_enviadas', 0777, true);

		}
		$this->folder = $row["sender_host"];

	}


	//ista função vai carregar o ficheiro
	public function open_visits( $action = "write", $envio_id = FALSE ){
		//vou precisar o dominio e data do envio
		$query = "select envios.date_sent, mensagens.id, users.sender_host 
			from envios
			inner join mensagens on envios.mensagem_id = mensagens.id
			inner join 	users on users.id = envios.user_id
			where envios.id = '".$envio_id."'";
		
		//echo "<hr />".$query."<hr />";
		//die("afsdf");
		//exit("fdsaf");
		
		$res = mysql_query($query) or die( mysql_error() );

		$row = mysql_fetch_array($res);

		if ($row) {
			$this->filename = date("Ymd", strtotime( $row["date_sent"] ) ).'-'.$envio.'.csv';

			$this->conn_file = $this->folder_prefix.$row["sender_host"].'/visits/' . $this->filename;

			//echo $this->folder_prefix.$row["sender_host"].'/visits/'.$this->filename;

			if ( file_exists( $this->conn_file = $this->folder_prefix.$row["sender_host"].'/visits/'.$this->filename ) ) {
				if( $action == "write" ) { $mode = 'a'; }elseif( $action == 'read' ){ $mode = "r"; }else{ die("Modo não definido."); }
				$this->conn = fopen($this->conn_file = $this->folder_prefix.$row["sender_host"].'/visits/'.$this->filename, $mode) or die("Não foi possível abrir ficheiro.");
			}else{
				if( $action != "write" ) { return false; }
				$mode = "w";
				$this->conn = fopen($this->conn_file = $this->folder_prefix.$row["sender_host"].'/visits/'.$this->filename, $mode) or die("Não foi possível criar ficheiro. - file_class 166");
			}
		}
		else
			echo "Something wrong in line 106, file_class.php";	

	}

	public function open_clicks( $action = "write", $envio_id = FALSE ){

		//vou precisar o dominio e data do envio
		$query = "select envios.date_sent, mensagens.id, users.sender_host 
			from envios
			inner join mensagens on envios.mensagem_id = mensagens.id
			inner join 	users on users.id = envios.user_id
			where envios.id = '".$envio_id."'";
		
		//echo "<hr />".$query."<hr />";
		//die("afsdf");
		//exit("fdsaf");
		
		$res = mysql_query($query) or die( mysql_error() );

		$row = mysql_fetch_array($res);

		if ($row) {
			$this->filename = date("Ymd", strtotime( $row["date_sent"] ) ).'-'.$envio_id.'.csv';

			$this->conn_file = $this->folder_prefix.$row["sender_host"].'/clicks/' . $this->filename;

			//echo $this->folder_prefix.$row["sender_host"].'/clicks/'.$this->filename;

			if ( file_exists( $this->conn_file = $this->folder_prefix.$row["sender_host"].'/clicks/'.$this->filename ) ) {
				if( $action == "write" ) { $mode = 'a'; }elseif( $action == 'read' ){ $mode = "r"; }else{ die("Modo não definido."); }
				$this->conn = fopen($this->conn_file = $this->folder_prefix.$row["sender_host"].'/clicks/'.$this->filename, $mode) or die("Não foi possível abrir ficheiro.");
			}else{
				if( $action != "write" ) { return false; }
				$mode = "w";
				$this->conn = fopen($this->conn_file = $this->folder_prefix.$row["sender_host"].'/clicks/'.$this->filename, $mode) or die("Não foi possível criar ficheiro. - file_class 166");
			}
		}
		else
			echo "Something wrong in line 106, file_class.php";	
		
	}

	public function add_mensagem_enviada( $click_a ){
		$click_a = $this->escape_csv( $click_a );
		fwrite( $this->conn, implode(",", $click_a)."\n" );
	}

	public function open_enviadas( $action = "write", $envio_id = FALSE ){

		//vou precisar o dominio e data do envio
		$query = "select envios.date_sent, mensagens.id, users.sender_host 
			from envios
			inner join mensagens on envios.mensagem_id = mensagens.id
			inner join 	users on users.id = envios.user_id
			where envios.id = '".$envio_id."'";
		
		//echo "<hr />".$query."<hr />";
		//die("afsdf");
		//exit("fdsaf");
		
		$res = mysql_query($query) or die( mysql_error() );

		$row = mysql_fetch_array($res);

		if ($row) {
			$this->filename = date("Ymd", strtotime( $row["date_sent"] ) ).'-'.$envio_id.'.csv';

			$this->conn_file = $this->folder_prefix.$row["sender_host"].'/mensagens_enviadas/' . $this->filename;

			//echo $this->folder_prefix.$row["sender_host"].'/mensagens_enviadas/'.$this->filename;

			if ( file_exists( $this->conn_file = $this->folder_prefix.$row["sender_host"].'/mensagens_enviadas/'.$this->filename ) ) {
				if( $action == "write" ) { $mode = 'a'; }elseif( $action == 'read' ){ $mode = "r"; }else{ die("Modo não definido."); }
				$this->conn = fopen($this->conn_file = $this->folder_prefix.$row["sender_host"].'/mensagens_enviadas/'.$this->filename, $mode) or die("Não foi possível abrir ficheiro.");
			}else{
				if( $action != "write" ) { return false; }
				$mode = "w";
				$this->conn = fopen($this->conn_file = $this->folder_prefix.$row["sender_host"].'/mensagens_enviadas/'.$this->filename, $mode) or die("Não foi possível criar ficheiro. - file_class 166");
			}
		}
		else
			echo "Something wrong in line 106, file_class.php";	
	}

	public function count_lines(){
		//File has to be opened
		if (!file_exists($this->conn_file)) {
			return FALSE;
		}
		$file = file($this->conn_file);
		$lines = count($file);
		return $lines;
	}

	public function lines( $start = 0, $limit = FALSE ) {
		return @file( $this->conn_file );
	}


	public function add_visit( $visit_obs ){		//Tem que ser array

		$visit_obs = $this->escape_csv( $visit_obs );
		$email = $visit_obs[2];
		$file = $this->folder_prefix.$this->folder.'/visits/'.$this->filename;
		$read = fopen($file, 'r'); // or die("Não foi possível criar ficheiro.");
		$contents = fread($read, filesize($file));

		if(strpos($contents, $email)){

		}
		else{
			//increment visit count
			$month = date("m");
			$year = date("Y");
			//get month and year id
			$sql = "SELECT * FROM stats WHERE month = " . $month . " AND year = " . $year;
			$query = mysql_query($sql);
			$result = mysql_fetch_object($query);

			//update
			if($result){
				$sql = "UPDATE stats SET mensagens_abertas = mensagens_abertas + 1 WHERE id = ".$result->id;
				$query = mysql_query($sql);
			}
			//inserir novo mês
			else{
				$sql = "REPLACE stats SET mensagens_abertas = mensagens_abertas + 1, month = ". $month . ", year = " . $year;
				$query = mysql_query($sql);
			}
	
		}

		fwrite($this->conn, implode(",", $visit_obs)."\n" );
	}

	public function add_click( $click_a ){		//Tem que ser array
		$click_a = $this->escape_csv( $click_a );
		fwrite($this->conn, implode(",", $click_a)."\n" );
	}

	function close(){
		@fclose($this->conn);
		$this->conn = '';
	}


	/**
	Functions to process .csv
	*/
	function escape_csv( $a ){
		foreach ($a as $key => $value) {
			//Retirar aspas e rodear de aspas
			$a[$key] = '"'.str_replace('"', '&quote;', $a[$key]).'"';
		}
		return $a;

	}
	function remove_quotes( $a ){
		if (is_array($a)) {
			foreach ($a as $key => $value) {
				$a[$key] = trim(str_replace('"', "", $a[$key]));
			}
		}else{
			$a = trim(str_replace('"', "", $a));
		}
		return $a;
	}


}

?>