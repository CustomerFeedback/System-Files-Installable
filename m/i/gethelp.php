<?php 
require_once('../required/config.php');
require_once('../../Connections/connSystem.php'); 
require_once('../required/check_user_mobile.php');
mysql_select_db($database_connSystem, $connSystem); //connect to DB

if( (isset($_SESSION['WkAtToMrPa_usrphone'])) && (strlen($_SESSION['WkAtToMrPa_usrphone'])==12) )
	{
	$mobnum=$_SESSION['WkAtToMrPa_usrphone'];
	} else {
	$mobnum='';
	}

if ( (isset($_POST['form_action'])) && ($_POST['form_action']=="cs_form") )
	{	
	//clean data
	$urgency=mysql_real_escape_string(trim($_POST['urgency']));
	$csmnum=mysql_real_escape_string(trim($_POST['csmnum']));	
	$csmsg=mysql_real_escape_string(trim($_POST['csmsg']));	
	
	if ($urgency==0)
		{
		$error_0="<div class=\"msg_warning_small\">Select Urgency Level</div>";
		}	

	if (strlen($csmnum)<3)
		{
		$error_1="<div class=\"msg_warning_small\">Enter Your Mobile Number</div>";
		} else if ((strlen($csmnum)<10) || (strlen($csmnum)>10)) {	
		$error_2="<div class=\"msg_warning_small\">Confirm Your Mobile Number</div>";
		}	
	
	if ( (!isset($error_1)) && (!isset($error_2)) && (!is_numeric($csmnum)) )	
		{
		$error_3="<div class=\"msg_warning_small\">Enter Numbers Only</div>";
		}
				
	if (strlen($csmsg)<10)
		{
		$error_4="<div class=\"msg_warning_small\">Enter Your Message</div>";
		}	
	
	if( (!isset($error_0)) && (!isset($error_1)) && (!isset($error_2)) && (!isset($error_3)) && (!isset($error_4)) )
		{	
		//Insert into the helpdesk table
		$insert="INSERT INTO hd (hd_urgency_idhd_urgency,datesent,helpsubject,helpmsg,createdon,createdby,helpmobile)
		VALUES ('".$urgency."','".$timenowis."','Mobile Support Request','".$csmsg."','".$timenowis."','".$_SESSION['WkAtToMrPa_idacname']."','".$csmnum."')";
		mysql_query($insert);	

		//send out SMSes to the support team
		$txtmsg_raw="URGENT MajiVoice Request [".$_SESSION['WkAtToMrPa_userteamshortname']."] ".$csmnum." - ".$csmsg."";
		$txtmsg=substr($txtmsg_raw,0,160);
	
		//For instant SMS alerts to the support team
//		$sql_sms="INSERT INTO mdata_out_sms (destnumber,msgtext) VALUES ()";
//		mysql_query($sql_sms);						
		
		$cs_feedback="<div class=\"msg_success_small\">The support team will contact you shortly</div>";	
		}
	}

?>
<html>
<head>
<meta charset="utf-8"> 
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<title><?php echo $app_title;?></title>
<link rel="stylesheet" href="../m_assets/main.css" />
<link rel="stylesheet" href="../m_assets/main_mmrs.css" />
<script language=Javascript>
       function isNumberKey(evt)
       {
          var charCode = (evt.which) ? evt.which : event.keyCode;
          if (charCode != 46 && charCode > 31 
            && (charCode < 48 || charCode > 57))
             return false;

          return true;
       }
</script>

</head>
<body>
<div><!-- page -->
    <div>
    <?php require_once('../m_header_2.php');?>
    </div>
        
    <div><!-- content -->
    <div><!-- version error check -->
	<?php 
	if ((isset($error_version)) || (isset($verror)) || (isset($verror2)) ) 
		{ 
		echo $error_version.$verror.$verror2; 
		} else { ?>
    
        <div class="fldblk2">
		<?php if(isset($cs_feedback)) 
            { 
            echo $cs_feedback; 
            
            } else {  ?>	    

            <div>
            <?php 
				if (isset($error_0)) { echo $error_0; } 
                if (isset($error_1)) { echo $error_1; } 
                if (isset($error_2)) { echo $error_2; } 
                if (isset($error_3)) { echo $error_3; } 
                if (isset($error_4)) { echo $error_4; } 
            ?>
            </div>

	        <form id="csform" name="csform" method="post" action="" autocomplete="off">	
            <div style="height:45px; padding:0px 10px 0px 10px; font-size:12px;">
                <div style="width:100%;">
                    <select id="urgency" name="urgency" class="sbox" style="width:100%; height:40px;">
                    <option value="0">[ How Urgent is it? ]</option>
                    <?php
                    $sql_urgent="SELECT idhd_urgency,hdurgency FROM hd_urgency";
                    $res_urgent=mysql_query($sql_urgent);
                    $num_urgent=mysql_num_rows($res_urgent);
                    $fet_urgent=mysql_fetch_array($res_urgent);
                        
                    if ($num_urgent>0)
                        {
                        do 	{?>
                            <option <?php if ((isset($urgency)) && ($urgency==$fet_urgent['idhd_urgency'])) { echo "selected=\"selected\"";}?>  value="<?php echo $fet_urgent['idhd_urgency'];?>"><?php echo $fet_urgent['hdurgency'];?></option>
                            <?php
                            } while ($fet_urgent=mysql_fetch_array($res_urgent));
                        }?>
                    </select>
                    </div>
                </div>                             
     
                <div style="height:52px; padding:10px 10px 0px 10px">
                	<div class="fldlbl3" style="color:#FF0000; font-weight:bold;">Your Mobile Number <span style="font-size:10px; font-weight:normal"></span></div>
                    <div class="alninput" style="width:100%; font-size:12px;">
                    	<input style="width:100%" class="tbox" type="number" maxlength="10" id="csmnum" name="csmnum" onKeyPress="return isNumberKey(event)" value="<?php if(isset($csmnum)) { echo $csmnum; } else { echo $mobnum; } ?>" />
                  	</div>                
                </div>
                
                <div style="height:52px; padding:10px 10px 0px 10px">
                    <div class="fldlbl3" style="color:#FF0000; font-weight:bold; padding-left:2px;">Your Message <span style="font-size:10px; font-weight:normal"></span></div>        
                    <div class="alninput" style="width:100%; font-size:12px">
	                    <input style="width:100%" class="tbox" type="text" maxlength="120" id="csmsg" name="csmsg" value="<?php if(isset($csmsg)) { echo $csmsg; } else { echo ""; } ?>" />
                   	</div>
                </div> 
                            
                <div class="fldblk" align="center" style="padding:15px 10px 10px 10px; height:40px;">
	                <input type="submit" class="button" style="width:90%" name="cssend" id="cssend" value="Send Message" />
                </div>
                
                <input type="hidden" value="cs_form" name="form_action" />
            </form> 
       <?php } 
	   if( (isset($_SESSION['WkAtToMrPa_teamzonephone'])) && (strlen($_SESSION['WkAtToMrPa_teamzonephone'])>1) )
	   		{?>	     
    
            <div style="height:30px; padding:20px 10px 10px 10px;">
                <div class="alninput" style="width:98%; background-color:#FFFFCC; padding:5px; font-size:12px; font-weight:bold" align="center">
                    Need urgent help? Call <?php echo $_SESSION['WkAtToMrPa_teamzonephone']; ?>
                </div>
            </div>   
            <?php } ?>

  	</div>            
    <?php } ?>
    </div><!-- /version check -->     
	</div><!-- /content -->
    
          <div class="menu">
          	<a href="index.php" style="color:#FFFFFF; text-decoration:none">
   	      		<div><img src="../m_assets/menu_arrow_n.png" width="16" height="19" border="0" align="absmiddle" /> Find Ticket</div>
       	  	</a>
          </div>                          
          <div class="menuo">
          	<a href="logout.php" style="text-decoration:none">
   	      		<div><img src="../m_assets/icon_login_n.gif" width="16" height="19" border="0" align="absmiddle" /> Log Out</div>
       	 	</a>
       	  </div>  
    	</div>

</div><!-- /page -->

</body>
</html>
