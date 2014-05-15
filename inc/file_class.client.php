<?php
/**
* 
*/
class bcsv_client
{
	public $folder_prefix;
	public $folder = 'mensagens';	//Folder EX: mensagens_enviadas
	public $filename = ''; //Name do the file to write EX: 20131121-32.csv (AAAAMMDD-newsletterID.csv)
	public $conn = ''; // Connection to file	
	public $conn_file = ''; // Connection file connected

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
				$this->folder_prefix = "../mensagens_enviadas/";
				break;
			
			default:
				//$this->folder_prefix = "/home/brightmi/brightminds.pt/html/bmm/mensagens_enviadas/";
				$this->folder_prefix = Core::base_path()."/mensagens_enviadas/";
				break;
		}
	}

	//Esta função vai apenas para a página do cliente
	public function initiate( $mensagem_id ){

		//Ficheiro
		$query = "SELECT * FROM `mensagens` where `id` = '".$mensagem_id."'";
		//echo $query;
		$res = mysql_query($query) or die( mysql_error() );
		$row = mysql_fetch_object($res);

		$news_folder = date("Ymd", strtotime($row->data_criada) ).'-'.$mensagem_id.'.csv';


		if ( file_exists( $this->folder_prefix.$this->folder.'/'.$news_folder ) ) {
			$this->conn = fopen( $this->folder_prefix.$this->folder.'/'.$news_folder, 'a' ) or die("Não foi possível abrir ficheiro.");
		}else{
			$this->conn = fopen( $this->folder_prefix.$this->folder.'/'.$news_folder, 'w') or die("Não foi possível abrir ficheiro.");
		}

	}


	public function add_mensagem_enviada( $click_a ){
		$click_a = $this->escape_csv( $click_a );
		fwrite( $this->conn, implode(",", $click_a)."\n" );
	}

	function close(){
		fclose($this->conn);
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