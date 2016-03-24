<?php
require_once('../required/config.php');
require_once('../../Connections/connSystem.php'); 
mysql_select_db($database_connSystem, $connSystem); //connect to DB

require_once('../required/check_user_mobile.php');

$tkttype=intval($_GET['tkttype']);

$query="SELECT DISTINCT idtktcategory,tktcategoryname,wfproc.wfstatus FROM tktcategory 
INNER JOIN link_tktcategory_tktype ON tktcategory.idtktcategory=link_tktcategory_tktype.tktcategory_idtktcategory
INNER JOIN link_tskcategory_wfproc ON link_tktcategory_tktype.tktcategory_idtktcategory=link_tskcategory_wfproc.tktcategory_idtktcategory 
LEFT JOIN wfproc ON link_tskcategory_wfproc.wfproc_idwfproc=wfproc.idwfproc
WHERE link_tskcategory_wfproc.usrteam_idusrteam=".$_SESSION['WkAtToMrPa_idacteam']." AND link_tktcategory_tktype.tkttype_idtkttype=".$tkttype." ORDER BY tktcategoryname ASC";
$result=mysql_query($query);
$numrec=mysql_num_rows($result);
//echo $query;

if ($numrec>0)
	{
?>
<select name="tktcat"  onChange="getwkflow(this.value)">
<option value=""> --- </option>
<?php while($row=mysql_fetch_array($result)) { ?>
<option  <?php if ($row['wfstatus']!=1) { echo "disabled=\"disabled\""; } ?> <?php  if ( (isset($_POST['tktcat'])) && ($_POST['tktcat']==$row['idtktcategory']) ) { echo "selected=\"selected\""; } ?> value=<?php echo $row['idtktcategory']; ?>><?php echo $row['tktcategoryname']; ?></option>
<?php } ?>
</select>
<?php 
} else { 
	echo "<div class=\"msg_warning_small\">Category Not Configured to Workflow</div>";
}
?>