<?php
require_once('../required/config.php');
require_once('../../Connections/connSystem.php'); 
mysql_select_db($database_connSystem, $connSystem); //connect to DB

require_once('../required/check_user_mobile.php');

$q = intval($_GET['q']);

$sql = "SELECT regnam FROM acc_list WHERE accnum='".$q."' ";
$res = mysql_query($sql);
$num = mysql_num_rows($res);
$fet = mysql_fetch_array($res);

if ($num > 0)
	{
	echo "<span style=\"color:#009900\">".$fet['regnam']."</span>";
	} else {
	echo "<span style=\"color:#ff0000\">N/A</span>";
	}
?>
