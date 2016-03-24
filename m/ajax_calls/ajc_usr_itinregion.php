<?php
require_once('../required/config.php');
require_once('../../Connections/connSystem.php'); 
mysql_select_db($database_connSystem, $connSystem); //connect to DB

require_once('../required/check_user_mobile.php');

$itineraryid=intval($_GET['itinerary']);

$query="SELECT DISTINCT idusrteamzone,userteamzonename
FROM itinerary_routes
INNER JOIN usrteamzone ON itinerary_routes.usrteamzone_idusrteamzone=usrteamzone.idusrteamzone 	
WHERE iditinerary_routes=".$itineraryid."";
$result=mysql_query($query);
?>
<select name="region" onchange="getzone(this.value);getusrdevice(this.value);">
<option value="0"> --- </option>
<?php 
while($row=mysql_fetch_array($result)) { ?>
<option  value=<?php echo $row['idusrteamzone']; ?>><?php echo $row['userteamzonename']; ?></option>
<?php 
}
?>
</select>