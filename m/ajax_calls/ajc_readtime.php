<?php
require_once('../required/config.php');
require_once('../../Connections/connSystem.php'); 
mysql_select_db($database_connSystem, $connSystem); //connect to DB

require_once('../required/check_user_mobile.php');

$reasonid=intval($_GET['reason']);

if (isset($reasonid))
	{

//	echo $reasonid;

	$res_expiry=mysql_query("SELECT iditinerary_reset_time,reset_days,reset_lbl 
	FROM itinerary_reset_time 
	INNER JOIN link_reason_expirytime ON itinerary_reset_time.iditinerary_reset_time=link_reason_expirytime.itinerary_reset_time_iditinerary_reset_time 
	WHERE link_reason_expirytime.itinerary_reset_reason_ididitinerary_reset_reason=".$reasonid."
	ORDER BY iditinerary_reset_time ASC");

	$fet_expiry=mysql_fetch_array($res_expiry);
	$num_expiry=mysql_num_rows($res_expiry);
	
	if ($num_expiry>0)	
		{
?>
<select name="expiry_re">
<?php
	do {
	echo "<option ";
	echo " value=".$fet_expiry['reset_days'].">".$fet_expiry['reset_lbl']."</option>";
	} while ($fet_expiry=mysql_fetch_array($res_expiry));
	?>
</select> 
<?php
		} else {
		echo "--Invalid Selecton--";
		}
	} //if isset $_GET
?>