<?php

/**
 * Freepage
 *
 * Class to manage news.
 *
 *
 * @package Bright CMS
 * @author Fred Flinstone
 * @var
 * */
class Mass_email {

	private $debug = '';
	public $lang;
	private $mod;

	function __construct($mode = '') {
		$this->debug = new Debug();
		$this->lang = $_SESSION['lang'];
		$this->mod = 'mass_email';

	}

	function __destruct() {
		$this->debug->__destruct();
		unset($this->debug);
		$this->lang = null;
		unset($this->lang);
	}
}

?>