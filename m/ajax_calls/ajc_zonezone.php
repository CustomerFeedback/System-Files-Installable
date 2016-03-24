<?php
require_once('../required/genconfig.php');

//initialize connection
require_once('../../Connections/connSystem.php');
mysql_select_db($database_connSystem, $connSystem);

$zone=intval($_GET['zone']);

$query="SELECT idusrteamsubzone,usrteamsubzonelbl FROM usrteamsubzone WHERE usrteamzone_idusrteamzone=$zone ORDER BY usrteamsubzonelbl ASC";
$result=mysql_query($query);
//echo $query;
?>
<select name="zonezone">
<option value=""> --- </option>
<?php while($row=mysql_fetch_array($result)) { ?>
<option value=<?php echo $row['idusrteamsubzone']; ?>><?php echo $row['usrteamsubzonelbl']; ?></option>
<?php } ?>
</select>