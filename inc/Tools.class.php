<?
//system-wide functions goes here
class Tools {

	static function is_email($email){
	    return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9+-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i", $email);
	}

	static function escape_single_quotes($string){
		return str_replace('\'', '\\\'', $string);
	}

	static function remember_subscriber_tab($group_id){
		$_SESSION["subscriber_tab"] = intval($group_id);
	}

	static function check_subscriber_tab(){

		if(!empty($_SESSION["subscriber_tab"]) && $_SESSION["subscriber_tab"] != false ){
			$return = $_SESSION["subscriber_tab"];
			$_SESSION["subscriber_tab"] = false;
		}
		else
			$return = false;

		return $return;
	}

	function html_cut($text, $max_length){


		if(strlen($text) > $max_length){
			$tags   = array();
			$result = "";

			$is_open   = false;
			$grab_open = false;
			$is_close  = false;
			$in_double_quotes = false;
			$in_single_quotes = false;
			$tag = "";

			$i = 0;
			$stripped = 0;

			$stripped_text = strip_tags($text);

			while ($i < strlen($text) && $stripped < strlen($stripped_text) && $stripped < $max_length)
			{
				$symbol  = $text{$i};
				$result .= $symbol;

				switch ($symbol)
				{
					case '<':
					$is_open   = true;
					$grab_open = true;
					break;

					case '"':
					if ($in_double_quotes)
						$in_double_quotes = false;
					else
						$in_double_quotes = true;

					break;

					case "'":
					if ($in_single_quotes)
						$in_single_quotes = false;
					else
						$in_single_quotes = true;

					break;

					case '/':
					if ($is_open && !$in_double_quotes && !$in_single_quotes)
					{
						$is_close  = true;
						$is_open   = false;
						$grab_open = false;
					}

					break;

					case ' ':
					if ($is_open)
						$grab_open = false;
					else
						$stripped++;

					break;

					case '>':
					if ($is_open)
					{
						$is_open   = false;
						$grab_open = false;
						array_push($tags, $tag);
						$tag = "";
					}
					else if ($is_close)
					{
						$is_close = false;
						array_pop($tags);
						$tag = "";
					}

					break;

					default:
					if ($grab_open || $is_close)
						$tag .= $symbol;

					if (!$is_open && !$is_close)
						$stripped++;
				}

				$i++;
			}

			while ($tags)
				$result .= "</".array_pop($tags).">";

			return strip_tags($result, "<a>")." ...";
		}
		else
			return $text;

		
	}

	public static function get_page($post_var){
		return ($_POST[$post_var]);
	}
	//values -> formate
	//jan_min -> 10 de Nov de 2012. 15 horas, 10 min e 15 seg
	public static function timestamp_to_jan($timestamp, $format = 'jan_min'){
		$jan = array('01' => 'Jan', '02' =>'Fev', '03'=>'Mar', '04'=>'Abr', '05'=>'Mai', '06'=>'Jun', '07'=>'Jul', '08'=>'Ago', '09'=>'Set', '10' => 'Out', '11'=>'Nov', '12'=>'Dez');
		if($format == 'jan_min'){
			$time_array = explode(" ", $timestamp);
			$day = explode("-", $time_array[0]);
			$hour = explode(":", $time_array[1]);
			return ((int)$day[2]).' de '.$jan[$day[1]].' de '.$day[0].'. '.((int)$hour[0]).' hora(s), '.((int)$hour[1]).' min(s) e '.((int)$hour[1]).' seg(s)';

		}
	}
	public static function get_timestamp($time = false){
		if(!$time){
			$time=  time();
		}
		return date('Y-m-d H:i:s');
	}
	public static function removeAccents($string){
		$search = explode(",","ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u,ã,õ");
		$replace = explode(",","c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u,a,e");
		$urlTitle = str_replace($search, $replace, $string);
		return $urlTitle;
	}

	public static function make_friendly_url($string){
		$string = strtolower($string);
		$search = explode(",","ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u,ã,õ, ");
		$replace = explode(",","c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u,a,e,-");

		$urlTitle = str_replace($search, $replace, $string);
		return $urlTitle;
	}

	public static function imageexists($path){
		//echo $path;
		if($_SERVER['HTTP_HOST'] != 'localhost'){
			$path = str_replace(_ROOT.'/','',$path);
		}
		$url = @getimagesize($path);
		//echo $url;
		$basedir = ($_SERVER['HTTP_HOST'] == 'localhost') ? '/bvcacilhas': '';
		if(!is_array($url))
			$default_image = _ROOT."/media/images/noimage.gif";
		else
			return $path;


		return $default_image;
	}

	public static function getPost($s) {
		if (array_key_exists($s, $_POST))
			return mysql_real_escape_string(htmlspecialchars($_POST[$s]));
		else return false;
	}


	public static function getGet($s) {
		if (array_key_exists($s, $_GET))
			return mysql_real_escape_string(htmlspecialchars($_GET[$s]));
		else return false;
	}

	public static function send_email($msg, $to, $subject = 'Default subject',$name = '', $from) {
		$headers = 'From: '.$from."\r\n".
		'Reply-To: '.$from."\r\n" .
		'X-Mailer: PHP/' . phpversion();
		$headers .= 'Date: '.date('n/d/Y g:i A').PHP_EOL;

		$result=mail($to, $subject, $msg, $headers);

		return $result;
	}

	public static function createActiveLinks($active,$id,$mod,$option){
		if($active){
			$text = _('Activo');
			$link = _ROOT.'/?mod='.$mod.'&option='.$option.'&action=deactivate&id='.$id;
		}
		else{
			$text = _('Inactivo');
			$link = _ROOT.'/?mod='.$mod.'&option='.$option.'&action=activate&id='.$id;
		}
		echo '<a href="'. $link .'">'	. $text . '</a>';
	}

	public static function createActiveSelect($active,$name){
		echo '<select name="'.$name.'">';
		if($active){
			echo '<option value="0">'._('Não').'</option>';
			echo '<option value="1" selected="selected">'._('Sim').'</option>';
		}
		else{
			echo '<option value="0" selected="selected">'._('Não').'</option>';
			echo '<option value="1">'._('Sim').'</option>';
		}
		echo '</select>';
	}

	public static function createActions($params = array(delete => "Apagar" , activate => "Activar" ,deactivate => "Desactivar")){
		echo '<select name="action">
		<option value="">--</option>';
		foreach ($params as $key => $value) {
			echo '<option value="'.$key.'">'.$value.'</option>';
		}
		echo '</select>';
	}

	public static function createPager($params = Array(10,20,50,100),$selected = 20){
		$path = _ROOT.'/'.get_include_path();

		?>
		<div class="pager" style="margin-bottom:10px;">
			<form>
				<img src="<?php echo $path;?>img/admin/first.png" class="first">
				<img src="<?php echo $path;?>img/admin/prev.png" class="prev">
				<div class="pagedisplay" style="display: inline;"></div>
				<img src="<?php echo $path;?>img/admin/next.png" class="next">
				<img src="<?php echo $path;?>img/admin/last.png" class="last">
				<label><?php echo _('Mostrar')?></label>
				<select class="pagesize">

					<?php
					for ($i=0; $i < sizeof($params); $i++) {
						$sel = '';
						if($params[$i] == $selected)
							$sel = 'selected="selected"';
						echo '<option '.$sel.' value="'.$params[$i].'">'.$params[$i].'</option>';
					}
					?>
				</select>
			</form>
			</div><?php
		}

		public static function doAction($table,$action,$items, $module = 0,$lang = 1){
			$debug = new Debug();
			$wLang = '';
			if($lang)
				$wLang = "lang = '{$_SESSION['lang']}' and";

			if(sizeof($items)>0){
				switch ($action) {
					case 'delete':
					$sql = "delete from $table where $wLang (";
					                                         if($module != 0){
					                                         	$sql2 = "delete from sub_menus where module = $module and $wLang (";
					                                         }
					                                         break;
					                                         case 'activate':
					                                         $sql = "update $table set is_active = 1 where $wLang (";
					                                                                                               break;
					                                                                                               case 'deactivate':
					                                                                                               $sql = "update $table set is_active = 0 where $wLang (";
					                                                                                                                                                     break;
					                                                                                                                                                     default:
					                                                                                                                                                     echo _('Acção solicitada não reconhecida. Tente novamente');
					                                                                                                                                                     return FALSE;
					                                                                                                                                                     break;
					                                                                                                                                                 }
					                                                                                                                                                 foreach ($items as $key) {
					                                                                                                                                                 	$sql .= "id = $key or ";
					                                                                                                                                                 	if($module != 0)
					                                                                                                                                                 		$sql2 .= "module_id = $key or ";
					                                                                                                                                                 }
					                                                                                                                                                 
					                                                                                                                                                 $sql = substr($sql,0,-3) . ')';
$sql2 = substr($sql2,0,-3) . ')';
			/*if(sizeof($items)>1){
				$sql .= ')';
				$sql2 .= ')';
}*/
$res = mysql_query($sql);
if($action == 'delete' && $module != 0)
	$res &= mysql_query($sql2);
if(!$res)
	$debug->dbErrors($sql."<br/>".$sql2."<br/>");

return $res;
}
else
	return FALSE;
$debug->__destruct();
}



public static function inputValue($inputName, $dbValue){
	if(isset($_POST[$inputName]))
		return $_POST[$inputName];
	else
		return $dbValue;
}

}

class Modules{

	private $debug = '';

	function __construct() {
	/*	if($mode!='')
	$this->setMode($mode);*/
	$this->debug = new Debug();
}

function __destruct() {
        //$this->mode = null;
	$this->debug->__destruct();
        //unset($this->mode);
	unset($this->debug);
}

public static function getModulesList(){
	$sql = "select * from modules where lang = '{$_SESSION['lang']}'";

	$res = mysql_query($sql);
	if(!$res)
		$this->debug->dbErrors($sql);
	if(mysql_num_rows($res)>0){
		return $res;
	}else
	return FALSE;

}

public static function getModulesSelect($id){
	$sql = "select * from modules m
	left join (select module as checked from sub_menus where id = $id and lang ='{$_SESSION['lang']}') aux
	on aux.checked = m.id
	where lang = '{$_SESSION['lang']}' AND `is_active`='1'";
	$res = mysql_query($sql);
	if(!$res)
		$this->debug->dbErrors($sql);
	if(mysql_num_rows($res)>0){
		return $res;
	}else
	return FALSE;
}

public static function getModulesPages($id){
	$mod = $this->getModulesDB($id);
	$sql = "select * from $mod m
	left join (select module_id as checked from sub_menus where id = $id and lang = '{$_SESSION['lang']}') aux
	on aux.checked = m.id
	where lang = '{$_SESSION['lang']}'";
	$res = mysql_query($sql);
	if(!$res)
		$this->debug->dbErrors($sql);
	if(mysql_num_rows($res)>0){
		return $res;
	}else
	return FALSE;
}

public static function getModulesDB($id){
	$sql = "select db from modules m
	inner join sub_menus sm on sm.module = m.id and sm.lang = '{$_SESSION['lang']}'
	where sm.id = $id and m.lang = '{$_SESSION['lang']}'";
	$res = mysql_query($sql);
	if(!$res)
		$this->debug->dbErrors($sql);
	if(mysql_num_rows($res)>0){
		return mysql_fetch_object($res)->db;
	}else
	return FALSE;
}

public static function getModulesPagesByMod($mod_id,$id = 0){
	$sql = "select db from modules m
	where m.id = $mod_id and m.lang = '{$_SESSION['lang']}'";
	$res = mysql_query($sql);
	if(!$res)
		$this->debug->dbErrors($sql);
	$mod = mysql_fetch_object($res)->db;
	$sql = "select * from $mod m
	left join (select module_id as checked from sub_menus where id = $id and lang = '{$_SESSION['lang']}') aux
	on aux.checked = m.id
	where lang = '{$_SESSION['lang']}'";
	$res = mysql_query($sql);
	if(!$res)
		$this->debug->dbErrors($sql);
	if(mysql_num_rows($res)>0){
		return $res;
	}else
	return FALSE;

}

public static function getModulesMod($id){
	$sql = "select m.module from modules m
	left join sub_menus sm on sm.module = m.id and sm.lang = '{$_SESSION['lang']}'
	where m.id = $id and m.lang = '{$_SESSION['lang']}' limit 1";
	$res = mysql_query($sql);
	if(!$res)
		$this->debug->dbErrors($sql);
	if(mysql_num_rows($res)>0){
		return mysql_fetch_object($res)->module;
	}else
	return FALSE;
}



}

?>