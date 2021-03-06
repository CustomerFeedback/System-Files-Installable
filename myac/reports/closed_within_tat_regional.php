<?php
require_once('../../assets_backend/be_includes/config.php');

if (isset($_GET['display']))
	{
	$_SESSION['display_type']=trim($_GET['display']);
	} else {
	$_SESSION['display_type']="graph";
	}

if ((isset($_GET['timestart'])) && ($_GET['timestart']!=''))
	{
	$_SESSION['timestart']=preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_escape_string(trim($_GET['timestart'])));
	} 
	
	if (!isset($_SESSION['timestart']))
	{
	$error_1 = "<div class=\"msg_warning\">Start Date is Missing</div>";
	}
	
if ((isset($_GET['timestop'])) && ($_GET['timestop']!=''))
	{
	$_SESSION['timestop']=preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_escape_string(trim($_GET['timestop'])));
	} 
	
	if (!isset($_SESSION['timestop'])) {
	$error_2="<div class=\"msg_warning\">Ending Date is Missing</div>";
	}

if (isset($_GET['reportname']))
	{
	$_SESSION['reportname']=trim($_GET['reportname']);
	}

	
?>	
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="../css_report_print.css" rel="stylesheet" type="text/css" media="print" />
<link href="../css_report.css" rel="stylesheet" type="text/css" />
<title><?php echo $_SESSION['reportname'];?></title>
</head>
<body>
<div style="padding:10px">
    <div >
    <div class="text_body_vlarge"><?php echo $_SESSION['MVGitHub_acteam'];?></div>
    <div class="text_body_large"><?php echo $_SESSION['reportname'];?></div>
    </div>
    <div>
    <div class="text_body_large">
    Report Period<br /> <?php echo date("D, M d, Y",strtotime($_SESSION['timestart'])); ?> - <?php echo date("D, M d, Y",strtotime($_SESSION['timestop'])); ?>
    </div>
    <div class="hidden">
            <table border="0" width="100%">
                <tr>
                    <td class="text_small" width="80%">
                     <span class="text_small" >
                
                <a href="#" style="color:#CCCCCC" class="text_body_mod">Switch to Graph</a>                </span>                    </td>
                    <td align="right" class="text_body_mod">
                    <a href="#" onClick="window.print()">Print</a>                    </td>
                    <td align="right" class="text_body_mod">
                    <?php
					//run the queries and do the tabular table below
					$sql_print="SELECT count(*) as tkts, tktcategoryname,idtktcategory,
					 TIME_TO_SEC( SUM(TIMEDIFF( tktin.timeclosed, tktin.timereported ) ) ) AS TTT,
					(SELECT count(*) FROM tktin WHERE usrteamzone_idusrteamzone=".$_SESSION['MVGitHub_userteamzoneid']." AND tktcategory_idtktcategory=idtktcategory AND tktin.timereported>='".$_SESSION['timestart']."' AND timereported<='".$_SESSION['timestop']."') as TTL 
					FROM tktin 
					INNER JOIN tktcategory ON tktin.tktcategory_idtktcategory=tktcategory.idtktcategory
					WHERE  TIME_TO_SEC(TIMEDIFF(tktin.timeclosed,tktin.timereported)) <= tktcategory.tat
					AND tktstatus_idtktstatus=4 
					AND tktin.usrteamzone_idusrteamzone=".$_SESSION['MVGitHub_userteamzoneid']."
					AND tktin.timereported>='".$_SESSION['timestart']."' 
					AND timereported<='".$_SESSION['timestop']."'
					GROUP BY tktin.tktcategory_idtktcategory";
					$res_print=mysql_query($sql_print);
					$num_print=mysql_num_rows($res_print);
					$fet_print=mysql_fetch_array($res_print);
					//echo $sql;
					?>
                    
                    <form method="post" action="print_report.php" name="excel" target="_blank">
                    <input type="hidden" name="report_name" value="<?php echo $_SESSION['reportname'];?>" />
                    <input type="hidden" name="report_body" value="<?php if ($num_print > 0)
						{
						echo "<table>
						<tr>
							<td>
							Category
							</td>
							<td>
							Total Received 
							</td>
							<td>
							Closed within TAT
							</td>
							<td>
							% Closed
							</td>
							<td>
							Avg. Time Taken per Ticket
							</td>
						</tr>";
						do {
						echo "
						<tr>
							<td>
							".$fet_print['tktcategoryname']."
							</td>
							<td>
							".$fet_print['TTL']."
							</td>
							<td>
							".$fet_print['tkts']."
							</td>
							<td>";
							
							$perc_raw = ($fet_print['tkts']/$fet_print['TTL'])*100;
							$perc = number_format($perc_raw,2);				
								
							echo $perc;
							echo "
							</td>
							<td>";
							$avg_raw=($fet_print['TTT'] / $fet_print['tkts']);
								 
								 if ($avg_raw > 86400) //if reached a day, then show in days
									{
									$avg_raw_days=($avg_raw/86400); ///days
									$lbl = " days";
									} else if (($avg_raw <= 86400) && ($avg_raw >= 3600)) //then show hours
									{
									$avg_raw_days=($avg_raw/3600); ///hours
									$lbl = " hrs";
									} else {
									$avg_raw_days=($avg_raw/60); ///minutes
									$lbl = " min";
									}
							//echo number_format( $avg_raw_days,2).$lbl;
							echo "</td>
							</tr>";
							} while ($fet_print=mysql_fetch_array($res_print));
					echo "</table>";
					}?>" />
                    <a href="#" onClick="document.forms['excel'].submit()">Export to Excel</a>
                    </form>
                    </td>
                </tr>
            </table>
            </div>
    </div>
    <div style="padding:10px 5px">
    <?php
    //run the queries and do the tabular table below
    $sql="SELECT count(*) as tkts, tktcategoryname,idtktcategory,
	 TIME_TO_SEC( SUM(TIMEDIFF( tktin.timeclosed, tktin.timereported ) ) ) AS TTT,
    (SELECT count(*) FROM tktin WHERE usrteamzone_idusrteamzone=".$_SESSION['MVGitHub_userteamzoneid']." AND tktcategory_idtktcategory=idtktcategory AND tktin.timereported>='".$_SESSION['timestart']."' AND timereported<='".$_SESSION['timestop']."') as TTL 
    FROM tktin 
    INNER JOIN tktcategory ON tktin.tktcategory_idtktcategory=tktcategory.idtktcategory
    WHERE  TIME_TO_SEC(TIMEDIFF(tktin.timeclosed,tktin.timereported)) <= tktcategory.tat
    AND tktstatus_idtktstatus=4 
	AND tktin.usrteamzone_idusrteamzone=".$_SESSION['MVGitHub_userteamzoneid']."
    AND tktin.timereported>='".$_SESSION['timestart']."' 
    AND timereported<='".$_SESSION['timestop']."'
    GROUP BY tktin.tktcategory_idtktcategory";
    $res=mysql_query($sql);
    $num=mysql_num_rows($res);
    $fet=mysql_fetch_array($res);
    //echo $sql;
    if ($num > 0)
        {
    
    ?>
    <table border="0" cellpadding="2" cellspacing="0">
        <tr>
          <td class="tbl_h">
            Category            </td>
          <td class="tbl_h">
            Total Received            </td>
          <td class="tbl_h">
            Closed within TAT            </td>
          <td class="tbl_h">
            % Closed            </td>
          <td class="tbl_h">
            Avg. Time Taken per Ticket            </td>
      </tr>
        <?php
            do {
        ?>
        <tr>
            <td class="tbl_data">
              <strong><?php echo $fet['tktcategoryname'];?></strong> </td>
            <td class="tbl_data">
            <?php echo $fet['TTL'];?>
            </td>
            <td class="tbl_data">
            <?php echo $fet['tkts'];?>
            </td>
          <td class="tbl_data">
            <?php 
			$perc_raw = ($fet['tkts']/$fet['TTL'])*100;
			$perc = number_format($perc_raw,2);
			
			if ($perc > 50)
				{
				$color="#009900";
				} else {
				$color="#ff0000";
				}
			
			echo "<span style=\"color:".$color.";font-weight:bold\">".$perc."%</span>";
			?>
          </td>
            <td class="tbl_data">
             <?php
			 $avg_raw=($fet['TTT'] / $fet['tkts']);
			 
			 if ($avg_raw > 86400) //if reached a day, then show in days
			 	{
				$avg_raw_days=($avg_raw/86400); ///days
				$lbl = " days";
				} else if (($avg_raw <= 86400) && ($avg_raw >= 3600)) //then show hours
				{
				$avg_raw_days=($avg_raw/3600); ///hours
				$lbl = " hrs";
				} else {
				$avg_raw_days=($avg_raw/60); ///minutes
				$lbl = " min";
				}
			 //echo number_format( $avg_raw_days,2).$lbl;
			//echo $fet['TTT'];
			?>
            </td>
        </tr>
        <?php
        } while ($fet=mysql_fetch_array($res));
        ?>
    </table>    
    <?php    
        } else {
        
        echo "<span style=\"font-family:arial;color:#ff0000\">No Data to Generate Report</span>";
        
        }
    ?>
    </div>
</div>
</body>
</html>
