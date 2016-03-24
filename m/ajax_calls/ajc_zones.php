<?php
require_once('../required/config.php');
require_once('../../Connections/connSystem.php'); 
mysql_select_db($database_connSystem, $connSystem); //connect to DB

require_once('../required/check_user_mobile.php');

$regionid=intval($_GET['region']);

$query="SELECT idusrteamsubzone,usrteamzone_idusrteamzone,usrteamsubzonelbl,
(SELECT usrteamsubzone_idusrteamsubzone FROM itinerary_routes WHERE usrteamsubzone_idusrteamsubzone=idusrteamsubzone AND usrteamzone_idusrteamzone=".$regionid." LIMIT 1 ) as iko
FROM usrteamsubzone
WHERE usrteamzone_idusrteamzone=".$regionid."
ORDER BY usrteamsubzonelbl ASC";
$result=mysql_query($query);
?>
<select name="zone">
<option value=""> --- </option>
<?php
$iko="";
while($row=mysql_fetch_array($result)) { 
$iko.=$row['iko'];
?>
<option <?php if ($row['iko'] < 1) { echo "disabled=\"disabled\""; } ?> value=<?php echo $row['idusrteamsubzone']; ?>><?php echo $row['usrteamsubzonelbl']; ?></option>
<?php 
}
//if ($iko=="") //if not found, then give the option below
//	{
?>
<!--<option value="0" style="color:#009900"> Does Not Apply </option>-->
<?php
//	}
?>
</select>