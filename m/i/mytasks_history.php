<?php
require_once('../required/config.php');
require_once('../../Connections/connSystem.php'); 

mysql_select_db($database_connSystem, $connSystem); //connect to DB

require_once('../required/check_user_mobile.php');

//id
if (isset($_GET['task']))
	{
	$_SESSION['wtaskid']=preg_replace('/[^a-z\-_0-9\.:\/]/i','',mysql_real_escape_string(trim($_GET['task'])));
	
	$sql_historyid="SELECT wftasks_idwftasks,wftaskstrac_idwftaskstrac FROM wftasks WHERE idwftasks=".$_SESSION['wtaskid']." LIMIT 1";
	$res_historyid=mysql_query($sql_historyid);
	$fet_historyid=mysql_fetch_array($res_historyid);
	
	$sql_history="SELECT idwftasks,usrrole.usrrolename,usrac.utitle,usrac.lname,wftskstatustypes.idwftskstatustypes,wftskstatustypes.wftskstatustype,wftskupdates.wftskupdate,wftskupdates.createdon,wftasks.tktin_idtktin FROM wftskupdates
	INNER JOIN usrrole ON wftskupdates.usrrole_idusrrole=usrrole.idusrrole
	INNER JOIN usrac ON wftskupdates.usrac_idusrac=usrac.idusrac
	INNER JOIN wftasks ON wftskupdates.wftasks_idwftasks=wftasks.idwftasks 	
	INNER JOIN wftskstatustypes ON wftskupdates.wftskstatustypes_idwftskstatustypes=wftskstatustypes.idwftskstatustypes
	WHERE wftasks.wftaskstrac_idwftaskstrac=".$fet_historyid['wftaskstrac_idwftaskstrac']." ORDER BY createdon DESC";
	$res_history=mysql_query($sql_history);
	$fet_history=mysql_fetch_array($res_history);
	$num_history=mysql_num_rows($res_history);	
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8"> 
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<title><?php echo $app_title;?></title>
<link rel="stylesheet" href="../m_assets/main.css" />
<script type="text/javascript" src="../../scripts/jquery.js"></script>
<script language="javascript" type="text/javascript" src="../../scripts/datetimepicker.js"></script>
<script type="text/javascript" src="../../scripts/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../scripts/jquery-ui-timepicker-addon_.js"></script>
<script type="text/javascript" src="../../scripts/jquery.autocomplete.js"></script>
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
                  	<?php if(isset($_SESSION['pview'])) { ?>
                  	<div class="header_font_m"><a href="search.php" class="button_small">Customer Tickets</a><a href="mytasks_view.php?task=<?php echo $_SESSION['wtaskid']; ?>" class="button_small">Task Details</a></div>
                  	<?php } else { ?>
 					<div class="header_font_m"><a href="<?php echo $_SESSION['tw_url'];?>" class="button_small"><?php echo $_SESSION['tw'];?></a><a href="mytasks_view.php?task=<?php echo $_SESSION['wtaskid']; ?>" class="button_small">Manage Task</a></div>
                    <?php } ?>                   
                </div>
          </div>
          <div style="padding:0px 2px 20px 2px" >
   
   <table border="0" cellpadding="2" cellspacing="0" width="100%">
			<tr>
            	<td class="tbl_h">Task History - <?php echo $_SESSION['tktno']; ?></td>
            </tr>
            <?php 
			if ($num_history>0)
				{
				do { ?>            
                <tr>
                    <td class="tbl_data">
                    <div class="small_font"><?php echo date("D, M d, Y h:i a",strtotime($fet_history['createdon'])); ?></div>
                    <div class="d_font"><strong>From:</strong> <?php echo $fet_history['utitle']." ".$fet_history['lname'];?> - <small><?php echo $fet_history['usrrolename'];?></small></div>
                    <div class="d_font"><strong>Action:</strong> <?php echo $fet_history['wftskstatustype'];?></div>
    				<?php    
					if (($fet_history['idwftskstatustypes']==2) || ($fet_history['idwftskstatustypes']==3) || ($fet_history['idwftskstatustypes']==5))
						{
						//find the recepient of this task
						$sql_recepient="SELECT utitle,lname,usrrolename FROM wftasks
						INNER JOIN usrrole ON wftasks.usrrole_idusrrole=usrrole.idusrrole 
						INNER JOIN usrac ON usrrole.idusrrole=usrac.usrrole_idusrrole
						WHERE wftasks_idwftasks=".$fet_history['idwftasks']." LIMIT 1";
						$res_recepient=mysql_query($sql_recepient);
						$num_recepient=mysql_num_rows($res_recepient);
						$fet_recepient=mysql_fetch_array($res_recepient);
			
						if ($num_recepient > 0)
						{ ?>
							<div class="d_font"><strong>To:</strong> <?php echo $fet_recepient['utitle']." ".$fet_recepient['lname'];?> - <small><?php echo $fet_recepient['usrrolename'];?></small></div>
						<?php }
						}?>
					<div class="d_font"><strong>Message:</strong> <?php echo $fet_history['wftskupdate'];?></div>
                    </td>
                </tr>
                <?php
				} while ($fet_history=mysql_fetch_array($res_history));					
			} ?>
  
            <tr>
            	<td class="tbl_data" style="background-color:#FFFFCC">
                <div class="small_font"><?php echo date("D, M d, Y h:i a",strtotime($_SESSION['timereported'])); ?></div>
                <div class="d_font"><strong>From:</strong> <?php echo "Customer via - "?><small><?php echo $_SESSION['tktchannelname'];?></small></div>
                <div class="d_font"><strong>Action:</strong> <?php echo "New Ticket";?></div>
                <div class="d_font"><strong>Message:</strong> <?php echo $_SESSION['tktdesc'];?></div>
                </td>               
            </tr>
            <!--<tr>
           	  <td class="body_font">&nbsp;</td>
            </tr>-->
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
