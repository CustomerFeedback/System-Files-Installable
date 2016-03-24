<?php
require_once('../required/config.php');
require_once('../../Connections/connSystem.php'); 
mysql_select_db($database_connSystem, $connSystem); //connect to DB

require_once('../required/check_user_mobile.php');
//echo "This is Device : ";
//echo $_REQUEST['deviceid'];
$device_no=trim($_REQUEST['deviceid']);

if ($device_no>0)
	{
//get the total number of itineraries assigned to this phone
$res_assigned=mysql_query("SELECT count(*) as no FROM itinerary_meta_count 
WHERE itinerary_meta_iditinerary_meta=".$_SESSION['bcycle']." AND device_iddevice=".$device_no."");
$fet_assigned=mysql_fetch_array($res_assigned);

//total number of itineraries downloaded
$res_downloaded=mysql_query("SELECT count(*) as no FROM itinerary_meta_count 
WHERE itinerary_meta_iditinerary_meta=".$_SESSION['bcycle']." 
AND device_iddevice=".$device_no."
AND itinerary_meta_count_status_iditinerary_meta_count_status=3");
$fet_downloaded=mysql_fetch_array($res_downloaded);

//still currently on phone
$res_onphone=mysql_query("SELECT count(*) as no FROM itinerary_meta_count 
WHERE itinerary_meta_iditinerary_meta=".$_SESSION['bcycle']." 
AND device_iddevice=".$device_no."
AND itinerary_meta_count_status_iditinerary_meta_count_status=3 
AND expires_on>='".$timenowis."'");
$fet_onphone=mysql_fetch_array($res_onphone);
	
echo "<table border=0 cellpadding=2 cellspacing=0></tr>";
echo "<tr><td colspan=6 class=\"txt_small\" style=\"padding:0px;\"><strong>Overview of Itineraries on Device ".$device_no." this Cycle</strong></td></tr>";
echo "<td width=80 align=\"center\" style=\"border:1px solid #999999;border-radius:2px;background-color:#f7f7f7\"><div class=\"txt_small\"># Assigned</div><div>".$fet_assigned['no']." <input value=\"".$fet_assigned['no']."\" name=\"o_assigned\" type=\"hidden\"></div></td>";
echo "<td width=\"10\"></td>";
echo "<td width=80 align=\"center\" style=\"border:1px solid #999999;border-radius:2px;background-color:#f7f7f7\"><div class=\"txt_small\"># Downloaded</div><div>".$fet_downloaded['no']." <input value=\"".$fet_downloaded['no']."\" name=\"o_downloaded\" type=\"hidden\"></div></td>";
echo "<td width=\"10\"></td>";
echo "<td width=80 align=\"center\" style=\"border:1px solid #999999;border-radius:2px;background-color:#f7f7f7\"><div class=\"txt_small\"># on Phone Now</div><div>".$fet_onphone['no']." <input value=\"".$fet_onphone['no']."\" name=\"o_onphone\" type=\"hidden\"></div></td>";
echo "</tr></table>";
	} else {
	echo "<div >---</div>";
	}
?>