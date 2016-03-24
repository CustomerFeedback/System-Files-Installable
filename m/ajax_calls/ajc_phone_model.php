<?php
require_once('../required/config.php');
require_once('../../Connections/connSystem.php'); 
mysql_select_db($database_connSystem, $connSystem); //connect to DB

require_once('../required/check_user_mobile.php');

$makeid=intval($_GET['phone_make']);

$query="SELECT  iddevice_model,device_modellbl FROM device_models 
WHERE device_makes_iddevicemake=".$makeid." ORDER BY device_modellbl ASC";
$result=mysql_query($query);
//echo $query;
?>
<select name="phone_model">
<option value=""> --- </option>
<?php while($row=mysql_fetch_array($result)) { ?>
<option value=<?php echo $row['iddevice_model']; ?>><?php echo $row['device_modellbl']; ?></option>
<?php } ?>
</select>