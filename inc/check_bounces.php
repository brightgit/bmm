<?php
function error_email($msg)
{
	mail("hugo.silva@bright.pt", "Erro Bright Mail Module::Bounces", $msg);
	die($msg);
}



require_once('Core.php');
date_default_timezone_set("Europe/Lisbon");
//TODO: Start the session or include core
$core = new Core('bo');

function delete_subscriber($email)
{
	$query = "UPDATE `subscribers` set `is_active`= 0 where `email` = '" . $email . "'";
	//echo "Queria apagar";
	mysql_query($query) or die( mysql_error() );
}

function log_this($msg)
{
	$query = "INSERT INTO `logs` ( `message` ) VALUES ( '" . $msg . "' )";
}

function add_bounce_to_count($type, $email)
{
	//Devolve o número de bounces que ficou.
	$query = "SELECT * FROM `subscribers` WHERE `email` = '" . $email . "'";
	$res = mysql_query($query) or die(mysql_error());
	
	if (mysql_num_rows($res) == 0) {
		log_this("Não encontrou o email " . $email);
	}
	
	$row = mysql_fetch_object($res);
	
	
	
	
	if ($type == 'soft') {
		$row->soft_bounces_count++;
		$soft_bounces_count = $row->soft_bounces_count;
		$query              = "UPDATE `subscribers` set `soft_bounces_count` = '" . $soft_bounces_count . "', `last_bounce_added` = '" . date("Y-m-d H:i:s") . "' where `email` = '" . $email . "' AND `last_bounce_added` <=  ( CURDATE() - INTERVAL 1 DAY ) ";
		mysql_query($query) or die(mysql_error());
	} else {
		$query_n = "SELECT * FROM newsletters order by id desc limit 1";
		$res_n = mysql_query( $query_n ) or die(mysql_error());
		$n = mysql_fetch_object($res_n);

		if ( empty( $row->bounced_on ) ) {
			$bounced_on = $n->id;
		}else{
			$bounced_on = $bounced_on.','.$n->id;
		}

		$row->hard_bounces_count++;
		$hard_bounces_count = $row->hard_bounces_count;
		$query              = "UPDATE `subscribers` set `hard_bounces_count` = '" . $hard_bounces_count . "', `bounced_on` = '" . $bounced_on . "', `last_bounce_added` = '" . date("Y-m-d H:i:s") . "'  where `email` = '" . $email . "' AND `last_bounce_added` <=  ( CURDATE() - INTERVAL 1 DAY ) ";
		mysql_query($query) or die(mysql_error());
	}
	$num       = new stdClass();
	$num->soft = $row->soft_bounces_count;
	$num->hard = $row->hard_bounces_count;
}


#Settings
#API KEY para este dominio
$api_key = $core->settings->sender_api_key;


$mail_box  = '{mail.' . $core->settings->sender_domain . ':143/imap4/novalidate-cert/debug}INBOX'; //imap example
$mail_user = $core->settings->return_path; //mail username
$mail_pass = $core->settings->return_path_password; //mail username


$delete_num                = $core->settings->remove_bounces_count;
$remove_bounces            = $core->settings->remove_bounces;
$unsubscribe_automatically = $core->settings->unsubscribe_automatically;


//var_dump($core->settings);

$conn = imap_open($mail_box, $mail_user, $mail_pass);

if (!$conn) {
	error_email(imap_last_error());
}

$num_msgs = imap_num_msg($conn);
//echo $num_msgs;
//Quero que leia no maximo 10
if( $num_msgs >= 200 ) {
	$num_msgs = 200;
}

require_once('bounce_driver.class.php');

$bouncehandler    = new Bouncehandler();
# get the failures
$email_addresses  = array();
$delete_addresses = array();
for ($n = 1; $n <= $num_msgs; $n++) {
	$bounce = @imap_fetchheader($conn, $n) . @imap_body($conn, $n); //entire message

	//evitar fazer toda a iteração caso não sejam detectados bounces
	if (!$bounce) $errors += 1;
	if($errors > 3) exit;

	$multiArray = $bouncehandler->get_the_facts($bounce);
	
	
	if (!empty($multiArray[0]['action']) && !empty($multiArray[0]['status']) && !empty($multiArray[0]['recipient'])) {
		//echo "<hr />" . $multiArray[0]["status"] . '<br />';
		//var_dump($multiArray[0]["status"]);
		$status_a = explode(".", $multiArray[0]["status"]);
		switch ($status_a[0]) {
			case '1': //Wrong email adress
				$num = add_bounce_to_count("hard", trim($multiArray[0]["recipient"]));
				break;
			case '2': //Mailbox full/not accpeting
			case '0': //Undefined, add to soft
			case '3': //Undefined, add to soft
			case '4': //Undefined, add to soft
				$num = add_bounce_to_count("soft", trim($multiArray[0]["recipient"]));
				break;
			default:
				$num = add_bounce_to_count("hard", trim($multiArray[0]["recipient"]));
				break;
		}
		
		//Always delete message.
		echo '<hr />';
		echo "Message deleted";
		imap_delete( $conn, $n );
		
		/*
		if ($multiArray[0]['action']=='failed') {
		
		$email_addresses[$multiArray[0]['recipient']]++; //increment number of failures
		$delete_addresses[$multiArray[0]['recipient']][] = $n; //add message to delete array
		
		} //if delivery failed */
		
	} //if passed parsing as bounce
	
} //for loop

/*
# process the failures
foreach ($email_addresses as $key => $value) { //trim($key) is email address, $value is number of failures
//Adiciona bounce
$num = add_bounce_to_count("hard", trim($key) );
if( $num >= $delete ) {
//delete_subscriber( trim($key) );
}
/*
do whatever you need to do here, e.g. unsubscribe email address
*a/
# mark for deletion
//foreach ($delete_addresses[$key] as $delnum) imap_delete($conn, $delnum);
} //foreach
*/

# delete messages
imap_expunge($conn);

# close
imap_close($conn);


if ($core->settings->remove_bounces && $core->settings->remove_bounces_count > 0) {
	$query = "update subscribers set is_active = 0 where hard_bounces_count >= " . $core->settings->remove_bounces_count;
	mysql_query($query) or die(mysql_error());
}

$core->__destruct();

?>