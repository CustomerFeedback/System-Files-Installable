<?php
require_once('../required/config.php');
require_once('../../Connections/connSystem.php'); 
mysql_select_db($database_connSystem, $connSystem); //connect to DB

require_once('../required/check_user_mobile.php');

$region=intval($_GET['zone']);

$query="SELECT idusrteamsubzone,usrteamsubzonelbl FROM usrteamsubzone WHERE usrteamzone_idusrteamzone=".$region." ORDER BY usrteamsubzonelbl ASC";

/*$query="SELECT idusrrole,usrrolename FROM usrrole 
WHERE usrteamzone_idusrteamzone=$zone ORDER BY usrrolename ASC";
*/
$result=mysql_query($query);
$num_role=mysql_num_rows($result);
//echo $query;
if ($num_role < 1)
	{
	echo "<div class=\"msg_warning_small\" style=\"padding:0px; margin:0px\">No Zone for this Region</div>";
	} else {

	echo "<select name=\"subzone\" style=\"padding:0px; margin:0px\" >";
	//echo "<option value=\"NULL\">---</option>";
			while($row=mysql_fetch_array($result)) {
				echo "<option value=\"".$row['idusrteamsubzone']."\" title=\"".$row['usrteamsubzonelbl']."\" ";
			//disable selection if the role has already been selected
			
					echo ">".$row['usrteamsubzonelbl']."</option>";
				} 
			echo "</select>";
		}
?>

