<?php
require_once('required/config.php');

include ('nocsrf.php');

require_once('../Connections/connSystem.php');
mysql_select_db($database_connSystem, $connSystem);

//then query and count number of attempts in the last 30 minutes
$sql_lastlog="SELECT attempttime FROM audit_login WHERE userip='".$userIP."' AND usersession='".session_id()."' ORDER BY idaudit_login DESC LIMIT 3,1";
$res_lastlog=mysql_query($sql_lastlog);
$num_lastlog=mysql_num_rows($res_lastlog);
$fet_lastlog=mysql_fetch_array($res_lastlog);
//echo $sql_lastlog;
$login_time = $fet_lastlog['attempttime'];
			
$deducted_time = strtotime($timenowis) - strtotime($login_time) ;
//echo $deducted_time;	
//if the earliest time logged in of the three (3) consecutive login attempts is less than 100 seconds, then kill the login process	
//ban for 15 minutes which = 15*60=1350 seconds
if ($deducted_time < 1350 )
	{
	//header ('location:error_times.php');
	//exit;
	//time remaining to return login screen?
	$relogin=number_format(((1350-$deducted_time)/60),0); 
	
	$error_timeout="<div class=\"msg_warning\" style=\"margin:35px\"><ul><li>Too many failed login attempts</li>
	<li>The Log-in Screen will be back in about <h3> ".$relogin." minutes...</h3></li>				
	</div>";
	//echo "OLOLOLO";
	}
			
			
if ((isset($_POST['form_action'])) && ($_POST['form_action']=="authenticate"))
	{
	
try
{
// Run CSRF check, on POST data, in exception mode, for 10 minutes, in one-time mode.
NoCSRF::check( 'csrf_token', $_POST, true, 60*10, false );
		
			//first clean em up
			$username = preg_replace('/[^a-z\-_0-9\.:@\/\s]/i','',mysql_escape_string(trim($_POST['usr'])));
			$userpass = mysql_escape_string(trim($_POST['pwd']));		
			
			//check if the mac address for this server is valid before proceeding
		/*	ob_start(); // Turn on output buffering
			system('ipconfig /all'); //Execute external program to display output
			$mycom=ob_get_contents(); // Capture the output into a variable
			ob_clean(); // Clean (erase) the output buffer
			
			$findme = "Physical";
			$pmac = strpos($mycom, $findme); // Find the position of Physical text
			$mac=substr($mycom,($pmac+36),17); // Get Physical Address*/
		
		
			
			//check if the user has submitted this
			if ( (strlen($username)<1) && (strlen($userpass)<1) )
				{
				$error =  "<div class=\"msg_warning\">Please enter valid account details...</div>";
				} else {
				//validate
				$sql_check = "SELECT usrac.idusrac,usrac.usrname,usrac.usrpass,usrac.utitle,usrac.fname,usrac.lname,usrac.usremail,usrac.usrphone,usrteam.usrteamname,usrteam.usrteamshortname,usrteam.usrteamshortname,usrteam.idusrteam,usrac.acstatus,usrrole.idusrrole,usrrole.reportingto,usrrole.usrrolename,usrrole.reportingto,usrrole.sysprofiles_idsysprofiles,usrteamzone.userteamzonename,usrteamzone.idusrteamzone,usrteam.mainlogo_path,usrteam.usrteamtype_idusrteamtype,usrteamzone.region_pref,usrteamzone.teamzonephone,usrrole.joblevel,usrrole.usrdpts_idusrdpts FROM usrac 
				INNER JOIN usrteam ON usrac.usrteam_idusrteam=usrteam.idusrteam
				INNER JOIN usrrole ON usrac.usrrole_idusrrole=usrrole.idusrrole
				INNER JOIN usrteamzone ON usrrole.usrteamzone_idusrteamzone=usrteamzone.idusrteamzone
				WHERE usrname='".$username."' AND usrpass='".SHA1($userpass)."' LIMIT 1";
				//echo $sql_check;
				//exit;
				$res_check = mysql_query($sql_check);
				$num_check = mysql_num_rows($res_check);
				$fet_check = mysql_fetch_array($res_check);
				
				
				if ($num_check < 1)
					{
				$error = "<div class=\"msg_warning\">Invalid Access Details</div>";
				
			//first, insert the attempt
			$sql_reclog="INSERT INTO audit_login (acname,userip,userbrowser,urlreferer,attempttime,attemptresult,usersession)
			VALUES ('".$username."','".$userIP."','".$userBrowser."','".$_SERVER['HTTP_REFERER']."','".$timenowis."','FAIL','".session_id()."')";
			mysql_query($sql_reclog);
				//echo $sql_reclog;
				//echo "<br>";
					}
					
				if ( ($num_check > 0) && ($fet_check['acstatus']!="1") ) //if user status not active, then warn
					{
				$error = "<div class=\"msg_warning\">Invalid Access Details</div>";
					}
					
				if ( ($num_check > 0) && ($fet_check['acstatus']=="1") )
					{	
					//create the session
					$_SESSION['WkAtToMrPa_logstatus'] = "IS_LOGGED_IN";
					$_SESSION['WkAtToMrPa_acname'] = $fet_check['usrname'];
					$_SESSION['WkAtToMrPa_acpass'] = $fet_check['usrpass'];
					$_SESSION['WkAtToMrPa_usrtitle'] = $fet_check['utitle'];
					$_SESSION['WkAtToMrPa_usrfname'] = $fet_check['fname'];
					$_SESSION['WkAtToMrPa_usrlname'] = $fet_check['lname'];
					$_SESSION['WkAtToMrPa_idacname'] = $fet_check['idusrac'];
					$_SESSION['WkAtToMrPa_usremail'] = $fet_check['usremail'];
					$_SESSION['WkAtToMrPa_usrphone'] = $fet_check['usrphone'];
					$_SESSION['WkAtToMrPa_acteam'] = $fet_check['usrteamname'];
					$_SESSION['WkAtToMrPa_acteamshrtname'] = $fet_check['usrteamshortname'];
					$_SESSION['WkAtToMrPa_logo'] = $fet_check['mainlogo_path'];
					$_SESSION['WkAtToMrPa_userrole'] = $fet_check['usrrolename'];
					$_SESSION['WkAtToMrPa_reportingto'] = $fet_check['reportingto'];
					$_SESSION['WkAtToMrPa_iduserrole'] = $fet_check['idusrrole'];
					$_SESSION['WkAtToMrPa_idacteam'] = $fet_check['idusrteam'];
					$_SESSION['WkAtToMrPa_iduserprofile'] = $fet_check['sysprofiles_idsysprofiles'];
					$_SESSION['WkAtToMrPa_userteamzone'] = $fet_check['userteamzonename'];
					$_SESSION['WkAtToMrPa_teamzonephone'] = $fet_check['teamzonephone'];					
					$_SESSION['WkAtToMrPa_userteamzoneid'] = $fet_check['idusrteamzone'];
					$_SESSION['WkAtToMrPa_userteamshortname'] =$fet_check['usrteamshortname'];
					$_SESSION['WkAtToMrPa_userteamtype'] =$fet_check['usrteamtype_idusrteamtype'];
					$_SESSION['WkAtToMrPa_tblbill'] ="billacs_".strtolower($fet_check['usrteamshortname']);
					$_SESSION['WkAtToMrPa_tblsmsbc'] ="smssubs_".strtolower($fet_check['usrteamshortname']);
					$_SESSION['WkAtToMrPa_regionpref']=$fet_check['region_pref'];
					$_SESSION['WkAtToMrPa_joblevel']=$fet_check['joblevel'];
					$_SESSION['WkAtToMrPa_usrdpts']=$fet_check['usrdpts_idusrdpts'];
					//Added by Dickson Marira on 24th FEB 2015
					$_SESSION['WkAtToMrPa_usrteamsubzone']=$fet_check['usrteamsubzone_idusrteamsubzone'];
					//log the password modification date
					//$_SESSION['WkAtToMrPa_chkpass']=$fet_check['passwd_modifiedon'];	
					 	
					//record the current session
					$sql_sess="UPDATE usrac SET currentsess='".session_id()."',lastaccess='".$timenowis."' WHERE idusrac=".$_SESSION['WkAtToMrPa_idacname']."";
					mysql_query($sql_sess);
					
					//first, insert the attempt
					$sql_reclog="INSERT INTO audit_login (acname,userip,userbrowser,urlreferer,attempttime,attemptresult,usersession)
					VALUES ('".$username."','".$userIP."','".$userBrowser."','".$_SERVER['HTTP_REFERER']."','".$timenowis."','OK','".session_id()."')";
					mysql_query($sql_reclog);
					//echo $sql_reclog;
					//echo "<br>";
					//send to new page
					//find out which module and submodule this person has
					/*$sql_mymods="SELECT syssubmodule_idsyssubmodule,sysprofiles_idsysprofiles,idsyssubmodule,sysmodule_idsysmodule 	FROM systemprofileaccess 
					INNER JOIN syssubmodule ON systemprofileaccess.syssubmodule_idsyssubmodule=syssubmodule.idsyssubmodule 
					WHERE systemprofileaccess.sysprofiles_idsysprofiles=".$fet_check['sysprofiles_idsysprofiles']." ORDER BY idsystemprofileaccess ASC LIMIT 1 ";
					$res_mymods=mysql_query($sql_mymods);
					$fet_mymods=mysql_fetch_array($res_mymods);
					
					$_SESSION['MFAsec_mod']=$fet_mymods['sysmodule_idsysmodule'];*/
					
					//get the latest billing cycle
					$sql_bc="SELECT iditinerary_meta FROM itinerary_meta WHERE current_meta=1 ORDER BY iditinerary_meta DESC LIMIT 1";
					$res_bc=mysql_query($sql_bc);
					$fet_bc=mysql_fetch_array($res_bc);
					
					$_SESSION['bcycle']=$fet_bc['iditinerary_meta'];
					$_SESSION['bcycle_current']=$fet_bc['iditinerary_meta'];
					
					
					//CHECK IF THIS USER BELONGS TO A WORKTASKGROUP AND CONSTRUCT THIS QUERY ONLY ONCE FOR VIEWING TASKS
					$sql_mygroup="SELECT idwfactorsgroup FROM wfactorsgroup WHERE usrrole_idusrrole=".$_SESSION['WkAtToMrPa_iduserrole']." ORDER BY idwfactorsgroup ASC ";
					$res_mygroup=mysql_query($sql_mygroup);
					$num_mygroup=mysql_num_rows($res_mygroup);
					$fet_mygroup=mysql_fetch_array($res_mygroup);
					//echo $sql_mygroup;
					$idwfgroup="";				
					
					if ($num_mygroup > 0)
						{
						do {
							$idwfgroup.= " OR wftasks.wfactorsgroup_idwfactorsgroup=".$fet_mygroup['idwfactorsgroup']." ";
							} while ($fet_mygroup=mysql_fetch_array($res_mygroup));
							
						$_SESSION['idwfgroup']=$idwfgroup;
						$_SESSION['idwfgroup_val']=$fet_mygroup['idwfactorsgroup'];
							
						} else {
						$_SESSION['idwfgroup'] = "";
						$_SESSION['idwfgroup_val']="";
						}
					
					header("location:i/?mod=1");
					exit;
					}//close if num check is >0 and userstatus is 1
					
				} //close if username and password are not empty
			
			}
catch ( Exception $e )
{
// CSRF attack detected
$result = $e->getMessage() . ' Form ignored.';
}

	} //close form action
//close this mysql connection


$token = NoCSRF::generate( 'csrf_token' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8"> 
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<title><?php echo $app_title;?></title>
<link rel="stylesheet" href="m_assets/main.css" />
<script type="text/javascript" src="../scripts/jquery.js"></script>
<script type="text/javascript" src="../uilock/jquery.uilock.js"></script>
<script language="javascript">
//Logging in preloader
			$(document).ready(function() {
				//$('#lock').click(function(){
				$('#button_login').click(function(){
				
					// To lock user interface interactions
					// Optinal: put html on top of the lock section,
					// like animated loading gif
					
					//$.uiLock('some html and <a href="#" onclick="$.uiUnlock();">unlock</a>');
				$.uiLock('<center class=msg_ok_overlay>Logging In...One Moment Please...</center>');
					
				});
				
				
				// To unlock user interface interactions
				//$.uiUnlock();

			});
</script>

</head>
<body>
<div>
	<!-- header -->
   	<!--<div class="hblock">
    	<div class="htxt" style="width:100%">
		<center><h2>Kiwasco Link Mobile</h2></center>
        </div>
    </div>-->
    <div>
        <?php require_once('m_header_2.php');?>
    </div>
    <!-- /header -->

    <div><!-- content -->
    	<?php 
		if(isset($error)) { echo $error; }
		?>
        <form method="post" style="margin:0px" action="" name="search" autocomplete="off">
	   	    <div class="fldblk" align="center" style="padding:20px 10px 0px 10px; height:50px;">
            	<div class="fldlbl" align="left" style="background-color:#FFFFFF; color:#000000">Account Name</div>
              	<div><input class="tbox" type="text" style="height:20px; width:80%" name="usr" value="" /></div>
			</div>	
        	<div class="fldblk" align="center" style="padding:20px 10px 10px 10px; height:50px;">
				<div class="fldlbl" align="left" style="background-color:#FFFFFF; color:#000000">Account Password</div>
           	  <div><input class="tbox" type="password" style="height:20px; width:80%" name="pwd" value="" /></div>
        	</div>	
        	<div class="fldblk" align="center" style="padding:10px 10px 10px 10px; height:50px;">
                <input type="hidden" value="authenticate" name="form_action" />
                <input type="hidden" value="<?php echo $token;?>" name="csrf_token" />
                <a href="#" onclick="document.forms['search'].submit()" id="button_login" class="button" style="width:80%">Log In</a>
            </div>    

        </form>
		<div style="padding:10px 0px 30px 0px;" align="center">
			<!--<img src="m_assets/logo.png">-->
            [Logo Here]
		</div>    
    </div><!-- content -->
    
	<div class="fblockfixed"><!-- footer -->
    	<div class="ftxt">Customer Feedback</div>
    </div><!-- /footer -->
</div>
</body>
</html>