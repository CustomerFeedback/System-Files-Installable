<?php
require_once('../required/genconfig.php');

//autheticate this user
require_once('../required/check_user.php');

$q = trim(mysql_escape_string($_GET['q']));

//set the rules for the query below depending on the job level of the user
//$min_level=($_SESSION['WkAtToMrPa_joblevel']+1);
$max_level=abs($_SESSION['WkAtToMrPa_joblevel']-2);

if (!$q) return;

if (strlen($q) > 0)
	{
/*
$sql = "SELECT utitle,fname,lname,usrname,usrrolename,usrteamzone.region_pref FROM usrac
INNER JOIN usrrole ON usrac.usrrole_idusrrole=usrrole.idusrrole 
INNER JOIN usrteamzone ON usrrole.usrteamzone_idusrteamzone=usrteamzone.idusrteamzone
WHERE ( usrrolename LIKE '%".$q."%' OR fname LIKE '%".$q."%' OR lname LIKE '%".$q."%' OR usrname LIKE '%".$q."%' ) 
AND joblevel > ".$max_level."
AND idusrac!=".$_SESSION['WkAtToMrPa_idacname']."
AND usrac.acstatus=1 
".$exempt."
LIMIT 10 ";
*/
$sql = "SELECT utitle,fname,lname,usrname,usrrolename,usrteamzone.region_pref FROM usrac
INNER JOIN usrrole ON usrac.usrrole_idusrrole=usrrole.idusrrole 
INNER JOIN usrteamzone ON usrrole.usrteamzone_idusrteamzone=usrteamzone.idusrteamzone
WHERE ( usrrolename LIKE '%".$q."%' OR fname LIKE '%".$q."%' OR lname LIKE '%".$q."%' OR usrname LIKE '%".$q."%' ) 
AND idusrac!=".$_SESSION['WkAtToMrPa_idacname']." 
AND usrac.acstatus=1 
LIMIT 80 ";
//echo $sql;
$rsd=mysql_query($sql);
$fet=mysql_fetch_array($rsd);
$num=mysql_num_rows($rsd);

if ($num >0)
	{
	do {
	$lname = mysql_escape_string($fet['usrrolename']).", ".mysql_escape_string($fet['utitle'])." ".mysql_escape_string($fet['fname'])." ".mysql_escape_string($fet['lname'])." [".mysql_escape_string($fet['region_pref'])."]";
	echo "$lname\n";
		} while ($fet=mysql_fetch_array($rsd));
	}
}
?>