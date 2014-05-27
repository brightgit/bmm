<?php 
/**
 * Dashboard
 */
class Dashboard {

	public $view = "dashboard/dashboard";

	public $subscribers_by_interval;
	public $subscribers_per_month;
	public $hard_bounces_count;
	public $exclusion_requests_per_month;
	public $subscribers_last_time_interval;
	public $total_sent;
	public $total_opened;
	public $total_newsletter_sends_time_interval;
	public $total_sent_last_semester;
	public $total_subscribers_last_time_interval;
	public $total_exclusion_requests_time_interval;
	public $all_users;

	function __construct() {

		self::set_time_intervals(); //carregar informação dos intervalos de tempo

		//static / flat data
		self::get_total_sent(); //mensagens abertas e enviadas
		$this->subscribers_by_interval = self::get_subscribers_by_time_interval($_SESSION["subscritores_bars"]);
		$this->subscribers_per_month = self::get_subscribers_per_month();
		$this->hard_bounces_count = self::get_hard_bounces_count();
		$this->exclusion_requests_per_month = self::get_exclusion_requests_per_month();		
		$this->total_delivered = $this->total_sent - $this->hard_bounces_count; //total entregues

		//pie graph - adaptado a intervalos de tempo
		$this->delivered_last_time_interval = $this->delivered_last_time_interval($_SESSION["envios_pie"]); //enviadas
		$this->opened_last_time_interval = $this->opened_last_time_interval($_SESSION["envios_pie"]); //lidos - trimestre
		$this->bounced_last_time_interval = $this->bounced_last_time_interval($_SESSION["envios_pie"]); //devolvidos - trimestre

		//totals
		$this->total_subscribers_last_time_interval = self::total_subscribers_last_time_interval($_SESSION["totais_stats"]);
		$this->total_newsletter_sends_time_interval = self::total_newsletter_sends_time_interval($_SESSION["totais_stats"]);
		$this->total_exclusion_requests_time_interval = self::total_exclusion_requests_time_interval($_SESSION["totais_stats"]);

		$this->all_users = $this->get_all_users();

	}

	function get_all_users() {
		$query = "select * from users where is_active = 1";
		$res = mysql_query($query) or die( mysql_error().$sql );;
		while ( $row = mysql_fetch_array($res) ) {
			$aux["name"] = $row["first_name"] . " " . $row["last_name"];
			$aux["id"] = $row["id"];
			$ret[] = $aux;
		}
		return $ret;

	}

	function set_time_intervals(){
		//load em session - envios_pie, subscritores_bars, totais_stats
		$_SESSION["envios_pie"] = empty($_SESSION["envios_pie"]) ? "trimester" : $_SESSION["envios_pie"];
		$_SESSION["subscritores_bars"] = empty($_SESSION["subscritores_bars"]) ? "trimester" : $_SESSION["subscritores_bars"];
		$_SESSION["totais_stats"] = empty($_SESSION["totais_stats"]) ? "trimester" : $_SESSION["totais_stats"];

		if ( $_SESSION["user"]->is_admin ) {
			$ids = "1,6,7,8";
		}else{
			$ids = $_SESSION["user"]->id;
		}

		$_SESSION["envios_pie_users"] = empty($_SESSION["envios_pie_users"]) ? $ids : $_SESSION["envios_pie_users"];
		$_SESSION["subscritores_bars_users"] = empty($_SESSION["subscritores_bars_users"]) ? $ids : $_SESSION["subscritores_bars_users"];
		$_SESSION["totais_stats_users"] = empty($_SESSION["totais_stats_users"]) ? $ids : $_SESSION["totais_stats_users"];

		if(!empty($_POST["time_period"]["envios_pie"])) $_SESSION["envios_pie"] = $_POST["time_period"]["envios_pie"];
		if(!empty($_POST["time_period"]["subscritores_bars"])) $_SESSION["subscritores_bars"] = $_POST["time_period"]["subscritores_bars"];
		if(!empty($_POST["time_period"]["totais_stats"])) $_SESSION["totais_stats"] = $_POST["time_period"]["totais_stats"];

		if(!empty($_POST["time_period"]["envios_pie_users"])) $_SESSION["envios_pie_users"] = $_POST["time_period"]["envios_pie_users"];
		if(!empty($_POST["time_period"]["subscritores_bars_users"])) $_SESSION["subscritores_bars_users"] = $_POST["time_period"]["subscritores_bars_users"];
		if(!empty($_POST["time_period"]["totais_stats_users"])) $_SESSION["totais_stats_users"] = $_POST["time_period"]["totais_stats_users"];

		$this->time_interval_labels = array(
			"trimester" => "trimestre",
			"semester" => "semestre",
			"year" => "ano"
			);

	}

	function get_previous_time_interval($time_period){

	}

	function total_subscribers_last_time_interval($time_period){
		//dentro do intervalo (trimestre / semestre) em qual estamos? 1º ou 2º trimesttre / semestre
		$time_interval_now = self::get_current_time_interval($time_period);
		$time_interval = self::load_time_interval_map($time_period);

		$month_start = $time_interval[$time_interval_now]["from"];
		$month_end = $time_interval[$time_interval_now]["to"];

		//comparar com último intervalo de igual período
		//caso 1 - o intervalo anterior corresponde a um ano anterior e terá de se adaptar a query
		if($time_interval_now - 1 == 0){
			$key = count($time_interval);
			$prev_month_start = $time_interval[$key]["from"];
			$prev_month_end = $time_interval[$key]["to"];
			$prev_year = date("Y") - 1;
		}
		else{
			$key = $time_interval_now - 1;
			$prev_month_start = $time_interval[$key]["from"];
			$prev_month_end = $time_interval[$key]["to"];
			$prev_year = date("Y"); //current year
		}

		//query
		$sql = "SELECT COUNT(subscribers.id) AS total 
			FROM subscribers 
			left join subscriber_by_cat on subscriber_by_cat.id_subscriber = subscribers.id 
			left join newsletter_categorias on newsletter_categorias.id = subscriber_by_cat.id_categoria 
			left join user_permissions on user_permissions.group_id = newsletter_categorias.id 
			WHERE MONTH(subscribers.date_created) BETWEEN ".$month_start." AND " . $month_end . " AND YEAR(subscribers.date_created) = ".date("Y") . " and user_permissions.user_id IN(".$_SESSION["totais_stats_users"].")
			UNION 
			SELECT COUNT(subscribers.id) AS total 
			FROM subscribers 
			left join subscriber_by_cat on subscriber_by_cat.id_subscriber = subscribers.id 
			left join newsletter_categorias on newsletter_categorias.id = subscriber_by_cat.id_categoria 
			left join user_permissions on user_permissions.group_id = newsletter_categorias.id 
				WHERE MONTH(subscribers.date_created) BETWEEN ".$prev_month_start." AND " . $prev_month_end . " 
					AND YEAR(subscribers.date_created) = ".$prev_year . " and user_permissions.user_id in( ". $_SESSION["totais_stats_users"] . " )";
		//echo "<hr />" . $sql . "<hr />";
		$query = mysql_query($sql) or die( mysql_error().$sql );

		while ($row = mysql_fetch_object($query)) {
			$output[] = $row;
		}

		if(empty($output[1]))
			$output[1] = $output[0];

		return $output;
	}

	function total_exclusion_requests_time_interval($time_period){
		//dentro do intervalo (trimestre / semestre) em qual estamos? 1º ou 2º trimesttre / semestre
		$time_interval_now = self::get_current_time_interval($time_period);
		$time_interval = self::load_time_interval_map($time_period);

		$month_start = $time_interval[$time_interval_now]["from"];
		$month_end = $time_interval[$time_interval_now]["to"];

		//comparar com último intervalo de igual período
		//caso 1 - o intervalo anterior corresponde a um ano anterior e terá de se adaptar a query
		if($time_interval_now - 1 == 0){
			$key = count($time_interval);
			$prev_month_start = $time_interval[$key]["from"];
			$prev_month_end = $time_interval[$key]["to"];
			$prev_year = date("Y") - 1;
		}
		else{
			$key = $time_interval_now - 1;
			$prev_month_start = $time_interval[$key]["from"];
			$prev_month_end = $time_interval[$key]["to"];
			$prev_year = date("Y"); //current year
		}



		//query
		$sql = "
		SELECT COUNT(subscribers.id) AS total 
			FROM subscribers 
			left join subscriber_by_cat on subscriber_by_cat.id_subscriber = subscribers.id 
			left join newsletter_categorias on newsletter_categorias.id = subscriber_by_cat.id_categoria 
			left join user_permissions on user_permissions.group_id = newsletter_categorias.id 
			WHERE requested_exclusion > 0 AND MONTH(subscribers.date_created) BETWEEN ".$month_start." AND " . $month_end . " AND YEAR(subscribers.date_created) = ".date("Y") . " and user_permissions.user_id IN(".$_SESSION["totais_stats_users"].")
			UNION 
			SELECT COUNT(subscribers.id) AS total 
			FROM subscribers 
			left join subscriber_by_cat on subscriber_by_cat.id_subscriber = subscribers.id 
			left join newsletter_categorias on newsletter_categorias.id = subscriber_by_cat.id_categoria 
			left join user_permissions on user_permissions.group_id = newsletter_categorias.id 
				WHERE requested_exclusion > 0 AND MONTH(subscribers.date_created) BETWEEN ".$prev_month_start." AND " . $prev_month_end . " 
					AND YEAR(subscribers.date_created) = ".$prev_year . " and user_permissions.user_id in( ". $_SESSION["totais_stats_users"] . " )";
		//echo "<hr />" . $sql . "<hr />";

		/*
		//Previous query, before users
		$sql = "SELECT COUNT(id) AS total 
			FROM subscribers 
			WHERE requested_exclusion > 0 AND MONTH(date_updated) BETWEEN ".$month_start." AND ".$month_end . " AND year(date_updated) = " . date("Y") . " 
				UNION 
			SELECT COUNT(id) AS total 
				FROM subscribers 
				WHERE requested_exclusion > 0 AND MONTH(date_updated) BETWEEN ".$prev_month_start." AND ".$prev_month_end . " AND year(date_updated) = " . $prev_year;
		echo "<hr />" . $sql . "<hr />";
		*/
		$query = mysql_query($sql) or die( mysql_error().$sql );;

		while ($row = mysql_fetch_object($query)) {
			$output[] = $row;
		}

		if(empty($output[1]))
			$output[1] = $output[0];

		return $output;
	}

	function total_newsletter_sends_time_interval($time_period){
		//dentro do intervalo (trimestre / semestre) em qual estamos? 1º ou 2º trimesttre / semestre
		$time_interval_now = self::get_current_time_interval($time_period);
		$time_interval = self::load_time_interval_map($time_period);

		$month_start = $time_interval[$time_interval_now]["from"];
		$month_end = $time_interval[$time_interval_now]["to"];

		//comparar com último intervalo de igual período
		//caso 1 - o intervalo anterior corresponde a um ano anterior e terá de se adaptar a query
		if($time_interval_now - 1 == 0){
			$key = count($time_interval);
			$prev_month_start = $time_interval[$key]["from"];
			$prev_month_end = $time_interval[$key]["to"];
			$prev_year = date("Y") - 1;
		}
		else{
			$key = $time_interval_now - 1;
			$prev_month_start = $time_interval[$key]["from"];
			$prev_month_end = $time_interval[$key]["to"];
			$prev_year = date("Y"); //current year
		}

		//query
		$sql = "SELECT COUNT(envios.id) AS total 
			FROM envios 
			WHERE month(date_sent) 
			BETWEEN " . $month_start . " AND " . $month_end . " AND year(date_sent) = " . date("Y") . " and user_id in (".$_SESSION["totais_stats_users"].")
				UNION 
			SELECT COUNT(envios.id) AS total 
				FROM envios WHERE month(date_sent) 
				BETWEEN " . $prev_month_start . " AND " . $prev_month_end . " AND year(date_sent) = " . $prev_year . " and user_id in (".$_SESSION["totais_stats_users"].")";
		//echo "<hr />" . $sql . "<hr />";
		$query = mysql_query($sql) or die( mysql_error().$sql );;

		while ($row = mysql_fetch_object($query)) {
			$output[] = $row;
		}

		if(empty($output[1]))
			$output[1] = $output[0];

		return $output;

	}

	function bounced_last_time_interval($time_period){
		
		//dentro do intervalo (trimestre / semestre) em qual estamos? 1º ou 2º trimesttre / semestre
		$time_interval_now = self::get_current_time_interval($time_period);
		$time_interval = self::load_time_interval_map($time_period);

		$month_start = $time_interval[$time_interval_now]["from"];
		$month_end = $time_interval[$time_interval_now]["to"];


		$sql = "SELECT SUM(hard_bounces_count) AS total, MONTH(last_bounce_added) AS month_bounced, YEAR(last_bounce_added) FROM subscribers WHERE hard_bounces_count > 0 GROUP BY month_bounced HAVING month_bounced BETWEEN ".$month_start." AND ".$month_end;
		$query = mysql_query($sql) or die( mysql_error().$sql );;
		$result = mysql_fetch_object($query);

		return $result->total;
	}

	function opened_last_time_interval($time_period){

		//dentro do intervalo (trimestre / semestre) em qual estamos? 1º ou 2º trimesttre / semestre
		$time_interval_now = self::get_current_time_interval($time_period);
		//echo $time_interval_now;
		$time_interval = self::load_time_interval_map($time_period);
		//var_dump($time_interval);

		$month_start = $time_interval[$time_interval_now]["from"];
		$month_end = $time_interval[$time_interval_now]["to"];


		$sql = "SELECT SUM(mensagens_abertas) as total FROM stats WHERE month BETWEEN ".$month_start." AND " . $month_end . " and user_id in (".$_SESSION["envios_pie_users"].")";
		//echo $sql;
		$query = mysql_query($sql) or die( mysql_error().$sql );;
		$result = mysql_fetch_object($query);

		return $result->total;
	}

	function delivered_last_time_interval($time_period = "trimester"){

		//dentro do intervalo (trimestre / semestre) em qual estamos? 1º ou 2º trimesttre / semestre
		$time_interval_now = self::get_current_time_interval($time_period);
		$time_interval = self::load_time_interval_map($time_period);

		$month_start = $time_interval[$time_interval_now]["from"];
		$month_end = $time_interval[$time_interval_now]["to"];

		$sql = "SELECT SUM(mensagens_enviadas) as total FROM stats WHERE month BETWEEN ".$month_start." AND " . $month_end . "";
		$query = mysql_query($sql) or die( mysql_error().$sql );;
		$result = mysql_fetch_object($query);

		return $result->total;
	}

	function get_total_sent(){
		$sql = "SELECT mensagens_enviadas, mensagens_abertas FROM stats where user_id in ( ".$_SESSION["envios_pie_users"]." )";
		$query = mysql_query($sql) or die( mysql_error().$sql );;

		$result = mysql_fetch_object($query);
		$this->total_sent = $result->mensagens_enviadas;
		$this->total_opened = $result->mensagens_abertas;

	}

	function get_exclusion_requests_per_month(){
		$sql = "SELECT COUNT(subscribers.id) / 12 AS average, MONTH(subscribers.date_updated), YEAR(subscribers.date_updated) 
			FROM subscribers 
			left join subscriber_by_cat on subscriber_by_cat.id_subscriber = subscribers.id 
			left join newsletter_categorias on newsletter_categorias.id = subscriber_by_cat.id_categoria 
			left join user_permissions on user_permissions.group_id = newsletter_categorias.id 
				WHERE requested_exclusion = 1 AND YEAR(date_updated) = ".date("Y") . " and user_permissions.user_id in ( ".$_SESSION["envios_pie_users"]." )";
		$query = mysql_query($sql) or die( mysql_error().$sql );

		$result = mysql_fetch_object($query);

		return number_format($result->average, 2);
	}

	function get_hard_bounces_count(){
		$sql = "SELECT count(subscribers.id) AS total_bounces 
			FROM subscribers 
			left join subscriber_by_cat on subscriber_by_cat.id_subscriber = subscribers.id 
			left join newsletter_categorias on newsletter_categorias.id = subscriber_by_cat.id_categoria 
			left join user_permissions on user_permissions.group_id = newsletter_categorias.id 
				where hard_bounces_count>0 and user_permissions.user_id in (".$_SESSION["envios_pie_users"].")";
		//echo $sql;

		$query = mysql_query($sql) or die( mysql_error().$sql );
		$result = mysql_fetch_object($query);

		return $result->total_bounces;
	}

	//baseado no numero de subscribers por ano
	function get_subscribers_per_month(){
		$sql = "SELECT COUNT(subscribers.id) / 12 AS average, YEAR(subscribers.`date_created`) AS year_created 
			FROM subscribers 
			left join subscriber_by_cat on subscriber_by_cat.id_subscriber = subscribers.id 
			left join newsletter_categorias on newsletter_categorias.id = subscriber_by_cat.id_categoria 
			left join user_permissions on user_permissions.group_id = newsletter_categorias.id 
				WHERE YEAR(subscribers.date_created) = ".date("Y")." GROUP BY year_created and user_permissions.user_id in ( ".$_SESSION["envios_pie_users"]." )";
		$query = mysql_query($sql) or die( mysql_error().$sql );;

		$result = mysql_fetch_object($query);

		return number_format($result->average, 2);
	}

	//como vai ser necessaria para escala de tempo
	function load_time_interval_map($time_type){


		//devolve um mapeamento de datas, consoante o que for solicitado, por forma a ser mais simples a utilização de outras escalas temporais
		switch ($time_type) {
			case 'trimester':
			case 'trimesters':
				$time_map[1] = array("from" => 1, "to" => 3);
				$time_map[2] = array("from" => 3, "to" => 6);
				$time_map[3] = array("from" => 6, "to" => 9);
				$time_map[4] = array("from" => 9, "to" => 12);
				break;

			case 'semester':
				$time_map[1] = array("from" => 1, "to" => 6);
				$time_map[2] = array("from" => 6, "to" => 12);
				break;

			case 'year'	:
				$time_map[1] = array("from" => 1, "to" => 12);
				break;
			default:
				$time_map[1] = array("from" => 1, "to" => 3);
				$time_map[2] = array("from" => 3, "to" => 6);
				$time_map[3] = array("from" => 6, "to" => 9);
				$time_map[4] = array("from" => 9, "to" => 12);
				break;
		}

		return $time_map;
	}

	function get_current_trimester(){
		return floor((date("m") / 3)); //entre ceil e floor a diferença é se estivermos no mês 5, o último trimestre pode ser de 01 a 03, ou de 03 a 06
	}

	function get_current_time_interval($time_interval){


		switch ($time_interval) {
			case 'semester':
				$output = ceil((date("m") / 6));
				break;
			case 'year':
				$output = 1;
			default:
				$output =  floor((date("m") / 3));
				break;
		}

		return $output;
	}

	function get_subscribers_by_time_interval($time_period){

		//dentro do intervalo (trimestre / semestre) em qual estamos? 1º ou 2º trimesttre / semestre
		$time_interval_now = self::get_current_time_interval($time_period);
		$time_interval = self::load_time_interval_map($time_period);

		$month_start = $time_interval[$time_interval_now]["from"];
		$month_end = $time_interval[$time_interval_now]["to"];

		$date_start = date("Y-".$month_start."-01");
		$date_end = date("Y-".$month_end."-31");
		
		//query
		$sql = "
		SELECT *, COUNT(subscribers.id) AS total, MONTH(subscribers.date_created) AS month_created 
			FROM subscribers 
			left join subscriber_by_cat on subscriber_by_cat.id_subscriber = subscribers.id 
			left join newsletter_categorias on newsletter_categorias.id = subscriber_by_cat.id_categoria 
			left join user_permissions on user_permissions.group_id = newsletter_categorias.id 
			WHERE subscribers.date_created BETWEEN '".$date_start."' AND '".$date_end."' and user_permissions.user_id IN(".$_SESSION["subscritores_bars_users"].") GROUP BY month_created";


		/*
		//Old Query, before grouped by user
		$sql = "SELECT COUNT(id) AS total, MONTH(date_created) AS month_created FROM subscribers WHERE date_created BETWEEN '".$date_start."' AND '".$date_end."' GROUP BY month_created";
		*/
		//echo " <hr /> " . $sql . " <hr /> ";
		$query = mysql_query($sql) or die( mysql_error().$sql );

		while ($row = mysql_fetch_object($query)) {
			$this->subscribers_last_time_interval += $row->total;
			$subscribers_totals[$row->month_created] = $row->total;
		}

		for ($i= $time_interval[$time_interval_now]["from"]; $i <= $time_interval[$time_interval_now]["to"]; $i++) { 
			$subscribers_per_month[$i] = (int) $subscribers_totals[$i];
		}

		return $subscribers_per_month;

	}
}


?>