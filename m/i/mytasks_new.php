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
                  <div class="header_font_m"><a href="mytasks.php" class="button_small">My Tasks</a>  &raquo; New Tasks</div>
                </div>
          </div>         
          <div style="padding:0px 2px" >
<?php
$sql_mytasks="SELECT idwftasks,wftasks.usrrole_idusrrole,wftasks.tktin_idtktin,wftasks.tasksubject,wftasks.timeinactual,wftasks.timedeadline,wftasks.tktin_idtktin,wftasks.wftskstatustypes_idwftskstatustypes,wftasks.timeactiontaken,usrrole.usrrolename as sender_role,sender_idusrrole,TIMESTAMPDIFF(MINUTE, NOW(), wftasks.timedeadline) AS time_to_deadline ,usrac.utitle,usrac.lname,usrac.fname,tktin.refnumber,tktin.senderphone,tktin.sendername,wftasks.wfactorsgroup_idwfactorsgroup,
tktin.voucher_number,tktcategoryname,tktin.timereported,wftasks.taskdesc,sender_idusrrole,
(SELECT wftasks_batch.batch_no_verbose FROM wftasks_batch
INNER JOIN tktin ON wftasks_batch.idwftasks_batch=tktin.wftasks_batch_idwftasks_batch 
WHERE tktin.idtktinPK=tktin_idtktin) as batch_number_verb
FROM wftasks 
INNER JOIN usrrole ON wftasks.sender_idusrrole=usrrole.idusrrole 
INNER JOIN usrac ON wftasks.sender_idusrac=usrac.idusrac 
INNER JOIN tktin ON wftasks.tktin_idtktin=tktin.idtktinPK 
INNER JOIN tktcategory ON tktin.tktcategory_idtktcategory=tktcategory.idtktcategory
WHERE wftasks.usrrole_idusrrole=".$_SESSION['WkAtToMrPa_iduserrole']." 
AND wftskstatustypes_idwftskstatustypes=0 AND wftskstatusglobal_idwftskstatusglobal=1 
GROUP BY wftasks.tktin_idtktin ORDER BY tktin.timereported DESC ";
$res_mytasks=mysql_query($sql_mytasks);
$fet_mytasks=mysql_fetch_array($res_mytasks);
$num_mytasks=mysql_num_rows($res_mytasks);
?>          
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
            <?php
			if ($num_mytasks>0)
				{ ?>
      			<tr>
                    <td class="tbl_h" width="25%">Ticket No</td>
                    <td class="tbl_h" width="48%" style="padding-left:20px;">Details</td>
                    <td class="tbl_h" width="27%" style="padding-left:40px;">Reported by</td>
                </tr>
                <tr>
	            	<td colspan="3">	
                <?php 
				do {?>
            		<a href="mytasks_view.php?task=<?php echo $fet_mytasks['idwftasks'];?>&tw=new_tasks">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">                    	
                    	<tr>
                			<td class="tbl_data" width="25%">                
                            <div><?php echo $fet_mytasks['refnumber'];?></div>
                            <div><small><?php echo $fet_mytasks['tktcategoryname'];?></small></div>
                			</td>
                            <td class="tbl_data" width="48%">
                            <div><?php echo $fet_mytasks['taskdesc'];?></div>
                            <?php 
								//get the name of the sender
								$sql_sender="SELECT utitle,fname,lname FROM usrac WHERE usrrole_idusrrole=".$fet_mytasks['sender_idusrrole']." LIMIT 1";
								$res_sender=mysql_query($sql_sender);
								$fet_sender=mysql_fetch_array($res_sender);
								$num_sender=mysql_num_rows($res_sender);
								
								if($num_sender>0)
									{?> <div><small>From: <?php echo $fet_sender['utitle']." ".$fet_sender['fname']." ".$fet_sender['lname'];?></small></div><?php } ?>
                            </td >
                            <td class="tbl_data" width="27%">
                            <div><?php echo $fet_mytasks['senderphone'];?></div>
                            <div><?php echo $fet_mytasks['sendername'];?></div>
                            </td >
                        </tr>
            		</table>
            		</a>
            		<?php
					} while ($fet_mytasks=mysql_fetch_array($res_mytasks)); ?>
                </td>
               </tr>
				<?php 	
				} else { //if num >
			?>
            <tr>
                <td class="tbl_h" width="25%">Ticket No</td>
                <td class="tbl_h" width="48%" style="padding-left:0px;">Details</td>
                <td class="tbl_h" width="27%" style="padding-left:0px;">Reported by</td>
            </tr>
            <tr>
            	<td colspan="3">
                <div class="msg_warning_small">No New Tasks</div>
                </td>
            </tr>
            <?php
				}
			?>	
		</table>
          </div>
            
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
