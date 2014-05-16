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

	function __construct($mode = 'fe') {

		$base_path = "/home/pmenet/public_html/holmes/bmm";
		$base_path = "/Users/bright/Documents/htdocs/pme24/bmm";
		$base_path = "C:/xampp/htdocs/bmm";


		//dependencies
		require_once $base_path . '/inc/Debug.class.php';
		require_once $base_path . '/inc/database.php';
		require_once $base_path . '/inc/Tools.class.php';
		require_once $base_path . '/inc/modules/user.mod.php';
		require_once $base_path . "/inc/libs/ckeditor/ckeditor.php";
		require_once $base_path . "/inc/modules/feedback.php";
		require_once $base_path . "/inc/modules/settings.mod.php";

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
		} else if($this->mod == 'media')
			include("views/$this->mode/$this->mod.view.php");
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