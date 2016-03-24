<?php
require_once('../../Connections/connSystem.php'); 
mysql_select_db($database_connSystem, $connSystem); //connect to DB

if ( (!isset($_SESSION['WkAtToMrPa_logstatus'])) || ($_SESSION['WkAtToMrPa_logstatus']!="IS_LOGGED_IN") || (!isset($_SESSION['WkAtToMrPa_idacname'])) || (!isset($_SESSION['WkAtToMrPa_iduserrole'])) || (!isset($_SESSION['WkAtToMrPa_idacteam'])) || (!isset($_SESSION['WkAtToMrPa_iduserprofile'])) )
	{
	$_SESSION['autologout']=1;
	echo "<div style=\"color:#ff0000;font-family:arial;font-size:12px;font-weight:bold\">You seem to have been logged out. Please <a href=\"../i/logout.php\"> re-log in</a></div>";
	exit;
	if ( (isset($_SESSION['autologout'])) && ($_SESSION['autologout']==1) && (!isset($_SESSION['autologoutOK'])) )
		{
		exit;
		}
	}

//check login details
$sql_login="SELECT idusrac,usrname,currentsess FROM usrac WHERE idusrac=".$_SESSION['WkAtToMrPa_idacname']." AND usrname='".$_SESSION['WkAtToMrPa_acname']."' AND usrpass='".$_SESSION['WkAtToMrPa_acpass']."' LIMIT 1";
$res_login=mysql_query($sql_login);
$num_login=mysql_num_rows($res_login);
$fet_login=mysql_fetch_array($res_login);

mysql_free_result($res_login);	

if ($num_login<1)
	{
	echo "<div style=\"color:#ff0000;font-family:arial;font-size:12px;font-weight:bold\">Please <a href=\"../i/logout.php\"> log in</a> to your account</div>";
	exit;
	}
	
//check simultaneous access
if ($fet_login['currentsess']!=session_id())
	{
	echo "<div style=\"color:#ff0000;font-family:arial;font-size:12px;font-weight:bold\">You have been logged out because you or someone else has just logged onto the same account from another computer or device.<br> <a href=\"../i/logout.php\"> re-log in</a></div>";
	exit;
	}	

?>