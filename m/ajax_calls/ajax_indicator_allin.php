<?php
require_once('../required/genconfig.php');

//autheticate this user
require_once('../required/check_user_lite.php');

$sql_mytasksc="SELECT DISTINCT count(tktin_idtktin) AS allin FROM wftasks WHERE wftasks.usrrole_idusrrole=".$_SESSION['WkAtToMrPa_iduserrole']."  AND wftasks.tktin_idtktin>0 AND ((wftskstatustypes_idwftskstatustypes=0 AND wftskstatusglobal_idwftskstatusglobal=1 ) OR 
(wftskstatustypes_idwftskstatustypes=6 AND wftskstatusglobal_idwftskstatusglobal=2) ) ";
$res_mytasksc=mysql_query($sql_mytasksc);
$fet_mytasksc=mysql_fetch_array($res_mytasksc);

if ($fet_mytasksc['allin'] > 0)
	{
	echo "<span class=\"box_count\">".$fet_mytasksc['allin']."</span>";
	}
mysql_free_result($res_mytasksc);	
?>