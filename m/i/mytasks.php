<?php
require_once('../required/config.php');
require_once('../../Connections/connSystem.php'); 

mysql_select_db($database_connSystem, $connSystem); //connect to DB

require_once('../required/check_user_mobile.php');

//check if this user has some tasks
$sql_myundone="SELECT DISTINCT count(tktin_idtktin) as undone FROM wftasks WHERE wftasks.usrrole_idusrrole=".$_SESSION['WkAtToMrPa_iduserrole']." AND wftasks.usrac_idusrac=".$_SESSION['WkAtToMrPa_idacname']."  AND wftasks.tktin_idtktin>0 AND wftskstatustypes_idwftskstatustypes=0 AND wftskstatusglobal_idwftskstatusglobal=1 ";
$res_myundone=mysql_query($sql_myundone);
$fet_myundone=mysql_fetch_array($res_myundone);


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8"> 
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<title><?php echo $app_title;?></title>
<link rel="stylesheet" href="../m_assets/main.css" />
</head>
<body>
	<div>
    	<div>
        <?php require_once('../m_header_2.php');?>
        </div>
               
        <div>
       	  <div>
          <a name="bottom"></a>
            	<div class="section">
                  <div class="header_font_m">&raquo; My Tasks</div>
                </div>
          </div>
            
            <div class="menu"><a href="mytasks_new.php" style="color:#FFFFFF; text-decoration:none">
            <div><img src="../m_assets/menu_arrow_n.png" width="16" height="19" border="0" align="absmiddle" /> New 
            <?php
			if ($fet_myundone['undone'] > 0)
				{
				echo "<span class=\"box_count\">".$fet_myundone['undone']."</span>";
				}
			?>
            </div>
            </a></div>
       	  <div class="menu"><a href="mytasks_inprog.php" style="color:#FFFFFF; text-decoration:none">
   	      <div><img src="../m_assets/menu_arrow_n.png" width="16" height="19" border="0" align="absmiddle" /> In Progress 
          <?php
		  $sql_inprog="SELECT DISTINCT count(tktin_idtktin) as inprog FROM wftasks WHERE wftasks.usrrole_idusrrole=".$_SESSION['WkAtToMrPa_iduserrole']." AND wftskstatustypes_idwftskstatustypes=6 AND wftskstatusglobal_idwftskstatusglobal=2";
			$res_inprog=mysql_query($sql_inprog);
			$fet_inprog=mysql_fetch_array($res_inprog);
			
			if ($fet_inprog['inprog'] > 0)
				{
				echo "<span class=\"box_count\">".$fet_inprog['inprog']."</span>";
				}
			mysql_free_result($res_inprog);	
		  ?>
          </div>
       	  </a></div>
          <div class="menu"><a href="mytasks_overdue.php" style="color:#FFFFFF; text-decoration:none">
   	      <div><img src="../m_assets/menu_arrow_n.png" width="16" height="19" border="0" align="absmiddle" /> Overdue 
          <?php
		  $sql_overdue="SELECT count(tktin_idtktin) as overdue FROM wftasks 
			WHERE wftasks.usrrole_idusrrole=".$_SESSION['WkAtToMrPa_iduserrole']." AND wftasks.usrac_idusrac=".$_SESSION['WkAtToMrPa_idacname']." AND wftasks.tktin_idtktin>0 AND wftskstatustypes_idwftskstatustypes=0 AND wftasks.timedeadline<='".$timenowis."'";
			$res_overdue=mysql_query($sql_overdue);
			$fet_overdue=mysql_fetch_array($res_overdue);
			
			if ($fet_overdue['overdue'] > 0)
				{
				echo "<span class=\"box_count\">".$fet_overdue['overdue']."</span>";
				}
			mysql_free_result($res_overdue);
		  ?>
          </div>
       	  </a></div>
          
          <div class="menuo"><a href="index.php" style="text-decoration:none">
   	      <div><img src="../m_assets/icon_search_on.gif" width="16" height="19" border="0" align="absmiddle" /> Find Ticket</div>
       	  </a></div>
          <div class="menuo"><a href="logout.php" style="text-decoration:none">
   	      <div><img src="../m_assets/icon_login_n.gif" width="16" height="19" border="0" align="absmiddle" /> Log Out</div>
       	  </a></div>
            
    </div>
</div>
</body>
</html>
