<?php
require_once('Core.php');
date_default_timezone_set("Europe/Lisbon");

//TODO: Start the session or include core
$core = new Core('bo');

$i = 1;
while( $i < 101 ) {
	$query = "INSERT INTO `subscribers` (`email`) VALUES ('aaaa".$i."@bright.pt')";
	mysql_query($query) or die( mysql_error() );
	$i++;
}

$core->__destruct();

?>
