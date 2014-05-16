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
		$sql = "SELECT * FROM subscribers WHERE id = " . $id_subscriber;
		$query = mysql_query($sql);

		if($query)
			return $result = mysql_fetch_object($query);
		return false;
	}

	//view related methods
	function view_subscriber(){
		$id = (int) $_GET["id"];
		$subscriber = $this->get_subscriber_info($id);

		echo  Core::load_view(base_path("inc/views/bo/subscriber.php"), $subscriber);

	}

}

?>