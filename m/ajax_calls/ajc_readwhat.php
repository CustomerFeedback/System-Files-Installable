<?php
//initialize connection
require_once('../../Connections/connSystem.php');
mysql_select_db($database_connSystem, $connSystem);

//autheticate this user
require_once('../required/check_user.php');

$reasonid=intval($_GET['reason']);

if (isset($reasonid))
	{

	//echo $reasonid;

	$res_expiry=mysql_query("SELECT iditinerary_reset_cat,reset_cat,reset_cat_desc 
	FROM itinerary_reset_cat 
	INNER JOIN link_reason_expirycat ON itinerary_reset_cat.iditinerary_reset_cat=link_reason_expirycat.itinerary_reset_cat_iditinerary_reset_cat 
	WHERE link_reason_expirycat.itinerary_reset_reason_ididitinerary_reset_reason=".$reasonid."
	ORDER BY iditinerary_reset_cat ASC");
	
	$fet_expiry=mysql_fetch_array($res_expiry);
	$num_expiry=mysql_num_rows($res_expiry);
	
	if ($num_expiry>0)
		{
?>
<select name="filter_re">
<?php
	do {
	echo "<option ";
	echo " value=".$fet_expiry['iditinerary_reset_cat']." title=\"".$fet_expiry['reset_cat_desc']."\">
	".$fet_expiry['reset_cat']."</option>";
	} while ($fet_expiry=mysql_fetch_array($res_expiry));
	
	?>
</select> 

<?php
		} else {
		echo "--Invalid Selecton--";
		}
	} //if isset $_GET
?>