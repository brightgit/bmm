<?php 
/**
* 
*/
class Statistics
{
	public $view = "statistics/statistics";
	
	function __construct()
	{
		if ( !empty($_GET["view"]) ) {
			$this->$_GET["view"]();
		}
	}

	/* Controllers */
	function newsletter_statistics(  ) {
		$this->view = "statistics/newsletter_statistics";
	}
	function statistics() {  }	//Para não dar erro


	/* Models */
	function get_messages($start = 0, $limit = 30){

		if($_SESSION["user"]->is_admin)
			$query = "SELECT * FROM mensagens ORDER BY id DESC LIMIT ".$start.", ".$limit;
		else
			$query = "SELECT * FROM user_permissions_newsletter upn INNER JOIN mensagens m ON m.id = upn.id_newsletter where id_user = {$_SESSION["user"]->id} ORDER BY m.id DESC LIMIT ".$start.", ".$limit;

		$res = mysql_query($query) or die(mysql_error());
		return $res;
	}

	function get_subscribers_in_date_interval($start, $finish){	
		$query = "SELECT * FROM `subscribers` WHERE date_in BETWEEN '".$start."' AND '".$finish."'";
	}

	function get_subscribers_info_year($year){	
		$this->get_subscribers_in_date_interval(1, 2);
		return true;
	}



}

 ?>