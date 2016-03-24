<?php
$reasonid=intval($_GET['reason']);

if (isset($reasonid))
	{

if ($reasonid!=4)
	{
	$menu_status=" disabled=\"disabled\" ";
	} else if ($reasonid==4) {
	$menu_status=" ";
	}	

echo $reasonid;

$res_expiry=mysql_query("SELECT reset_days,reset_lbl FROM itinerary_reset_time ORDER BY iditinerary_reset_time ASC");
$fet_expiry=mysql_fetch_array($res_expiry);
?>
<select name="expiry_re">
<?php
	do {
	echo "<option ";
	echo " value=".$fet_expiry['reset_days'].">".$fet_expiry['reset_lbl']."</option>";
	} while ($fet_expiry=mysql_fetch_array($res_expiry));
	
	?>
</select> 

	
echo "<select name=\"expiry_re\">
<option value=1>1 Day After Download Date</option>
<option value=2>2 Days After Download Date</option>
<option value=3>3 Days After Download Date</option>
<option value=\"26\" ".$menu_status." >26th of this Cycle</option>
</select>";
	
	}

?>