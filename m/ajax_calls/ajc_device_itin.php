<?php
require_once('../required/config.php');
require_once('../../Connections/connSystem.php'); 
mysql_select_db($database_connSystem, $connSystem); //connect to DB

require_once('../required/check_user_mobile.php');
$usrroleid=intval($_GET['usrrole']);

if (isset($_GET['idsysmodule']))
	{
	$idsysmodule=intval($_GET['idsysmodule']);
	$qry_ext=" AND device.sysmodule_idsysmodule=".$idsysmodule." ";	
	} else {
	$qry_ext=" ";
	}

$query="SELECT device_iddevice,device_makelbl,device_modellbl
FROM device_user INNER JOIN device ON device_user.device_iddevice=device.iddevice 
INNER JOIN device_makes ON device.device_makes_iddevice_make=device_makes.iddevice_make
INNER JOIN device_models ON device_makes.iddevice_make=device_models.device_makes_iddevicemake
WHERE usrrole_idusrrole=".$usrroleid." ".$qry_ext." ";
$result=mysql_query($query);
$num=mysql_num_rows($result);
//echo $query;
if ($num>0)
	{
?>
<select name="deviceid" style="padding:1px" onChange="getdevicewl(this.value)">
<option value="0">----</option>
<?php
while($row=mysql_fetch_array($result)) { 
?>
<option value=<?php echo $row['device_iddevice']; ?>><?php echo $row['device_makelbl']; ?> <?php echo $row['device_modellbl']; ?> ( Device ID : <?php echo $row['device_iddevice']; ?> )</option>
<?php 
}
?>
</select>
<?php
} else {
echo "<select name=\"deviceid\"><option value=\"0\">--Device Not Mapped--</option></select>";
}
?>