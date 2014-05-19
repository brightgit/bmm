<?php

//set debug errors on
//error_reporting(0);
error_reporting(E_ALL ^E_NOTICE ^E_WARNING ^E_STRICT);
ini_set('display_errors', 1);


//Set Datetime
date_default_timezone_set("Europe/Lisbon");
setlocale(LC_ALL, 'Portuguese_Portugal.1252');
session_start();

class Core {

	//-------------------------------------------------------------------------------------
	// TODO: test sessions
	//
    //private $errors = array();
	private $path = '/';
	private $mode = 'fe';
	private $default_lang = 'pt';
	private $mod = '';
	private $tool = '';
	private $inc = '';
	private $ckeditor = '';
	public $user;
	public $settings;
	public static $base_path = "/Users/bright/Documents/htdocs/bmm";

	function __construct($mode = 'fe') {


		error_reporting( E_ALL );

		self::load_general_functions();
		$base_path = base_path();

		$base_path = base_path();

		//dependencies
		require_once base_path('/inc/Debug.class.php');
		require_once base_path('/inc/database.php');
		require_once base_path('/inc/Tools.class.php');
		require_once base_path('/inc/modules/user.mod.php');
		require_once base_path("/inc/libs/ckeditor/ckeditor.php");
		require_once base_path("/inc/modules/settings.mod.php");
		require_once base_path("/inc/modules/feedback.php");

		//carregar as definições da app
		$this->settings = new Settings;

		//carregar o utilizador a consultar a app
		$this->user = New BRIGHT_User;

		//set include dir
		switch($mode){
	    //development
			case 'fe':
			set_include_path ('inc/');
			break;
			case 'bo':
			set_include_path ('../inc/');
			$this->path = '/admin/';
			$this->mode = 'bo';
			break;
			default:
			set_include_path ('../inc/');
			break;
		}

		switch ($_SERVER["HTTP_HOST"]) {
			case "localhost":
			define('_ROOT' , "http://localhost/bmm/admin");
			break;
			default:
			define('_ROOT', 'http://'.$_SERVER["HTTP_HOST"].'/bmm/admin/');
			break;
		}

		$this->tool = new Tools();
		
		$this->setIncFolder($abs_path . "/inc/");
		
	}

	function __destruct() {

		$this->default_lang = null;
		$this->docroot = null;
		$this->mod = null;
		$this->mode = null;
		$this->path = null;

		unset($this->default_lang);
		unset($this->docroot);
		unset($this->mod);
		unset($this->mode);
		unset($this->path);
	}

	//define functions de utilização transversal (root, friendly-url, etc)
	function load_general_functions(){

		function var_bump($var){
			echo '<div style="z-index: 10000000; position: fixed; right:10px; top:10px; padding:15px; background: rgba(0,0,0,0.8); color:#fff; width: 400px; font-size: 13px; line-height: 19px; font-family: Consolas; line-height: 15px; border-radius: 3px; height: 300px; overflow-y: scroll;">'; var_dump($var); echo '</div>';
		}

		function redirect($uri = '', $method = 'location', $http_response_code = 302)
		{
			if ( ! preg_match('#^https?://#i', $uri))
			{
				$uri = base_url($uri);
			}

			switch($method)
			{
				case 'refresh'	: header("Refresh:0;url=".$uri);
				break;
				default			: header("Location: ".$uri, TRUE, $http_response_code);
				break;
			}
			exit;
		}

		function base_url($url = false){
			if($_SERVER["HTTP_HOST"] == "localhost")
				$host = "http://localhost/bmm";
			else
				$host = "http://".$_SERVER["HTTP_HOST"]."/bmm";
			return $host."/".$url;
		}

		//definir o caminho absoluto
		function base_path($url = false){

			if($_SERVER["HTTP_HOST"] == "localhost"){
				$path = "C:/xampp/htdocs/bmm";
				if(!file_exists($path))	
					$path = "/Users/bright/Documents/htdocs/bmm";
			}
			else
				$path = "/chroot/home/TO_BE_DETERMINED_/pmenet/html";
			return $path."/".$url;
		}

		function die_sql( $query = "" ){
			echo '<hr />';
			echo mysql_error();
			echo '<br />';
			echo $query;
			echo '<hr />';
			die();
		}

	}

	function load_view($file, $data = false){

		if(file_exists($file)){
			if($data){
					//criar um object temporario
				$temp_object = new stdClass();
				foreach ($data as $key => $value) {
					$temp_object->$key = $value;
				}
					//passar o core na própria data - deve haver melhor forma de fazer isto. não é grave porque php passa por referência
					//$temp_object->core = $this; não necessário para este caso
					//limpar $data colocando os valores do object temporario
				unset($data);
				$data = $temp_object;
			}

			ob_start();
			include($file);
			$output = ob_get_clean();
			return $output;
		}

		else echo "File <b>".$file."</b> not found";

	}

	static function draw_boolean_status($bool){

		switch ($bool) {
			case 1:
			echo '<img src="../inc/img/admin/yes.png" alt="Sim" />';
			break;
			
			default:
			echo '<img src="../inc/img/admin/no.png" alt="N&atilde;o" />';
			break;
		}
	}


	function show() {

		$this->setLang();
		$this->getRes();


		include("lang/en.php");

		$mod = htmlspecialchars($_GET['mod']);
	}

	function setMod($module = ''){
		if($module!=''){
			$this->mod = $module;
		}
		/*
		 * Descomentar para dashboard
		 */
	/*	 else if($this->mod == '')
	$this->mod = 'index';*/
	else{
		$this->mod = $this->tool->getGet('mod');
	}

}

function getMod($module = '',$params = '') {
		//$inc = get_include_path();
	if($module == '')
		$this->setMod();
	else
		$this->setMod($module);

	
		//echo $this->mod;
	if (file_exists($this->inc."modules/$this->mod.mod.php")
		&& file_exists($this->inc."views/$this->mode/$this->mod.view.php")) {
		include_once("modules/$this->mod.mod.php");
	include_once("views/$this->mode/$this->mod.view.php");
	$str = ucwords($this->mod);
	eval("\$module = new {$str}('bo');");
	$str = 'View'.$str;
	eval("\$view = new {$str}(\$module,\$this,\$params);");
} else if($this->mod == 'index'){
	$str = 'View'.ucwords($this->mod);
	include("views/$this->mode/$this->mod.view.php");
	eval("\$view = new {$str}(\$this);");
}
else{
	$this->errors[] .= 'Module '.$module.' doesn\'t exist';
	return false;
}

}

function setLang() {
	$this->default_lang = "pt";

	$a = array("en","pt","es");
	if (!array_key_exists("lang", $_SESSION)) $_SESSION['lang'] = $this->default_lang;
	if (!in_array($_SESSION['lang'],$a)) $_SESSION['lang'] = $this->default_lang;

	if (!array_key_exists("lang",$_GET)) return;
	$l = $_GET['lang'];

	if (!in_array($l,$a)) return;
	$_SESSION['lang'] = $l;
}

function getLang(){
	if (!array_key_exists("lang", $_SESSION))
		$_SESSION['lang'] = $this->default;
	return $_SESSION['lang'];
}

function getTools(){
	return $this->tool;
}

function getIncFolder(){
	return $this->inc;
}

function setIncFolder($inc){
	$this->inc = $inc;
}

function getFullFolder(){
	return _ROOT.get_include_path();
}

public static function base_path(){
		//chroot/home/brightmi/brightminds.pt/html/bmm/ « isto tem de ser o base path - todos têm chroot/home/<conta_unix>/<domain>/html/<pasta onde está o bmm>
	$homedir = getcwd();
	$base_dir = str_replace('/admin', "", $homedir);
	$base_dir = str_replace('/inc', "", $homedir);

	return $base_dir."/";
}

}
?>