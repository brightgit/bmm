<?php

/**
 * Debug Class
 *
 * Class to manage debugging of application.
 * By default, the debug is activated for local environment. The default behaviour
 * can be changed by calling the new Debug(value). Value must be either 0 or 1
 *
 * @package Bright CMS
 * @author João Ribeiro
 * @var $debug = 0/1
 **/

class Debug{

	private $doDebug = 0;
	private $tools;

	function __construct($debug = '') {
		if($debug==''){
			switch($_SERVER['HTTP_HOST']){
				case 'localhost':
					//$this->setDebug(1);
					break;
				default:
					$this->setDebug(0);
					break;
			}
		}else{
			if($debug != 0 || $debug != 1){
				$this->__destruct();
				die("Debug Class ERROR :: Value must be 0 or 1. '$debug' given!");
			}else
				$this->setDebug($debug);
		}
		//$this->tools = new Tools();
	}

    function __destruct() {
        $this->doDebug = null;
        unset($this->doDebug);
		$this->tools = null;
        unset($this->tools);
    }

	function setDebug($debug){
		$this->doDebug = $debug;
	}

	function getDebug(){
		return $this->doDebug;
	}

	function printSession(){
		foreach ($_SESSION as $key => $value) {

			//se for um object da fatal error e para execucao
			if(is_object($value) || is_array($value)){
				foreach($value as $proprety => $propretyvalue)
					echo "[".$key."] -> ".$proprety." = ".$propretyvalue."<br />";
			}

			else
				echo $key." -> ".$value."<br/>";
		}
	}

	function dbErrors($sql = ''){

		if($this->doDebug){
			
		}
		else{
			$msg = 'A error occured in '. $_SERVER['HTTP_HOST'] .':<br/>'.mysql_error().  '<br/>'. $sql.'<br/><br/>Backtrace::<br/>';
			ob_start();
			var_dump(debug_print_backtrace());
			$msg .= ob_get_clean();

			$msg .= '<br/>';
			//Tools::send_email($msg, 'hugo.silva@bright.pt', 'rarissimas',$_SERVER['HTTP_HOST'].' Mysql Error', '');
		}
	}
}

?>