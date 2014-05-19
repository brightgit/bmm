<?php 

class Subscribers {

	//private $mode = 'fe';
	private $debug = '';
	public $lang;
	private $mod;

	function __construct($mode = '') {

		$this->lang = $_SESSION['lang'];
		$this->mod = 'subscribers';

		if(!empty($_GET["act"]) && method_exists($this, $_GET["act"])){
			$act = $_GET["act"];
			$this->$act();
		}
	}

	function get_subscriber_info($id_subscriber){

		//info do subscriber
		$sql = "SELECT * FROM subscribers WHERE id = " . $id_subscriber;
		$query = mysql_query($sql);

		if($query)
			$output["subscriber"] = mysql_fetch_object($query);
		
		//buscar os groups a que pertence
		$sql = "SELECT nc.id, nc.categoria FROM subscriber_by_cat sbc LEFT JOIN newsletter_categorias nc ON nc.`id` = sbc.id_categoria WHERE id_subscriber = " . $id_subscriber;
		$query = mysql_query($sql);

		if($query){
			while($row = mysql_fetch_object($query)){
				$groups[] = $row;
				$output["groups_ids"][$row->id] = true;
			}		
		}

		//buscar todos os restantes grupos
		$sql = "SELECT id, categoria FROM newsletter_categorias";
		$query = mysql_query($sql);

		if($query){
			while($row = mysql_fetch_object($query)){
				$output["categories"][] = $row;
			}
		}

		return $output;
	}

	//view related methods
	function view_subscriber(){
		$id = (int) $_GET["id"];
		$subscriber = $this->get_subscriber_info($id);

		echo  Core::load_view(base_path("inc/views/bo/subscriber.php"), $subscriber);

	}

}

?>