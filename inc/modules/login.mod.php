<?php 
/**
* 
*/
class Login
{
	public $view = "login/login";
	
	function __construct()
	{
		$this->load_menu = FALSE;
		if (!empty($_GET["view"])) {
			$this->$_GET["view"]();
		}

	}

	/* Controllers */
	function login() {
		if ( isset($_POST) ) {
			$this->doLogin( $_POST["username"], $_POST["password"] );
			redirect( "admin/index.php?mod=dashboard" );
		}
		$this->load_menu = FALSE;
	}
	function logout() { 
		session_destroy();
		unset($_SESSION);
		redirect( "admin/index.php" );
	}





	function doLogin($user, $pass){
		$sql = "SELECT u.id,u.first_name,u.last_name,u.username,u.email,u.date_joined,u.user_group,ug.is_admin, u.sender_host from users u
			inner join user_groups ug on u.user_group = ug.id
			where
			((u.email = '$user' and ug.email_login = 1) or
			 (u.username = '$user' and ug.username_login = 1)) and
			u.password = MD5('{$pass}') and
			u.is_active = 1";
		$res = mysql_query($sql);

		if($res && mysql_num_rows($res)>0){
					//welcome user
			$user = mysql_fetch_object($res);
			$_SESSION['user'] = $user;
					// Allow admin file upload
			$_SESSION['KCFINDER'] = array();
			$_SESSION['KCFINDER']['disabled'] = FALSE;

			
			if ( !file_exists( base_path("media/".$user->sender_host) )) {
				mkdir( base_path("media/".$user->sender_host).'/', 0777 );
			}
			$_SESSION['KCFINDER']['uploadURL'] = "http://www.".$user->sender_host."/bmm/media/".$user->sender_host;
			$_SESSION['KCFINDER']['uploadDir'] = base_path("media/".$user->sender_host);
			var_dump($_SESSION);
			return TRUE;
		}else{
			return FALSE;
		}
	}


}

 ?>