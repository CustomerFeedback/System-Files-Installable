<?php
require_once('../required/genconfig.php');

//autheticate this user
require_once('../required/check_user_lite.php');

$sql_overdue="SELECT count(tktin_idtktin) as overdue FROM wftasks 
WHERE wftasks.usrrole_idusrrole=".$_SESSION['WkAtToMrPa_iduserrole']." AND sender_idusrrole!=".$_SESSION['WkAtToMrPa_iduserrole']." AND wftasks.usrac_idusrac=".$_SESSION['WkAtToMrPa_idacname']." AND wftasks.tktin_idtktin>0 AND wftskstatustypes_idwftskstatustypes=0 AND wftasks.timedeadline<='".$timenowis."'";
$res_overdue=mysql_query($sql_overdue);
$fet_overdue=mysql_fetch_array($res_overdue);

if ($fet_overdue['overdue'] > 0)
	{
	echo "<span class=\"box_count\">".$fet_overdue['overdue']."</span>";
	}
mysql_free_result($res_overdue);		
?>