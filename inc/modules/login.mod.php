<?php
/**
 * Login Class
 *
 * Class to manage login of users.
 * access:
 * Admin : 1
 * BO user : 2
 *
 *
 * @package Bright CMS
 * @author João Ribeiro
 * @var $access
 **/

class Login extends Core{

	private $mode = 'fe';
	private $debug = '';

	function __construct($mode = '') {
		if($mode!='')
			$this->setMode($mode);
		$this->debug = new Debug();
		parent::__construct();
	}

	function __destruct() {
		$this->mode = null;
		$this->debug = null;
		unset($this->mode);
		unset($this->debug);
	}

	function setMode($mode){
		$this->mode = $mode;
	}

	function getMode(){
		return $this->mode;
	}

	function doLogin($user, $pass){
		if($this->getMode()=='bo')
			$sql = "SELECT u.id,u.first_name,u.last_name,u.username,u.email,u.date_joined,u.user_group,ug.is_admin from users u
		inner join user_groups ug on u.user_group = ug.id
		where
		((u.email = '$user' and ug.email_login = 1) or
		 (u.username = '$user' and ug.username_login = 1)) and
u.password = MD5('{$pass}') and
u.is_active = 1";
else

	$sql = "SELECT u.id,u.first_name,u.last_name,u.username,u.email,u.date_joined,u.user_group,ug.is_admin from users u
inner join user_groups ug on u.user_group = ug.id
where
((u.email = '$user' and ug.email_login = 1) or
 (u.username = '$user' and ug.username_login = 1)) and
u.password = MD5('{$pass}') and
u.is_active = 1 and
ug.is_admin = 0";


$res = mysql_query($sql);
if(!$res)
	$this->debug->dbErrors($sql);

if($res && mysql_num_rows($res)>0){
			//welcome user
	$user = mysql_fetch_object($res);
	$_SESSION['user'] = $user;
			// Allow admin file upload
	$_SESSION['KCFINDER'] = array();
	$_SESSION['KCFINDER']['disabled'] = FALSE;
	$_SESSION['KCFINDER']['uploadURL'] = $this->settings->ck_upload_url;
	$_SESSION['KCFINDER']['uploadDir'] = $this->settings->ck_upload_dir;



			//die(print_r($_SESSION));
	return TRUE;
}else
return FALSE;
}

	/*
	 * Chama a função getAdditional e faz append aos resultados já existentes no sistema
	 */

	function find($id, $additional = 0){
		$sql = "select * from users u
		inner join group";

		$res = mysql_query($sql);
		if(!$res)
			$this->debug->dbErrors($sql);
	}

	/*
	 * Chama a função getAdditional e faz append aos resultados já existentes no sistema
	 */

	function findAll($additional = 0){

		$res = mysql_query($sql);
		if(!$res)
			$this->debug->dbErrors($sql);
	}

	/*
	 * Recebe um array, percorre o mesmo e faz upsert. Chama a função saveAdditional
	 */
	function save($values,$additional = 0){

		$res = mysql_query($sql);
		if(!$res)
			$this->debug->dbErrors($sql);
	}


	function saveAdditional($values){
		$res = mysql_query($sql);
		if(!$res)
			$this->debug->dbErrors($sql);
	}

	/*
	 * Verifica se existe info adicional na tabela group_additional_info. Caso exista fazer select
	 * de todos os campos pertencentes ao grupo, fazer select da tabela user_group_id_aditional_info
	 * devolver array contendo todos os campos em formato:
	 *
	 * Array(	[email] => valor,
	 * 			[nome_do_campo] => valor
	 * 			etc...
	 * 		)
	 */

	function getAdditional($mode = '',$group = ''){
		$res = mysql_query($sql);
		if(!$res)
			$this->debug->dbErrors($sql);
	}

}

?>