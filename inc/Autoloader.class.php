<?php

class Autoloader {
    public static $instance;
    private $_src = array('admin/inc/Classes/', 'admin/inc/modules/');
    private $_ext = array('.php', '.class.php', '.mod.php');

    /* initialize the autoloader class */
    public static function init(){
    	echo "init";
        if(self::$instance==NULL){
            self::$instance=new self();
        }
        return self::$instance;
    }

    /* put the custom functions in the autoload register when the class is initialized */
    private function __construct($folders){
		$this->_src = $folders;
        spl_autoload_register(array($this, 'clean'));
        spl_autoload_register(array($this, 'dirty'));
    }

    /* the clean method to autoload the class without any includes, works in most cases */
    private function clean($class){
        global $docroot;
        $class=str_replace('_', '/', $class);
        spl_autoload_extensions(implode(',', $this->_ext));
        foreach($this->_src as $resource){
        	//echo "clean:::::::(docroot.resource->".$docroot . $resource . " :::: class==".$class.")<br/>";

			set_include_path($docroot . $resource);
            spl_autoload($class);
        }
    }

    /* the dirty method to autoload the class after including the php file containing the class */
    private function dirty($class){
    	//echo "dirty: ".$class;
        global $docroot;
        $class=str_replace('_', '/', $class);
        foreach($this->_src as $resource){
            foreach($this->_ext as $ext){
          //  	echo "dklashjdfoasdf->".$docroot . $resource . $class . $ext. "<br/>";
                @include($docroot . $resource . $class . $ext);
            }
        }
        spl_autoload($class);
    }

}

?>