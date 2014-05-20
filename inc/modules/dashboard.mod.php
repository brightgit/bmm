<?php 
/**
 * Dashboard
 */
class Dashboard {
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
		$res = mysql_query($query);
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

		$_SESSION["envios_pie_users"] = empty($_SESSION["envios_pie_users"]) ? $_SESSION["user"]->id : $_SESSION["envios_pie_users"];
		$_SESSION["subscritores_bars_users"] = empty($_SESSION["subscritores_bars_users"]) ? $_SESSION["user"]->id : $_SESSION["subscritores_bars_users"];
		$_SESSION["totais_stats_users"] = empty($_SESSION["totais_stats_users"]) ? $_SESSION["user"]->id : $_SESSION["totais_stats_users"];

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
			left join user_groups on user_groups.id = subscriber_by_cat.id_categoria
			left join user_permissions on user_permissions.group_id = user_groups.id
			left join users on user_permissions.user_id = users.id
			WHERE MONTH(date_created) BETWEEN ".$month_start." AND " . $month_end . " AND YEAR(date_created) = ".date("Y") . " and users.id IN(".$_SESSION["totais_stats_users"].")
			UNION 
			SELECT COUNT(subscribers.id) AS total 
				FROM subscribers 
				left join subscriber_by_cat on subscriber_by_cat.id_subscriber = subscribers.id
				left join user_groups on user_groups.id = subscriber_by_cat.id_categoria
				left join user_permissions on user_permissions.group_id = user_groups.id
				left join users on user_permissions.user_id = users.id
				WHERE MONTH(date_created) BETWEEN ".$prev_month_start." AND " . $prev_month_end . " 
					AND YEAR(date_created) = ".$prev_year . " and users.id in( ". $_SESSION["totais_stats_users"] . " )";
		//echo "<hr />" . $sql . "<hr />";
		$query = mysql_query($sql) or die( mysql_error() );

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
		$sql = "SELECT COUNT(subscribers.id) AS total 
			FROM subscribers 
			left join subscriber_by_cat on subscriber_by_cat.id_subscriber = subscribers.id
			left join user_groups on user_groups.id = subscriber_by_cat.id_categoria
			left join user_permissions on user_permissions.group_id = user_groups.id
			left join users on user_permissions.user_id = users.id
			WHERE requested_exclusion > 0 AND MONTH(date_created) BETWEEN ".$month_start." AND " . $month_end . " AND YEAR(date_created) = ".date("Y") . " and users.id IN(".$_SESSION["totais_stats_users"].")
			UNION 
			SELECT COUNT(subscribers.id) AS total 
				FROM subscribers 
				left join subscriber_by_cat on subscriber_by_cat.id_subscriber = subscribers.id
				left join user_groups on user_groups.id = subscriber_by_cat.id_categoria
				left join user_permissions on user_permissions.group_id = user_groups.id
				left join users on user_permissions.user_id = users.id
				WHERE requested_exclusion > 0 AND MONTH(date_created) BETWEEN ".$prev_month_start." AND " . $prev_month_end . " 
					AND YEAR(date_created) = ".$prev_year . " and users.id in( ". $_SESSION["totais_stats_users"] . " )";
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
		$query = mysql_query($sql);

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
		$query = mysql_query($sql);

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
		$query = mysql_query($sql);
		$result = mysql_fetch_object($query);

		return $result->total;
	}

	function opened_last_time_interval($time_period){

		//dentro do intervalo (trimestre / semestre) em qual estamos? 1º ou 2º trimesttre / semestre
		$time_interval_now = self::get_current_time_interval($time_period);
		$time_interval = self::load_time_interval_map($time_period);

		$month_start = $time_interval[$time_interval_now]["from"];
		$month_end = $time_interval[$time_interval_now]["to"];


		$sql = "SELECT SUM(mensagens_abertas) as total FROM stats WHERE month BETWEEN ".$month_start." AND " . $month_end;
		$query = mysql_query($sql);
		$result = mysql_fetch_object($query);

		return $result->total;
	}

	function delivered_last_time_interval($time_period = "trimester"){

		//dentro do intervalo (trimestre / semestre) em qual estamos? 1º ou 2º trimesttre / semestre
		$time_interval_now = self::get_current_time_interval($time_period);
		$time_interval = self::load_time_interval_map($time_period);

		$month_start = $time_interval[$time_interval_now]["from"];
		$month_end = $time_interval[$time_interval_now]["to"];

		$sql = "SELECT SUM(mensagens_enviadas) as total FROM stats WHERE month BETWEEN ".$month_start." AND " . $month_end;
		$query = mysql_query($sql);
		$result = mysql_fetch_object($query);

		return $result->total;
	}

	function get_total_sent(){
		$sql = "SELECT mensagens_enviadas, mensagens_abertas FROM stats";
		$query = mysql_query($sql);

		$result = mysql_fetch_object($query);
		$this->total_sent = $result->mensagens_enviadas;
		$this->total_opened = $result->mensagens_abertas;

	}

	function get_exclusion_requests_per_month(){
		$sql = "SELECT COUNT(id) / 12 AS average, MONTH(date_updated), YEAR(date_updated) FROM subscribers WHERE requested_exclusion = 1 AND YEAR(date_updated) = ".date("Y");
		$query = mysql_query($sql);

		$result = mysql_fetch_object($query);

		return number_format($result->average, 2);
	}

	function get_hard_bounces_count(){
		$sql = "SELECT count(id) AS total_bounces FROM subscribers where hard_bounces_count>0";
		$query = mysql_query($sql);
		$result = mysql_fetch_object($query);

		return $result->total_bounces;
	}

	//baseado no numero de subscribers por ano
	function get_subscribers_per_month(){
		$sql = "SELECT COUNT(id) / 12 AS average, YEAR(`date_created`) AS year_created FROM subscribers WHERE YEAR(date_created) = ".date("Y")." GROUP BY year_created";
		$query = mysql_query($sql);

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
			default:
				# code...
				break;
		}

		return $time_map;
	}

	function get_current_trimester(){
		return floor((date("m") / 3)); //entre ceil e floor a diferença é se estivermos no mês 5, o último trimestre pode ser de 01 a 03, ou de 03 a 06
	}

	function get_current_time_interval($time_interval){

		switch ($time_interval) {
			case 'trimester':
				$output =  floor((date("m") / 3));
				break;
			case 'semester':
				$output = ceil((date("m") / 6));
				break;
			case 'year':
				$output = 1;
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
		SELECT COUNT(subscribers.id) AS total, MONTH(date_created) AS month_created
			FROM subscribers 
			left join subscriber_by_cat on subscriber_by_cat.id_subscriber = subscribers.id
			left join user_groups on user_groups.id = subscriber_by_cat.id_categoria
			left join user_permissions on user_permissions.group_id = user_groups.id
			left join users on user_permissions.user_id = users.id
			WHERE date_created BETWEEN '".$date_start."' AND '".$date_end."' and users.id IN(".$_SESSION["subscritores_bars_users"].") GROUP BY month_created";


		/*
		//Old Query, before grouped by user
		$sql = "SELECT COUNT(id) AS total, MONTH(date_created) AS month_created FROM subscribers WHERE date_created BETWEEN '".$date_start."' AND '".$date_end."' GROUP BY month_created";
		*/
		echo " <hr /> " . $sql . " <hr /> ";
		$query = mysql_query($sql);

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


$dashboard = new Dashboard;

?>

<div class="dashboard">
	
	<div class="full-widget">
		<div class="collumn">
			<span class="operator">/</span>
			<span class="number"><?php echo $dashboard->total_delivered ?></span>
			<span class="label">Entregues</span>			
		</div>
		
		<div class="collumn">
			<span class="operator">=</span>
			<span class="number"><?php echo $dashboard->total_sent ?></span>
			<span class="label">Enviados</span>
		</div>
		
		<div class="collumn collumn-result">
			<span class="number"><?php echo number_format((($dashboard->total_delivered * 100) / $dashboard->total_sent), 2) ?>%</span>
			<span class="label">Taxa de entrega</span>
		</div>
		
		<div class="clear"></div>
		<hr class="data-split" />
		
		<table class="compact" width="100%">
			<tr>
				<td>
					<span class="number"><?php echo number_format(($dashboard->total_sent / 12), 2) ?></span>
					<span class="label">Envios / mês</span>
				</td>
				<td>
					<span class="number"><?php echo $dashboard->subscribers_per_month ?></span>
					<span class="label">Subscritores / mês</span>
				</td>
				<td>
					<span class="number"><?php echo $dashboard->hard_bounces_count ?></span>
					<span class="label">Devolvidos</span>
				</td>
				<td>
					<span class="number"><?php echo $dashboard->total_exclusion_requests_time_interval[0]->total ?></span>
					<span class="label">Pedidos remoção</span>
				</td>
			</tr>
		</table>
		
		
	</div>
	
	<!-- envios -->
	<table width="100%;" class="table-widgets">
		<tr>
			<td>
	<div class="widget widget-orange flip-container">
			
		<div class="flipper widget-wrap">					
			<div class="front">
				<button class="flip-widget flip-widget-open"><i class="icon-white icon-cog"></i></button>
				<h2 class="widget-title"><i class="icon-white icon-envelope"></i> Envios último <?php echo $dashboard->time_interval_labels[$_SESSION["envios_pie"]] ?></h2>
				<div id="morris-graph-demo-1" style="height:170px;">
					<?php 
						$percent_abertas = number_format(($dashboard->opened_last_time_interval * 100 / $dashboard->delivered_last_time_interval),2);
						$percent_entregues = number_format(($dashboard->delivered_last_time_interval - $dashboard->opened_last_time_interval - $dashboard->bounced_last_time_interval) * 100 / $dashboard->delivered_last_time_interval, 2);
						$percent_bounces = number_format(($dashboard->bounced_last_time_interval * 100) / $dashboard->delivered_last_time_interval, 2);
					?>
					<input type="hidden" name="pie_graph_delivered_last_time_interval" value="<?php echo $percent_entregues ?>" />
					<input type="hidden" name="pie_graph_opened_last_time_interval" value="<?php echo $percent_abertas ?>" />
					<input type="hidden" name="pie_graph_bounced_last_time_interval" value="<?php echo $percent_bounces ?>" />
				</div>
			</div>
			<div class="back">
				<button class="flip-widget flip-widget-open"><i class="icon-white icon-check"></i></button>
				<h2 class="widget-title"><i class="icon-white icon-envelope"></i> Envios último <?php echo $dashboard->time_interval_labels[$_SESSION["envios_pie"]] ?></h2>
				<div class="back-settings" <?php echo ( $_SESSION["user"]->is_admin )?' style="padding-top:0;"':'' ?>>
					<div class="back-settings" <?php echo ( $_SESSION["user"]->is_admin )?' style="padding-top:0;"':'' ?>>
						<form name="envios_pie" action="?mod=dashboard" method="post">
							<label>Seleccionar período</label>
							<select name="time_period[envios_pie]">
								<option>Escolha um período</option>
								<option value="trimester">Último trimestre</option>
								<option value="semester">Último semestre</option>						
								<option value="year">Último ano</option>
							</select>
							<?php if ($_SESSION["user"]->is_admin): ?>
								<label>Utilizador</label>
								<select name="time_period[envios_pie_users]">
								<?php foreach ($dashboard->all_users as $key => $value): ?>
									<?php $ids[] = $value["id"]; ?>
									<option <?php echo ( $_SESSION["envios_pie_users"] == $value["id"] )?'selected="selected"':''; ?> value="<?php echo $value["id"] ?>"><?php echo $value["name"] ?></option>
								<?php endforeach ?>
									<option <?php echo ( $_SESSION["envios_pie_users"] == $value["id"] )?'selected="selected"':''; ?> value="<?php echo implode(",", $ids); ?>">Todos</option>

								</select>
								
							<?php endif ?>
							<input type="submit" name="submit" value="Alterar" class="btn btn-primary" />
						</form>
					</div>
					<!--label>Apresentar:</label>
					<span class="setting-inline"><input type="checkbox" checked="checked" /> Entregues</span>
					<span class="setting-inline"><input type="checkbox" checked="checked" /> Lidos</span>
					<span class="setting-inline"><input type="checkbox" checked="checked" /> Devolvidos</span>
					<span class="setting-inline"><input type="checkbox" /> Cliques</span-->
				</div>
			</div>
		</div>
		
	</div>
	<!-- /envios -->
	</td>
	
	<td>
	
	<!-- envios -->
	<div class="widget widget-blue widget-center flip-container">
			
		<div class="flipper widget-wrap">					
			<div class="front">
				<button class="flip-widget flip-widget-open"><i class="icon-white icon-cog"></i></button>
				<h2 class="widget-title"><i class="icon-white icon-user"></i> Subscritores último <?php echo $dashboard->time_interval_labels[$_SESSION["subscritores_bars"]] ?></h2>
				<div id="morris-graph-demo-2" style="height:220px;">
					<?php foreach($dashboard->subscribers_by_interval as $month => $total): ?>
					<input type="hidden" class="subscriber_month_totals" name="month_<?php echo $month ?>" value="<?php echo $total ?>" />
					<?php endforeach; ?>
				</div>
			</div>
			<div class="back">
				<button class="flip-widget flip-widget-open"><i class="icon-white icon-check"></i></button>
				<h2 class="widget-title"><i class="icon-white icon-user"></i> Subscritores último <?php echo $dashboard->time_interval_labels[$_SESSION["subscritores_bars"]]; ?></h2>
				<div class="back-settings" <?php echo ( $_SESSION["user"]->is_admin )?' style="padding-top:0;"':'' ?>>
					<form name="subscritores_bars" action="?mod=dashboard" method="post">
						<label>Seleccionar período</label>
						<select name="time_period[subscritores_bars]">
							<option>Escolha um período</option>
							<option value="trimester">Último trimestre</option>
							<option value="semester">Último semestre</option>						
							<option value="year">Último ano</option>
						</select>
						<?php if ($_SESSION["user"]->is_admin): ?>
							<label>Utilizador</label>
							<select name="time_period[subscritores_bars_users]">
							<?php foreach ($dashboard->all_users as $key => $value): ?>
								<?php $ids[] = $value["id"]; ?>
								<option <?php echo ( $_SESSION["subscritores_bars_users"] == $value["id"] )?'selected="selected"':''; ?> value="<?php echo $value["id"] ?>"><?php echo $value["name"] ?></option>
							<?php endforeach ?>
								<option <?php echo ( $_SESSION["subscritores_bars_users"] == $value["id"] )?'selected="selected"':''; ?> value="<?php echo implode(",", $ids); ?>">Todos</option>

							</select>
							
						<?php endif ?>
						<input type="submit" name="submit" value="Alterar" class="btn btn-primary" />
					</form>
				</div>
			</div>
		</div>
		
	</div>
	<!-- /envios -->
	</td>
	
	
	
	<td>
	<!-- dados gerais -->
	<div class="widget widget-green flip-container">
			
		<div class="flipper widget-wrap">					
			<div class="front">
				<button class="flip-widget flip-widget-open"><i class="icon-white icon-cog"></i></button>
				<h2 class="widget-title"><i class="icon-white icon-signal"></i> Totais último <?php echo $dashboard->time_interval_labels[$_SESSION["totais_stats"]] ?></h2>

				<ul class="general-data-list">
					<!-- subscribers -->
					<li>
						<strong><?php echo $dashboard->total_subscribers_last_time_interval[0]->total . ' - '. $dashboard->total_subscribers_last_time_interval[1]->total ?></strong> Subscritores 
						<?php if(($dashboard->total_subscribers_last_time_interval[1]->total != 0) && $dashboard->total_subscribers_last_time_interval[0]->total > $dashboard->total_subscribers_last_time_interval[1]->total): ?>
						<span class="compare compare-positive"><?php echo (($dashboard->total_subscribers_last_time_interval[0]->total * 100) / $dashboard->total_subscribers_last_time_interval[1]->total) -100 ?>% <span class="label label-success"><i class="icon-white icon-chevron-up"></span></i></span>
						<?php elseif(($dashboard->total_subscribers_last_time_interval[1]->total != 0) && $dashboard->total_subscribers_last_time_interval[0]->total < $dashboard->total_subscribers_last_time_interval[1]->total): ?>
						<span class="compare compare-negative"><?php echo number_format(100 - (($dashboard->total_subscribers_last_time_interval[0]->total * 100) / $dashboard->total_subscribers_last_time_interval[1]->total), 2) ?>% <span class="label label-important"><i class="icon-white icon-chevron-down"></span></i></span>
						<?php else: ?>
						<span class="compare compare-negative">0% <span class="label label-warning"><i class="icon-white icon-minus"></span></i></span>
						<?php endif; ?>
					</li>
					<!-- newsletters -->
					<li>
						<strong><?php echo $dashboard->total_newsletter_sends_time_interval[0]->total ?></strong> Newsletters enviadas 
						<?php if(($dashboard->total_newsletter_sends_time_interval[1]->total != 0) && $dashboard->total_newsletter_sends_time_interval[0]->total > $dashboard->total_newsletter_sends_time_interval[1]->total): ?>
						<span class="compare compare-positive"><?php echo (($dashboard->total_newsletter_sends_time_interval[0]->total * 100) / $dashboard->total_newsletter_sends_time_interval[1]->total) -100 ?>% <span class="label label-success"><i class="icon-white icon-chevron-up"></span></i></span>
						<?php elseif(($dashboard->total_newsletter_sends_time_interval[1]->total != 0) && $dashboard->total_newsletter_sends_time_interval[0]->total < $dashboard->total_newsletter_sends_time_interval[1]->total): ?>
						<span class="compare compare-negative"><?php echo number_format(100 - (($dashboard->total_newsletter_sends_time_interval[0]->total * 100) / $dashboard->total_newsletter_sends_time_interval[1]->total), 2) ?>% <span class="label label-important"><i class="icon-white icon-chevron-down"></span></i></span>
						<?php else: ?>
						<span class="compare compare-negative"><?php echo $dashboard->total_newsletter_sends_time_interval[0]->total; ?> <span class="label label-success"><i class="icon-white icon-chevron-up"></span></i></span>
						<?php endif; ?>
					</li>

					<!-- pedidos remoção -->
					<li>
						<strong><?php echo $dashboard->total_exclusion_requests_time_interval[0]->total ?></strong> Pedido(s) de remoção 
						<?php if($dashboard->total_exclusion_requests_time_interval[0]->total > $dashboard->total_exclusion_requests_time_interval[1]->total && $dashboard->total_exclusion_requests_time_interval[1]->total != 0): ?>
						<span class="compare compare-positive"><?php echo (($dashboard->total_exclusion_requests_time_interval[0]->total * 100) / $dashboard->total_exclusion_requests_time_interval[1]->total) - 100 ?>% <span class="label label-success"><i class="icon-white icon-chevron-up"></span></i></span>
						<?php elseif($dashboard->total_exclusion_requests_time_interval[1]->total == 0): ?>
						<span class="compare compare-positive"> <?php echo $dashboard->total_exclusion_requests_time_interval[0]->total ?> <span class="label label-success"><i class="icon-white icon-chevron-up"></span></i></span>
						<?php elseif($dashboard->total_exclusion_requests_time_interval[0]->total < $dashboard->total_exclusion_requests_time_interval[1]->total): ?>
						<span class="compare compare-negative"><?php echo number_format(100 - (($dashboard->total_exclusion_requests_time_interval[0]->total * 100) / $dashboard->total_exclusion_requests_time_interval[1]->total), 2) ?>% <span class="label label-important"><i class="icon-white icon-chevron-down"></span></i></span>
						<?php else: ?>
						<span class="compare compare-negative">0% <span class="label label-warning"><i class="icon-white icon-minus"></span></i></span>
						<?php endif; ?>
					</li>
				</ul>
			</div>
			<div class="back">
				<button class="flip-widget flip-widget-open"><i class="icon-white icon-check"></i></button>
				<h2 class="widget-title"><i class="icon-white icon-signal"></i> Totais último <?php echo $dashboard->time_interval_labels[$_SESSION["totais_stats"]] ?></h2>
				<div class="back-settings" <?php echo ( $_SESSION["user"]->is_admin )?' style="padding-top:0;"':'' ?>>
					<form name="totais_stats" action="?mod=dashboard" method="post">
						<label>Seleccionar período</label>
						<select name="time_period[totais_stats]">
							<option>Escolha um período</option>
							<option value="trimester">Último trimestre</option>
							<option value="semester">Último semestre</option>						
							<option value="year">Último ano</option>
						</select>
						<?php if ($_SESSION["user"]->is_admin): ?>
							<label>Utilizador</label>
							<select name="time_period[totais_stats_users]">
							<?php foreach ($dashboard->all_users as $key => $value): ?>
								<?php $ids[] = $value["id"]; ?>
								<option <?php echo ( $_SESSION["totais_stats_users"] == $value["id"] )?'selected="selected"':''; ?> value="<?php echo $value["id"] ?>"><?php echo $value["name"] ?></option>
							<?php endforeach ?>
								<option <?php echo ( $_SESSION["totais_stats_users"] == $value["id"] )?'selected="selected"':''; ?> value="<?php echo implode(",", $ids); ?>">Todos</option>

							</select>
							
						<?php endif ?>
						<input type="submit" name="submit" value="Alterar" class="btn btn-primary" />
					</form>
				</div>
			</div>
		</div>
		
	</div>
	<!-- /dados gerais -->
	</td>
	</tr>
	</table>
	
	<div class="clear"></div>
		
</div>