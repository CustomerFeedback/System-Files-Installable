<?php
require_once('../required/config.php');
require_once('../../Connections/connSystem.php'); 
mysql_select_db($database_connSystem, $connSystem); //connect to DB

require_once('../required/check_user_mobile.php');

$regionid=intval($_GET['region']);

$query="SELECT idusrrole,usrrolename,
(SELECT fname FROM usrac WHERE usrrole_idusrrole=idusrrole) as firstname,
(SELECT lname FROM usrac WHERE usrrole_idusrrole=idusrrole) as lastname,
(SELECT iddevice_user FROM device_user WHERE usrrole_idusrrole=idusrrole) as already_assigned
FROM usrrole
WHERE usrteamzone_idusrteamzone=".$regionid." ORDER BY usrrolename ASC";
$result=mysql_query($query);
//echo $query;
?>
<select name="usrto">
<option value=""> --- </option>
<?php while($row=mysql_fetch_array($result)) { ?>
<option <?php if ($row['already_assigned'] > 0){  ?> style="background-color:#FF0000" <?php } else { ?> style="background-color:#00FF33" <?php }?> title="<?php echo $row['firstname']; ?> <?php echo $row['lastname']; ?>" value=<?php echo $row['idusrrole']; ?>><?php echo $row['usrrolename']; ?></option>
<?php } ?>
</select>