<?php
require_once('../required/genconfig.php');

//autheticate this user
require_once('../required/check_user_lite.php');

$sql_myundone="SELECT DISTINCT count(tktin_idtktin) as undone FROM wftasks WHERE wftasks.usrrole_idusrrole=".$_SESSION['WkAtToMrPa_iduserrole']." AND wftasks.usrac_idusrac=".$_SESSION['WkAtToMrPa_idacname']."  AND wftasks.tktin_idtktin>0 AND wftskstatustypes_idwftskstatustypes=0 AND wftskstatusglobal_idwftskstatusglobal=1 ";
$res_myundone=mysql_query($sql_myundone);
$fet_myundone=mysql_fetch_array($res_myundone);

if ($fet_myundone['undone'] > 0)
	{
	echo "<span class=\"box_count\">".$fet_myundone['undone']."</span>";
	}
mysql_free_result($res_myundone);	
?>