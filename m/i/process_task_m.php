<?php
require_once('../required/config.php');
require_once('../../Connections/connSystem.php'); 
mysql_select_db($database_connSystem, $connSystem); //connect to DB
require_once('../required/check_user_mobile.php');

if (isset($_SESSION['wtaskid']))
	{ 	//003
	//find out if this task has a tskflowid and recepient whether it is to an individual or a group
	$sql_istskflow="SELECT wftskflow_idwftskflow,usrrole_idusrrole,usrac_idusrac,wfactorsgroup_idwfactorsgroup FROM wftasks WHERE idwftasks=".$_SESSION['wtaskid']."";
	$res_istskflow=mysql_query($sql_istskflow);
	$num_istskflow=mysql_num_rows($res_istskflow);
	$fet_istskflow=mysql_fetch_array($res_istskflow);
	
	//is a group task
	$is_group_task=$fet_istskflow['wfactorsgroup_idwfactorsgroup'];
		
	if ($fet_istskflow['wftskflow_idwftskflow'] > 0) //if there is a taskflow, then proceed
		{ //2121
		
		if ( ($fet_istskflow['usrrole_idusrrole']==0) && ($fet_istskflow['usrac_idusrac']==0) && ($fet_istskflow['wfactorsgroup_idwfactorsgroup']>0) )
			{ //if this is a group task		
			//check if it is a group task, then query the specific user role from the group...otherwise use default query
			$sql_task="SELECT idwftasks,wftasks.wftaskstrac_idwftaskstrac,wftasks.usrrole_idusrrole,wftasks_idwftasks,wftasks.wftskflow_idwftskflow,wftasks.tktin_idtktin,wftasks.usrac_idusrac,wftasks.wftskstatustypes_idwftskstatustypes,tasksubject,taskdesc,wftasks.timeinactual,wftasks.timeoveralldeadline,wftasks.timetatstart,wftasks.timedeadline,TIMESTAMPDIFF(MINUTE, NOW(), wftasks.timedeadline) AS time_to_deadline,wftskflow.wfproc_idwfproc,wftskflow.listorder,wftasks.sender_idusrrole,wftasks.sender_idusrac,wftasks.wftasks_batch_idwftasks_batch,wfproc.usrteam_idusrteam FROM wftasks
			INNER JOIN wftskflow ON wftasks.wftskflow_idwftskflow = wftskflow.idwftskflow
			INNER JOIN wfproc ON wftskflow.wfproc_idwfproc=wfproc.idwfproc
			WHERE wftasks.idwftasks=".$_SESSION['wtaskid']."";			
			} else { //else if not a group task
			$sql_task="SELECT idwftasks,wftasks.wftaskstrac_idwftaskstrac,wftasks.usrrole_idusrrole,wftasks_idwftasks,wftasks.wftskflow_idwftskflow,wftasks.tktin_idtktin,wftasks.usrac_idusrac,wftasks.wftskstatustypes_idwftskstatustypes,tasksubject,taskdesc,wftasks.timeinactual,wftasks.timeoveralldeadline,wftasks.timetatstart,wftasks.timedeadline,TIMESTAMPDIFF(MINUTE, NOW(), wftasks.timedeadline) AS time_to_deadline,usrrole.usrrolename,usrrole.usrteamzone_idusrteamzone,usrteamzone.usrteam_idusrteam,usrac.utitle,usrac.lname,wftskflow.wfproc_idwfproc,wftskflow.listorder,wftasks.sender_idusrrole,wftasks.sender_idusrac,wftasks.wftasks_batch_idwftasks_batch FROM wftasks
			INNER JOIN usrrole ON wftasks.usrrole_idusrrole=usrrole.idusrrole 
			INNER JOIN usrac ON wftasks.usrac_idusrac = usrac.idusrac
			INNER JOIN wftskflow ON wftasks.wftskflow_idwftskflow = wftskflow.idwftskflow
			INNER JOIN usrteamzone ON usrrole.usrteamzone_idusrteamzone=usrteamzone.idusrteamzone
			WHERE wftasks.idwftasks=".$_SESSION['wtaskid']."";
			//echo "<br><br><br>".$sql_task."<br>";
			}
		$res_task=mysql_query($sql_task);
		$fet_task=mysql_fetch_array($res_task);
		$num_task=mysql_num_rows($res_task);
		
		
		//this check is only for the first step of entry... after the START list order 0.00 on the workflow
		$sql_wfproc="SELECT wfproc_idwfproc,idwftskflow FROM wftskflow WHERE idwftskflow=".$fet_task['wftskflow_idwftskflow']." LIMIT 1";
		//echo "<br><br>".$sql_wfproc;
		$res_wfproc=mysql_query($sql_wfproc);
		$fet_wfproc=mysql_fetch_array($res_wfproc);
	
		//set the task flow variables here
		$_SESSION['wftaskstrac']=$fet_task['wftaskstrac_idwftaskstrac'];
		$_SESSION['thistskflow']=$fet_task['wftskflow_idwftskflow'];
		$_SESSION['tktin_idtktin']=$fet_task['tktin_idtktin'];
		$_SESSION['wfproc_idwfproc']=$fet_task['wfproc_idwfproc'];
		$_SESSION['listorder']=$fet_task['listorder'];
		
		//get the workflow process ID as well
		
						
		} else { //2121 else possibly this is an exception or the first task wkflowid==0

		//query the database for this task details
		if ( ($fet_istskflow['usrrole_idusrrole']==0) && ($fet_istskflow['usrac_idusrac']==0) && ($fet_istskflow['wfactorsgroup_idwfactorsgroup']>0) )
			{ //if this is a group task		
			//check if it is a group task, then query the specific user role from the group...otherwise use default query
			$sql_task="SELECT idwftasks,wftasks.wftaskstrac_idwftaskstrac,wftasks.usrrole_idusrrole,wftasks_idwftasks,wftasks.wftskflow_idwftskflow,wftasks.tktin_idtktin,wftasks.usrac_idusrac,wftasks.wftskstatustypes_idwftskstatustypes,tasksubject,taskdesc,wftasks.timeinactual,wftasks.timeoveralldeadline,wftasks.timetatstart,wftasks.timedeadline,TIMESTAMPDIFF(MINUTE, NOW(), wftasks.timedeadline) AS time_to_deadline,wftasks.sender_idusrrole,wftasks.sender_idusrac,wftasks.wftasks_batch_idwftasks_batch,tktin.usrteam_idusrteam 
			FROM wftasks
			INNER JOIN tktin ON wftasks.tktin_idtktin=tktin.idtktinPK
			WHERE wftasks.idwftasks=".$_SESSION['wtaskid']."";			
			} else { //else if not a group task
			$sql_task="SELECT idwftasks,wftasks.wftaskstrac_idwftaskstrac,wftasks.usrrole_idusrrole,wftasks_idwftasks,wftasks.wftskflow_idwftskflow,wftasks.tktin_idtktin,wftasks.usrac_idusrac,wftasks.wftskstatustypes_idwftskstatustypes,tasksubject,taskdesc,wftasks.timeinactual,wftasks.timeoveralldeadline,wftasks.timetatstart,wftasks.timedeadline,TIMESTAMPDIFF(MINUTE, NOW(), wftasks.timedeadline) AS time_to_deadline,usrrole.usrrolename,usrrole.usrteamzone_idusrteamzone,usrteamzone.usrteam_idusrteam,usrac.utitle,usrac.lname,wftasks.sender_idusrrole,wftasks.sender_idusrac,wftasks.wftasks_batch_idwftasks_batch
			FROM wftasks
			INNER JOIN usrrole ON wftasks.usrrole_idusrrole=usrrole.idusrrole
			INNER JOIN usrac ON wftasks.usrac_idusrac = usrac.idusrac
			INNER JOIN usrteamzone ON usrrole.usrteamzone_idusrteamzone=usrteamzone.idusrteamzone
			WHERE wftasks.idwftasks=".$_SESSION['wtaskid']."";
			$res_task=mysql_query($sql_task);
			$fet_task=mysql_fetch_array($res_task);
			$num_task=mysql_num_rows($res_task);
			}
		
		
	
		//get the workflow process id
		$sql_wfproc="SELECT wfproc_idwfproc,idwftskflow,listorder FROM wftskflow 
		INNER JOIN wftasks ON wftskflow.idwftskflow=wftasks.wftskflow_idwftskflow
		WHERE wftaskstrac_idwftaskstrac=".$fet_task['wftaskstrac_idwftaskstrac']." AND wftskflow_idwftskflow>0 LIMIT 1";
		$res_wfproc=mysql_query($sql_wfproc);
		$fet_wfproc=mysql_fetch_array($res_wfproc);
		
		//set taskflow variables here here	
		$_SESSION['wftaskstrac']=$fet_task['wftaskstrac_idwftaskstrac'];
		$_SESSION['thistskflow']=0; //NOT SET
		$_SESSION['tktin_idtktin']=$fet_task['tktin_idtktin'];
		$_SESSION['wfproc_idwfproc']=$fet_wfproc['wfproc_idwfproc'];
		$_SESSION['listorder']=$fet_wfproc['listorder'];	
		} //2121
			
	if (isset($_POST['tktcat'])) 
		{
		$sql_ticketcat="SELECT tktcategoryname FROM tktcategory WHERE idtktcategory=".$_POST['tktcat']."";
		$res_ticketcat=mysql_query($sql_ticketcat);
		$fet_ticketcat=mysql_fetch_array($res_ticketcat);
		}
	} //003


//fetch ticket details
		//Get the Ticket Details on the form
		$sql_ticket="SELECT idtktinPK,tktchannelname,tktstatusname,tktcategoryname,locationname,tktlang_idtktlang,usrteamzone_idusrteamzone,usrteam_idusrteam,tktin.tktgroup_idtktgroup,tktin.tktchannel_idtktchannel,tktin.tktstatus_idtktstatus,tktin.tktcategory_idtktcategory,tktin.tkttype_idtkttype,sendername,senderphone,senderemail,refnumber,tktdesc,timereported,timedeadline,timeclosed,city_town,loctowns_idloctowns,road_street,building_estate,unitno,waterac,kioskno,tkttype.idtkttype,tkttype.tkttypename,tktin.landmark,tktin.sendergender,refnumber_prev,wftasks_batch_idwftasks_batch,voucher_number FROM tktin
		INNER JOIN tktchannel ON tktin.tktchannel_idtktchannel=tktchannel.idtktchannel
		INNER JOIN tktstatus ON tktstatus_idtktstatus=tktstatus.idtktstatus
		INNER JOIN tkttype ON tktin.tkttype_idtkttype=tkttype.idtkttype
		INNER JOIN tktcategory ON tktin.tktcategory_idtktcategory=tktcategory.idtktcategory
		INNER JOIN loctowns ON tktin.loctowns_idloctowns=loctowns.idloctowns WHERE idtktinPK=".$fet_task['tktin_idtktin']." 
		AND usrteam_idusrteam=".$_SESSION['WkAtToMrPa_idacteam']."";
		$res_ticket=mysql_query($sql_ticket);
		$fet_ticket=mysql_fetch_array($res_ticket);
		
		$_SESSION['tktupdate']=$fet_ticket['idtktinPK']; //store the id for the ticket to update
		$_SESSION['wtitle']=$fet_ticket['refnumber'];
		$_SESSION['prev_tkt_num']=$fet_ticket['refnumber_prev'];		
		

if ( (isset($_POST['update_ticket_details'])) && ($_POST['update_ticket_details']=="Save") ) //Save Ticket Details
	{ 
	//clean up
	$tktacno=preg_replace('/[^a-z\-_0-9\.:\/]/i','',mysql_real_escape_string(trim($_POST['acnumber'])));
	$tktkiosk=preg_replace('/[^a-z\-_0-9\.:\/]/i','',mysql_real_escape_string(trim($_POST['kiosk'])));
	$txtmsg=mysql_real_escape_string(trim($_POST['txtmsg']));
	
	$sql_update="UPDATE tktin SET 
	kioskno='".$tktkiosk."',
	tktdesc='".$txtmsg."'
	WHERE idtktinPK=".$_SESSION['tktin_idtktin'].""; //waterac='".$tktacno."',
	
	mysql_query($sql_update);
	
	//set the message
	$msg_ticket_details="<div class=\"msg_success_small\">Changed Successfully</div>";
	} 


if ( (isset($_POST['update_location_details']))  && ($_POST['update_location_details']=="Save") ) //Save Location and Contact Details
	{
	//clean up
	$tktsender=preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_real_escape_string(trim($_POST['sendername'])));
	$tktsenderphone=preg_replace('/[^a-z\-_0-9\.:\/]/i','',mysql_real_escape_string(trim($_POST['senderphone'])));
	$tktsenderemail=preg_replace('/[^a-z\-_0-9\.:\/]/i','',mysql_real_escape_string(trim($_POST['senderemail'])));
	$tktstreet=preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_real_escape_string(trim($_POST['roadstreet'])));
	$tktbuilding=preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_real_escape_string(trim($_POST['building'])));
	$tktunitno=preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_real_escape_string(trim($_POST['unitnumber'])));
	$tktloc=mysql_real_escape_string(trim($_POST['locationtown']));
	$usrgender=mysql_real_escape_string($_POST['usrgender']);
	$directions=mysql_real_escape_string($_POST['directions']);
	$tktchannel=preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_real_escape_string(trim($_POST['tktchannel'])));
		
	//validate the process
	if (strlen($tktsender) < 2)
		{
		$error_details_1="Sender Name";
		}
	if (strlen($tktloc) < 1)
		{
		$error_details_2="Town / City";
		}
		
	//check if sender phone or at least sender email is set
	if (strlen($tktsenderphone)<12) 
		{
		$error_details_3_1=1;
		}
		
	if ( (strlen($tktsenderemail) > 5) && (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $tktsenderemail)) )
		{
		$error_details_3_2=1;
		}
		
	if ( ( (isset($error_details_3_1)) && (isset($error_details_3_2)) ) || ( (isset($error_details_3_1)) || (isset($error_details_3_2)) )  )
		{
		$error_details_3="Valid Mobile No. or eMail Address";
		}
		
	if (!isset($error_details_2))
			{	
			$sql_confirmloc="SELECT idloctowns,locationname FROM loctowns WHERE locationname='".$tktloc."' LIMIT 1";
			$res_confirmloc=mysql_query($sql_confirmloc);
			$num_confirmloc=mysql_num_rows($res_confirmloc);
			$fet_confirmloc=mysql_fetch_array($res_confirmloc);
			
			if ($num_confirmloc > 0) //if there is a location then
				{
				
				$location_id=$fet_confirmloc['idloctowns'];
				
				} else {
				//add new location
				$sql_newloc="INSERT INTO loctowns (loccountry_idloccountry,locationname,lng,lat,createdby,createdon,is_valid,is_town)
				VALUES ('1','".$tktloc."','0','0','".$_SESSION['WkAtToMrPa_idacname']."','".$timenowis."','1','0')";
				mysql_query($sql_newloc);
				
				//retreive that number
				$sql_idloc="SELECT idloctowns,locationname FROM loctowns WHERE createdby=".$_SESSION['WkAtToMrPa_idacname']." ORDER BY idloctowns DESC LIMIT 1"; 
				$res_idloc=mysql_query($sql_idloc);
				$fet_idloc=mysql_fetch_array($res_idloc);
				
				$location_id=$fet_idloc['idloctowns']; //thats the new location
				
				//just send email to support to map the new address
				
				
				//in such a case, the new location does not have the coordinates. Therefore, alert ICT team to add the new coordinates
				//the ict teams must be of that region
				//1. check if this region has a ICT_SUPPORT_MV role 
/*				$sql_checksupport="SELECT idusrrole,wfactors.wftskflow_idwftskflow,idusrac FROM usrrole 
				INNER JOIN wfactors ON usrrole.idusrrole=wfactors.usrrole_idusrrole
				INNER JOIN wftskflow ON wfactors.wftskflow_idwftskflow=wftskflow.idwftskflow
				INNER JOIN wfproc ON wftskflow.wfproc_idwfproc=wfproc.idwfproc
				INNER JOIN usrac ON usrrole.idusrrole=usrac.usrrole_idusrrole
				WHERE usrrole.usrrolename='ICT_SUPPORT_MV' AND wfproc.wftskflowname='ICT_SUPPORT_MV' AND usrrole.usrteamzone_idusrteamzone=".$_SESSION['WkAtToMrPa_userteamzoneid']." LIMIT 1";
				$res_checksupport=mysql_query($sql_checksupport);
				$num_checksupport=mysql_num_rows($res_checksupport);
				$fet_checksupport=mysql_fetch_array($res_checksupport);
				
				if ($num_checksupport > 0)
					{
					//2. then create a task for that role 
					$sql_gentracx="INSERT INTO wftaskstrac (createdon,createdby) VALUES ('".$timenowis."','".$_SESSION['WkAtToMrPa_idacname']."')";
					mysql_query($sql_gentracx);

					$sql_tracx="SELECT idwftaskstrac FROM wftaskstrac WHERE createdby=".$_SESSION['WkAtToMrPa_idacname']." ORDER BY idwftaskstrac DESC LIMIT 1";
					$res_tracx=mysql_query($sql_tracx);
					$fet_tracx=mysql_fetch_array($res_tracx);

															
					//insert new task for the recepeint
					$sql_new_taskx="INSERT INTO wftasks (wftaskstrac_idwftaskstrac,usrrole_idusrrole,wftasks_idwftasks,wftskflow_idwftskflow,tktin_idtktin,usrac_idusrac,wftskstatustypes_idwftskstatustypes,wftskstatusglobal_idwftskstatusglobal,tasksubject,taskdesc,timeinactual,timeoveralldeadline,timetatstart,timedeadline,timeactiontaken,sender_idusrrole,sender_idusrac,createdon,createdby) 
					VALUES ('".$fet_tracx['idwftaskstrac']."','".$fet_checksupport['iduserrole']."','0','".$fet_checksupport['wftskflow_idwftskflow']."','".$fet_tktid['idtktinPK']."','".$_SESSION['WkAtToMrPa_idacname']."','1','3','".$fet_tktid['tktcategoryname']." - ".$ticketref."','[MANUAL ENTRY]".$tktmsg."','".$tktdate_fin."','".$deadline."','".$task_starttime_final."','".$task_deadline_final."','".$timenowis."','2','2','".$timenowis."','".$_SESSION['WkAtToMrPa_idacname']."')";
					mysql_query($sql_new_taskx);
						
					//3. initiate the notifications as well for them to do the job of validating the new address
					
					}
*/
				}
			}
	
	if ( (!isset($error_details_1)) && (!isset($error_details_2)) && (!isset($error_details_3)) ) //if no error, then process this
		{
		$sql_update="UPDATE tktin SET 
		sendername='".$tktsender."',
		sendergender='".$usrgender."',
		senderemail='".$tktsenderemail."',
		senderphone='".$tktsenderphone."',
		city_town='".$tktloc."',
		loctowns_idloctowns='".$location_id."',
		road_street='".$tktstreet."',
		building_estate='".$tktbuilding."',
		unitno='".$tktunitno."',
		landmark='".$directions."'
		WHERE idtktinPK=".$_SESSION['tktin_idtktin']."";
		//echo $sql_update;
		mysql_query($sql_update);
		
		//msg_success
		$msg_location_details_ok="<div class=\"msg_success_small\">Location Details Saved Successfully</div>";
		} else {
		$msg_location_details_warn="Warning : Missing ";
		}
	
	} //Save

//form action
if ( (isset($_POST['formaction'])) && ($_POST['formaction']=="process_task") && (!isset($_POST['update_ticket_details'])) && (!isset($_POST['update_location_details'])) )
	{ //form action
	//now, sanitize the inputs
		$tktno=preg_replace('/[^a-z\-_0-9\.:\/]/i','',mysql_real_escape_string(trim($_SESSION['tktno'])));
		$tktcat=preg_replace('/[^a-z\-_0-9\.:\/]/i','',mysql_real_escape_string(trim($_SESSION['tktcat'])));
		$tktacno=preg_replace('/[^a-z\-_0-9\.:\/]/i','',mysql_real_escape_string(trim($_SESSION['tktacno'])));
		$tktkiosk=preg_replace('/[^a-z\-_0-9\.:\/]/i','',mysql_real_escape_string(trim($_SESSION['tktkiosk'])));
		$tktsender=preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_real_escape_string(trim($_SESSION['tktsender'])));
		$tktsenderphone=preg_replace('/[^a-z\-_0-9\.:\/]/i','',mysql_real_escape_string(trim($_SESSION['tktsenderphone'])));
		$tktsenderemail=preg_replace('/[^a-z\-_0-9\.:\/]/i','',mysql_real_escape_string(trim($_SESSION['tktsenderemail'])));
		$tktstreet=preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_real_escape_string(trim($_SESSION['tktstreet'])));
		$tktbuilding=preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_real_escape_string(trim($_SESSION['tktbuilding'])));
		$tktunitno=preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_real_escape_string(trim($_SESSION['tktunitno'])));
		$tktloc=mysql_real_escape_string(trim($_SESSION['tktloc']));
		$usrgender=mysql_real_escape_string($_SESSION['usrgender']);
		$directions=mysql_real_escape_string($_SESSION['directions']);
		$tktaction=preg_replace('/[^a-z\-_0-9\.:\/]/i','',mysql_real_escape_string(trim($_POST['action_to'])));
		$updateperm=preg_replace('/[^a-z\-_0-9\.:\/]/i','',mysql_real_escape_string(trim($_POST['up'])));

		//echo $tktno."+".$tktcat."+".$tktacno."+".$tktkiosk."+".$tktsender."+".$tktsenderphone."+".$tktsenderemail."+".$tktstreet."+".$tktbuilding."+".$tktunitno."+".$tktloc."+".$usrgender."+".$directions."+".$tktaction."+".$updateperm;
		//exit;
		//generate SMS if closed ticket / task
		/*if ($tktaction==1)
			{
			//retrieve the message composed for this kind of ticket
			//$tktsms_raw=$_SESSION['WkAtToMrPa_acteamshrtname']." [ Tkt ".$tktno."-".$fet_ticketcat['tktcategoryname']."] Dear Customer, the matter has been resolved.Satisfied? Call ".$_SESSION['WkAtToMrPa_acteamshrtname']." FREE on 0800720018";
			$tktsms_raw=$_SESSION['WkAtToMrPa_acteamshrtname']." [".$tktno."] This matter has been resolved. Satisfied? For further assistance, call us on 0721757382";
			$tktsms=substr($tktsms_raw,0,160);
			} else {
				if (isset($_POST['txtsms']))
					{
					$tktsms_raw=" ".preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_real_escape_string(trim($_POST['txtsms'])));
					$tktsms=substr($tktsms_raw,0,160);
					}
			}*/
			
		if (strlen($_POST['txtsms1'])>15) 
			{
			$tktsms_raw=" ".preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_real_escape_string(trim($_POST['txtsms1'])));
			$tktsms=substr($tktsms_raw,0,160);
			$tktsms_record="<br>[[[".$tktsms."]]]"; //this is the sms inserted on the history
			} else if (strlen($_POST['txtsms2'])>15) {
			$tktsms_raw=" ".preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_real_escape_string(trim($_POST['txtsms2'])));
			$tktsms=substr($tktsms_raw,0,160);
			$tktsms_record="<br>[[[".$tktsms."]]]"; //this is the sms inserted on the history
			} else if (strlen($_POST['txtsms4'])>15) {
			$tktsms_raw=" ".preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_real_escape_string(trim($_POST['txtsms4'])));
			$tktsms=substr($tktsms_raw,0,160);
			$tktsms_record="<br>[[[".$tktsms."]]]"; //this is the sms inserted on the history
			} else if (strlen($_POST['txtsms6'])>15) {
			$tktsms_raw=" ".preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_real_escape_string(trim($_POST['txtsms6'])));
			$tktsms=substr($tktsms_raw,0,160);
			$tktsms_record="<br>[[[".$tktsms."]]]"; //this is the sms inserted on the history
			}
			

		//clean up optional fields
		if (isset($_POST['close_1']))
			{
			$tkticonfirm=preg_replace('/[^a-z\-_0-9\.:\/]/i','',mysql_real_escape_string(trim($_POST['close_1'])));
			}
		if (isset($_POST['task_msg_1']))
			{
			$tkttskmsg1=preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_real_escape_string(strip_tags(trim($_POST['task_msg_1']))));
			}
		if (isset($_POST['task_msg_2']))
			{
			$tkttskmsg2=preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_real_escape_string(strip_tags(trim($_POST['task_msg_2']))));
			}
		if (isset($_POST['task_msg_3']))
			{
			$tkttskmsg3=preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_real_escape_string(strip_tags(trim($_POST['task_msg_3']))));
			}
		if (isset($_POST['task_msg_4']))
			{
			$tkttskmsg4=preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_real_escape_string(strip_tags(trim($_POST['task_msg_4']))));
			}
		if (isset($_POST['task_msg_5']))
			{
			$tkttskmsg5=preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_real_escape_string(strip_tags(trim($_POST['task_msg_5']))));
			}
		if (isset($_POST['task_msg_6']))
			{
			$tkttskmsg6=preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_real_escape_string(strip_tags(trim($_POST['task_msg_6']))));
			}
		if (isset($_POST['task_msg_8']))
			{
			$tkttskmsg8=preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_real_escape_string(strip_tags(trim($_POST['task_msg_8']))));
			}	
		if (isset($_POST['task_msg_9']))
			{
			$tkttskmsg9=preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_real_escape_string(strip_tags(trim($_POST['task_msg_9']))));
			}
		if (isset($_POST['assign_to_9']))
			{
			$tktasito9=preg_replace('/[^a-z\-_0-9\.:\/]/i','',mysql_real_escape_string(trim($_POST['assign_to_9'])));
			}	
		if (isset($_POST['assign_to_2']))
			{
			$tktasito2=preg_replace('/[^a-z\-_0-9\.:\/]/i','',mysql_real_escape_string(trim($_POST['assign_to_2'])));
			}
						
		if (isset($_POST['assign_to_8']))
			{
			$tktasito8=preg_replace('/[^a-z\-_0-9\.:\/]/i','',mysql_real_escape_string(trim($_POST['assign_to_8'])));
			}
		if (isset($_POST['assign_to_3']))
			{
			$tktasito3=preg_replace('/[^a-z\-_0-9\.:\/]/i','',mysql_real_escape_string(trim($_POST['assign_to_3'])));
			}
		if (isset($_POST['assign_to_5']))
			{
			$tktasito5=preg_replace('/[^a-z\-_0-9\.:\/]/i','',mysql_real_escape_string(trim($_POST['assign_to_5'])));
			}
		if (isset($_POST['invalid_id']))
			{
			$tktinvalidid=preg_replace('/[^a-z\-_0-9\.:\/]/i','',mysql_real_escape_string(trim($_POST['invalid_id'])));
			}
		if (isset($_POST['add_reason']))
			{
			$tktinvalidnew=preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_real_escape_string(trim($_POST['add_reason'])));
			}
		if (isset($_POST['senderemail']))
			{
			$tktsenderemail=preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_real_escape_string(trim($_POST['senderemail'])));
			}
		if (isset($_POST['newdeadline']))
			{
			$tktnewdeadline=preg_replace('/[^a-z\-_0-9\.:\/\s]/i','',mysql_real_escape_string(trim($_POST['newdeadline'])));
			$tktnewdeadline_trans=str_replace('/','-',$tktnewdeadline);

			$tktnewdeadline_fin=date("Y-m-d H:i:s",strtotime($tktnewdeadline_trans));
			}
		
		if (isset($_POST['batch_no']))
			{
				if ($_POST['batch_no'] >0)
					{
					$batch_no=mysql_real_escape_string(trim($_POST['batch_no']));
					} else {
					$batch_no=0;
					}
			} else {
			$batch_no=0;
			}

		// let's validate that all the fields have been entered
		if ($tktcat < 1)
			{
			$error_1="<div class=\"msg_warning_small\">".$msg_warning_nocategory."</div>";
			}
		if (strlen($tktloc) < 1)
			{
			$error_2="<div class=\"msg_warning_small\">".$msg_warning_location."</div>";
			}
		$sql_confirmloc="SELECT idloctowns,locationname FROM loctowns WHERE locationname='".$tktloc."' LIMIT 1";
//		echo $sql_confirmloc;
		$res_confirmloc=mysql_query($sql_confirmloc);
		$num_confirmloc=mysql_num_rows($res_confirmloc);
		$fet_confirmloc=mysql_fetch_array($res_confirmloc);
		
		if ($num_confirmloc < 1)
			{
			$error_3="<div class=\"msg_warning_small\">".$msg_warning_invalidloc."</div>";
			}
		if ($tktaction < 1)
			{
			$error_4="<div class=\"msg_warning_small\">".$msg_select_action."</div>";
			}
			
			if ( (!isset($error_1)) && (!isset($error_2)) && (!isset($error_3))  && (!isset($error_4)) )
				{ //if no error set
				
				$sql_idtask="SELECT wftaskstrac_idwftaskstrac FROM wftasks WHERE idwftasks=".$_SESSION['wtaskid']." ORDER BY idwftasks DESC LIMIT 1";
				$res_idtask=mysql_query($sql_idtask);
				$fet_idtask=mysql_fetch_array($res_idtask);
				
///////////////  ACTION 1  ///////////////////////////////////////////////////////////////////////////////////////////////////
		
				//option 1 = Close Task as per DB key
				if ($tktaction==1) //Select Task Action 1
					{
					
					//validate
					if (strlen($tkttskmsg1) < 1)
						{
						$error_1_1="<div class=\"msg_warning_small\">".$msg_warn_msgmis."</div>";
						}
					if ((!isset($tkticonfirm)) || ($tkticonfirm!=1))
						{
						$error_1_2="<div class=\"msg_warning_small\">".$msg_warn_confirm."</div>";
						}
						
					if ( (!isset($error_1_1)) && (!isset($error_1_2)) )//if the no error 
						{	//if the no error 1_1
						
						mysql_query("BEGIN");
						
						//create an update message on the record
						$sql_update_msg="INSERT INTO wftskupdates (wftaskstrac_idwftaskstrac,usrrole_idusrrole,usrac_idusrac,wftskstatusglobal_idwftskstatusglobal,wftskstatustypes_idwftskstatustypes,wftasks_idwftasks,wftskupdate,createdby,createdon) 
						VALUES ('".$fet_idtask['wftaskstrac_idwftaskstrac']."','".$_SESSION['WkAtToMrPa_iduserrole']."','".$_SESSION['WkAtToMrPa_idacname']."','3','1','".$_SESSION['wtaskid']."','".$tkttskmsg1.$tktsms_record."','".$_SESSION['WkAtToMrPa_idacname']."','".$timenowis."')";
						$query_1=mysql_query($sql_update_msg);
						
						
						//check if there is a form data and if so, go ahead and process this transaction with inserts or updates
						if ( (isset($_POST['formdata_available'])) && ($_POST['formdata_available']==1) )
							{ //go ahead process
							//echo "processed <br>";
							//check the db for this field by reusing the sql statement above
							/*
							$sql_val="SELECT idwfprocassetsaccess,assetname,perm_read,perm_write,perm_required,wfprocassets.wfprocdtype_idwfprocdtype,idwfprocassets FROM wfprocassetsaccess 
							INNER JOIN wfprocassets ON wfprocassetsaccess.wfprocassets_idwfprocassets=wfprocassets.idwfprocassets
							WHERE wftskflow_idwftskflow=".$fet_task['wftskflow_idwftskflow']." AND wfprocassetsaccess.perm_read=1 ORDER BY wfprocassets.ordering ASC";
							*/
							$sql_val="SELECT idwfprocassetsaccess,assetname,perm_read,perm_write,perm_required,wfprocassets.wfprocdtype_idwfprocdtype,idwfprocassets,wfprocassetsgrouplbl,sysprofiles_idsysprofiles,wfprocassetsaccess.wfprocforms_idwfprocforms
							FROM wfprocassetsaccess
							INNER JOIN wfprocassets ON wfprocassetsaccess.wfprocassets_idwfprocassets = wfprocassets.idwfprocassets
							INNER JOIN wfprocassetsgroup ON wfprocassets.wfprocassetsgroup_idwfprocassetsgroup = wfprocassetsgroup.idwfprocassetsgroup
							INNER JOIN wfprocforms_cats ON wfprocassetsaccess.wfprocforms_idwfprocforms = wfprocforms_cats.wfprocforms_idwfprocforms 
							WHERE sysprofiles_idsysprofiles=".$_SESSION['WkAtToMrPa_iduserprofile']." AND wfprocforms_cats.tktcategory_idtktcategory=".$fet_ticket['tktcategory_idtktcategory']." AND wfprocassetsaccess.perm_read=1 ORDER BY wfprocassetsgrouplbl ASC,ordering ASC";
//echo $sql_val;
							$res_val=mysql_query($sql_val);
							$num_val=mysql_num_rows($res_val);
							$fet_val=mysql_fetch_array($res_val);
							
							
							if ($num_val > 0) //if there are some values, then
								{
								do {
								//master-checklist if  | it is required | there is a value | the data type to determine the field |  whether an update or insert
								
								//validate required
							//	echo "validation ";
							//	echo $_POST['required_'.$fet_val['idwfprocassetsaccess'].'']."<br>";
							//	echo $_POST['item_'.$fet_val['idwfprocassetsaccess'].''];
								if ( (isset($_POST['required_'.$fet_val['idwfprocassetsaccess'].''])) && ($_POST['required_'.$fet_val['idwfprocassetsaccess'].'']==1) &&  ($_POST['item_'.$fet_val['idwfprocassetsaccess'].'']=="") )
									{
									//echo $_POST['item_'.$fet_val['idwfprocassetsaccess'].'']."<br>";
									$error_formdata=1;									
									echo "<div class=\"msg_warning_small\">Form: ".$fet_val['assetname']." is required | <a href=\"mytasks_history.php\">Back to Task View</a></div>";				
									}
									
								//if no error on the dataform, then process
								if (!isset($error_formdata))
									{	
									if ($_POST['transtype_'.$fet_val['idwfprocassetsaccess'].'']=="INSERT")
										{
										//check the form item type first
										$ttype=$_POST['itemtype_'.$fet_val['idwfprocassetsaccess'].''];
										
											
											if (($ttype==1) || ($ttype==4) || ($ttype==5) || ($ttype==6) || ($ttype==7) || ($ttype==8) || ($ttype==9) || ($ttype==10)   ) //if textbox OR yes/no OR datepicker OR datetimepicker
												{
												$fvalue=mysql_real_escape_string(trim($_POST['item_'.$fet_val['idwfprocassetsaccess'].'']));
												
												//then process as below
												$sql_insert="INSERT INTO wfassetsdata (wfprocassetsaccess_idwfprocassetsaccess,
												wfprocassetschoice_idwfprocassetschoice,
												wfprocassets_idwfprocassets,
												wftasks_idwftasks,
												value_choice,
												value_path,
												wftaskstrac_idwftaskstrac,
												tktin_idtktin,
												createdby,
												createdon)
												VALUES ('".$fet_val['idwfprocassetsaccess']."',
												'0',
												'".$fet_val['idwfprocassets']."',
												'".$_SESSION['wtaskid']."',
												'".$fvalue."',
												'',
												'".$_SESSION['wftaskstrac']."',
												'".$_SESSION['tktin_idtktin']."',
												'".$_SESSION['WkAtToMrPa_idacname']."',
												'".$timenowis."'
												)";
												
												mysql_query($sql_insert);
												//echo $sql_insert;
												//exit;
												}
												
											if ($ttype==2)//if menulist
												{
												$fvalue=mysql_real_escape_string(trim($_POST['item_'.$fet_val['idwfprocassetsaccess'].'']));
												
												$sql_insert="INSERT INTO wfassetsdata (wfprocassetsaccess_idwfprocassetsaccess,
												wfprocassetschoice_idwfprocassetschoice,
												wfprocassets_idwfprocassets,
												wftasks_idwftasks,
												value_choice,
												value_path,
												wftaskstrac_idwftaskstrac,
												tktin_idtktin,
												createdby,
												createdon)
												VALUES ('".$fet_val['idwfprocassetsaccess']."',
												'".$fvalue."',
												'".$fet_val['idwfprocassets']."',
												'".$_SESSION['wtaskid']."',
												'',
												'',
												'".$_SESSION['wftaskstrac']."',
												'".$_SESSION['tktin_idtktin']."',
												'".$_SESSION['WkAtToMrPa_idacname']."',
												'".$timenowis."'
												)";
												
												mysql_query($sql_insert);
												
												}
												
										if ( ($ttype==3) && (isset($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])) && (strlen($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])>4) )//if file upload".$_SESSION['tktin_idtktin']."
											{
											$fvalue_upload=basename($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"]);
											$target_dir = "../documents/task_docs/".$today."/";
//											$docname=$_SESSION['tktin_idtktin']."_".basename($_FILES["fileToUpload"]["name"]);
											$docname=$_SESSION['tktin_idtktin']."_".$fvalue_upload;
											//we need to seed the document to make it unique_
											//lets include the ticket_ref number of the task to the name of the file
											$target_file = $target_dir . $docname;
											$uploadOk = 1;
											//just keep the file name only
											$file_name_only=$_SESSION['tktin_idtktin']."_".$_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"];
											$file_size_only=$_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["size"];
											//check if there is any document before proceeding
											if (strlen($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])>4)
												{
												
												$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
												
												//validation before uploading										 
												//check if file exists
												if (file_exists($target_file)) 
													{
													$upload_error_1 = "<div class=\"msg_warning_small\">File Missing</div>";
													}
												
												if ($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["size"] > 10485760) 
													{
													$upload_error_2 = "<div class=\"msg_warning_small\">File Max Size Exceeded( 10 MB)</div>";
													}
												
												if	($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
													&& $imageFileType != "gif" && $imageFileType != "doc" && $imageFileType != "docx" 
													&& $imageFileType != "pdf" && $imageFileType != "xls" && $imageFileType != "xlsx" 
													&& $imageFileType != "ppt" && $imageFileType != "pptx"  && $imageFileType != "csv"    ) {
														
													$upload_error_3 = "<div class=\"msg_warning_small\">Sorry, file format [".$imageFileType."] not allowed</div>";
													}
												//echo $upload_error_1.$upload_error_2.$upload_error_3;	
												//echo "Size -->".$_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["size"];
												if ( (!isset($upload_error_1)) && (!isset($upload_error_2)) && (!isset($upload_error_3)) )
													{
													 if (move_uploaded_file($_FILES['item_'.$fet_val['idwfprocassetsaccess'].'']["tmp_name"], $target_file)) 
														{
														$upload_success=1;
														//log the record into the Database
														$sql_insert="INSERT INTO wfassetsdata (wfprocassetsaccess_idwfprocassetsaccess,
														wfprocassetschoice_idwfprocassetschoice,
														wfprocassets_idwfprocassets,
														wftasks_idwftasks,
														value_choice,
														value_path,
														wftaskstrac_idwftaskstrac,
														tktin_idtktin,
														createdby,
														createdon)
														VALUES ('".$fet_val['idwfprocassetsaccess']."',
														'0',
														'".$fet_val['idwfprocassets']."',
														'".$_SESSION['wtaskid']."',
														'',
														'".$file_name_only."',
														'".$_SESSION['wftaskstrac']."',
														'".$_SESSION['tktin_idtktin']."',
														'".$_SESSION['WkAtToMrPa_idacname']."',
														'".$timenowis."'
														)";
		
														mysql_query($sql_insert);
														
														//create the audit log
														$sql_audit="INSERT INTO audit_docuploads ( doc_name, doc_ext, doc_size, tktin_idtktin, createdon, createdby, usersess, usrip, wfprocassets_idwfprocassets) 
														VALUES ('".$file_name_only."', '".$imageFileType."', '".$file_size_only."', '".$_SESSION['tktin_idtktin']."', '".$timenowis."', '".$_SESSION['WkAtToMrPa_idacname']."', '".session_id()."', '".$_SERVER['REMOTE_ADDR']."','".$fet_val['idwfprocassets']."')";
														mysql_query($sql_audit);
														
														} else {
															$upload_error_4 = "<div class=\"msg_warning_small\">Sorry, we are unable to upload that file</div>";
														}
													} //no error
												} //where strlen > 4
											} //type==3
										
										} //close INSERT
										
										
										
									if ($_POST['transtype_'.$fet_val['idwfprocassetsaccess'].'']=="UPDATE")
									{
									$ttype=$_POST['itemtype_'.$fet_val['idwfprocassetsaccess'].''];
									$itempk=mysql_real_escape_string(trim($_POST['itempk_'.$fet_val['idwfprocassetsaccess'].'']));
									
									//value captured - //this hack for checkbox
									if ((($ttype==3)||($ttype==4)) && (!isset($_POST['item_'.$fet_val['idwfprocassetsaccess'].''])) )
										{
										$fvalue=0;
										} else {
										$fvalue=mysql_real_escape_string(trim($_POST['item_'.$fet_val['idwfprocassetsaccess'].'']));
										}
									
									//only if there are records
									if (
										( ($fvalue > 0) || (strlen($fvalue) > 0) && ($ttype!=4) ) 
										|| 
										( ($ttype==4) && (($fvalue=='') || ($fvalue==0) || ($fvalue!=0)) ) 
										) 
									/*if ( ($fvalue!='') && (strlen($fvalue) > 0) )*/
										{
										//check the form item type first
										if (($ttype==1) || ($ttype==4)  || ($ttype==5) || ($ttype==6) || ($ttype==7)  || ($ttype==8) || ($ttype==9) || ($ttype==10)  ) //if textbox OR yes/no OR datepicker OR datetimepicker
												{
												///audit log
												$sql_auditlog_form="INSERT INTO audit_wfassetsdata (idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice_prev, wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice_prev, value_choice_new, value_path_prev, value_path_new, wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon, modifiedby_new, modifiedon_new) 
												SELECT idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice, wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice, '".$fvalue."', value_path,'', wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon,".$_SESSION['WkAtToMrPa_idacname'].",'".$timenowis."' 
												FROM wfassetsdata
												WHERE idwfassetsdata=".$itempk." AND value_choice!='".$fvalue."' ";
												//echo $sql_auditlog_form."<br>";
												mysql_query($sql_auditlog_form);
												
												//then process as below
												$sql_update="UPDATE wfassetsdata SET 
												value_choice='".$fvalue."',
												wftaskstrac_idwftaskstrac='".$_SESSION['wftaskstrac']."',
												tktin_idtktin='".$_SESSION['tktin_idtktin']."',
												modifiedby='".$_SESSION['WkAtToMrPa_idacname']."',
												modifiedon='".$timenowis."'
												WHERE idwfassetsdata=".$itempk." LIMIT 1";
												
												mysql_query($sql_update);
												//echo $sql_update;
												}
										
										if ($ttype==2)//if menulist
												{
												//enter the audit trail only if there is a change
												$sql_auditlog_form="INSERT INTO audit_wfassetsdata (idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice_prev, wfprocassetschoice_idwfprocassetschoice_new, wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice_prev, value_choice_new, value_path_prev, value_path_new, wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon, modifiedby_new, modifiedon_new) 
												SELECT idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice,'".$fvalue."', wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice, '', value_path,'', wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon,".$_SESSION['WkAtToMrPa_idacname'].",'".$timenowis."' 
												FROM wfassetsdata
												WHERE idwfassetsdata=".$itempk." AND wfprocassetschoice_idwfprocassetschoice!='".$fvalue."' ";
												//echo $sql_auditlog_form."<br>";
												mysql_query($sql_auditlog_form);
												
												$sql_update="UPDATE wfassetsdata SET 
												wfprocassetschoice_idwfprocassetschoice='".$fvalue."',
												wftaskstrac_idwftaskstrac='".$_SESSION['wftaskstrac']."',
												tktin_idtktin='".$_SESSION['tktin_idtktin']."',
												modifiedby='".$_SESSION['WkAtToMrPa_idacname']."',
												modifiedon='".$timenowis."'
												WHERE idwfassetsdata=".$itempk." LIMIT 1";
												
												mysql_query($sql_update);
												}
										
										if ( ($ttype==3) && (isset($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])) && (strlen($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])>4) )//if file upload".$_SESSION['tktin_idtktin']."
											{
											$fvalue_upload=basename($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"]);
											$target_dir = "../documents/task_docs/".$today."/";
//											$docname=$_SESSION['tktin_idtktin']."_".basename($_FILES["fileToUpload"]["name"]);
											$docname=$_SESSION['tktin_idtktin']."_".$fvalue_upload;
											//we need to seed the document to make it unique_
											//lets include the ticket_ref number of the task to the name of the file
											$target_file = $target_dir . $docname;
											$uploadOk = 1;
											$file_name_only=$_SESSION['tktin_idtktin']."_".$_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"];
											$file_size_only=$_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["size"];

											////check if there is any document before proceeding
											if (strlen($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])>4)
												{
											
													$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
													
													//validation before uploading											 
													//check if file exists
													if (file_exists($target_file)) 
														{
														$upload_error_1 = "<div class=\"msg_warning_small\">File Missing</div>";
														}
													
													if ($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["size"] > 10485760) 
														{
														$upload_error_2 = "<div class=\"msg_warning_small\">File Max Size Exceeded( 10 MB)</div>";
														}
													
													if	($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
														&& $imageFileType != "gif" && $imageFileType != "doc" && $imageFileType != "docx" 
														&& $imageFileType != "pdf" && $imageFileType != "xls" && $imageFileType != "xlsx" 
														&& $imageFileType != "ppt" && $imageFileType != "pptx" && $imageFileType != "csv"    ) {
															
														$upload_error_3 = "<div class=\"msg_warning_small\">Sorry, file format [".$imageFileType."] not allowed</div>";
														}

													if ( (!isset($upload_error_1)) && (!isset($upload_error_2)) && (!isset($upload_error_3)) )
														{
														//echo "soo farr soo good";
														 if (move_uploaded_file($_FILES['item_'.$fet_val['idwfprocassetsaccess'].'']["tmp_name"], $target_file)) 
															{
															$upload_success=1;
															
															$sql_auditlog_form="INSERT INTO audit_wfassetsdata (idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice_prev, wfprocassetschoice_idwfprocassetschoice_new, wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice_prev, value_choice_new, value_path_prev, value_path_new, wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon, modifiedby_new, modifiedon_new) 
															SELECT idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice,'', wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice, '', value_path,'".$file_name_only."', wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon,".$_SESSION['WkAtToMrPa_idacname'].",'".$timenowis."' 
															FROM wfassetsdata
															WHERE idwfassetsdata=".$itempk." AND value_path!='".$file_name_only."' ";
															//echo $sql_auditlog_form."<br>";
															mysql_query($sql_auditlog_form);
															
															//log the record into the Database
															$sql_update="UPDATE wfassetsdata SET 
															value_path='".$file_name_only."',
															modifiedby='".$_SESSION['WkAtToMrPa_idacname']."',
															modifiedon='".$timenowis."'
															WHERE idwfassetsdata=".$itempk." LIMIT 1";

															mysql_query($sql_update);
															
															//create the audit log
															$sql_audit="INSERT INTO audit_docuploads ( doc_name, doc_ext, doc_size, tktin_idtktin, createdon, createdby, usersess, usrip,wfprocassets_idwfprocassets) 
															VALUES ('".$file_name_only."', '".$imageFileType."', '".$file_size_only."', '".$_SESSION['tktin_idtktin']."', '".$timenowis."', '".$_SESSION['WkAtToMrPa_idacname']."', '".session_id()."', '".$_SERVER['REMOTE_ADDR']."',".$itempk.")";
															mysql_query($sql_audit);
																											
															} else {
																$upload_error_4 = "<div class=\"msg_warning_small\">Sorry, we are unable to upload that file</div>";
															}
														} //no error
													} // if fvalue strlen>4
											
												} //type==3				
											}
											
											} //close UPDATE
										
										} //close form data error
									
									} while ($fet_val=mysql_fetch_array($res_val)); //close WHILE
							
								} //if record is > 0 // data checker
								
							} //form data available
							
							//Close this Task
							$sql_update_task="UPDATE wftasks SET wftskstatustypes_idwftskstatustypes='1',wftskstatusglobal_idwftskstatusglobal='3',timeactiontaken='".$timenowis."',actedon_idusrrole=".$_SESSION['WkAtToMrPa_iduserrole'].", actedon_idusrac='".$_SESSION['WkAtToMrPa_idacname']."' WHERE idwftasks=".$_SESSION['wtaskid']." LIMIT 1";
							$query_2=mysql_query($sql_update_task);
							
							//Feedback SMS to send customer/sender a message
							if ( (isset($tktsms)) && (strlen($tktsms)>15) && (strlen($tktsenderphone)==12) )
								{
								$sql_smsout="INSERT INTO mdata_out_sms (destnumber,msgtext) 
								VALUES ('".$tktsenderphone."','".$tktno." ".$tktsms."')";
								mysql_query($sql_smsout);
								}
								
							//Update the ticket status
						
								
								$sql_updatetkt="UPDATE tktin SET 
								tktstatus_idtktstatus='4',
								timeclosed='".$timenowis."'
								WHERE idtktinPK=".$_SESSION['tktupdate']." 
								LIMIT 1";								
								$query_3=mysql_query($sql_updatetkt);
								
								
							
								//notify if anyone is to be notified
								$sql_notify="SELECT idwfnotification,wfnotification.tktstatus_idtktstatus,usrrole_idusrrole,wftskflow_idwftskflow,notify_system,notify_email,notify_sms,idtktmsgs,tktmsg_sms,tktmsg_email,tktmsg_dashboard FROM wfnotification 
								INNER JOIN tktmsgs ON wfnotification.idwfnotification=tktmsgs.wfnotification_idwfnotification
								WHERE wftskflow_idwftskflow=".$_SESSION['thistskflow']." ORDER BY idwfnotification ASC";
								$res_notify=mysql_query($sql_notify);
								$num_notify=mysql_num_rows($res_notify);
								$fet_notify=mysql_fetch_array($res_notify);
					
								if ($num_notify > 0 ) // if there is a notification setting
									{
										do {			
										//check for each of the settings 
											if ( ($fet_notify['notify_system']==1) && (strlen($fet_notify['tktmsg_dashboard'])>2) ) //system dashboard set on
												{
												$sql_dash="INSERT INTO tktmsglogs_dashboard (tktmsgs_idtktmsgs,msgto_roleid,msgto_subject,msgto_body,createdon,readon)
												VALUES ('".$fet_notify['idtktmsgs']."','".$fet_notify['usrrole_idusrrole']."','Notification - Tkt No : ".$tktno.", ".$fet_ticketcat['tktcategoryname'].",'".$fet_notify['tktmsg_dashboard']." - ".$fet_ticketcat['tktcategoryname']."','".$timenowis."','0000-00-00 00:00:00')";
												mysql_query($sql_dash);									
												}// system dashboard set on
											
												//get this roles email address and phone numbers
												//ensure the account is active as well...
												$sql_rolecontacts="SELECT usremail,usrphone FROM usrac WHERE usrrole_idusrrole=".$fet_notify['usrrole_idusrrole']." AND acstatus=1 LIMIT 1";
												$res_rolecontacts=mysql_query($sql_rolecontacts);
												$fet_rolecontacts=mysql_fetch_array($res_rolecontacts);
												$num_rolecontacts=mysql_num_rows($res_rolecontacts);
									
											if ( ($fet_notify['notify_email']==1) && ($num_rolecontacts>0) && (strlen($fet_rolecontacts['usremail'])>6) && (strlen($fet_notify['tktmsg_email'])>2) )//email set on
												{
												$sql_email="INSERT INTO tktmsgslog_emails(tktmsgs_idtktmsgs,emailto,emailsubject,emailbody,createdon,senton) 
												VALUES ('".$fet_notify['idtktmsgs']."','".$fet_rolecontacts['usremail']."','Notification - Tkt No : ".$tktno.", ".$fet_ticketcat['tktcategoryname']."','".$fet_notify['tktmsg_email']." - Tkt No:".$tktno.", ".$fet_ticketcat['tktcategoryname']."','".$timenowis."','0000-00-00 00:00:00')";
											
												mysql_query($sql_email);
												}
										
											if ( ($fet_notify['notify_sms']==1) && ($num_rolecontacts>0) && (strlen($fet_rolecontacts['usrphone'])==13) )
												{
												$sql_sms="INSERT INTO mdata_out_sms (destnumber,msgtext)
												VALUES ('".$fet_rolecontacts['usrphone']."',' Auto Notification - Tkt No:".$tktno.", ".$fet_ticketcat['tktcategoryname']." received')";
						
												mysql_query($sql_sms);
												}
											
											} while ($fet_notify=mysql_fetch_array($res_notify));								
									
										} //close - if there is a notification setting
										
										
										/////////////////////////////check and insert a new subscriber
										if ($fet_task['usrrole_idusrrole']==2) //if this is the first ticket from the customer [customer is reserved as userrole 2], then do this...
											{
											//check if a subscriber with the same credentials matches
											$sql_subis="SELECT idsmssubs FROM ".$_SESSION['WkAtToMrPa_tblsmsbc']." WHERE subnumber='".$tktsenderphone."' AND usrtype=1";
											$res_subis=mysql_query($sql_subis);
											$num_subis=mysql_num_rows($res_subis);
											
											//if not, add the new credentials
											if ($num_subis==0)
												{
												$sql_subnew="INSERT INTO ".$_SESSION['WkAtToMrPa_tblsmsbc']." (wftskid,tktid,subnumber,idloctown,idusrteamzone,usrtype,createdon,createdby)
												VALUES ('".$_SESSION['wtaskid']."','".$fet_task['tktin_idtktin']."','".$fet_confirmloc['idloctowns']."','".$_SESSION['WkAtToMrPa_userteamzoneid']."','1','".$timenowis."','".$_SESSION['WkAtToMrPa_idacname']."')";
												mysql_query($sql_subnew);
												}
											}
											
								
								if ( (isset($batch_no)) && ($batch_no>0) && ($fet_ticket['wftasks_batch_idwftasks_batch']!=$batch_no) )
										{
									//first, lets check if this ticket already belonged to another batch before removing it
									$res_tktin=mysql_query("SELECT idtktinPK,wftasks_batch_idwftasks_batch,tktcategory_idtktcategory FROM tktin WHERE idtktinPK=".$fet_ticket['idtktinPK']."  ");
									$fet_tktin=mysql_fetch_array($res_tktin);
											
									if ($fet_tktin['wftasks_batch_idwftasks_batch']>0)
										{
										//update the tkt as well
										$sql_batchtkt="UPDATE tktin SET 
										wftasks_batch_idwftasks_batch='0',
										batch_number='0',
										voucher_number='0'
										WHERE idtktinPK=".$fet_ticket['idtktinPK']."";
										
										//update the countbatch
										$sql_updatecount_old="UPDATE wftasks_batch SET countbatch=(countbatch-1) WHERE idwftasks_batch=".$fet_tktin['wftasks_batch_idwftasks_batch']."";
													
										//log audit 1
										$sql_audit1="INSERT INTO audit_wftasks_batch (action, actionby_idusrac, actionby_idusrrole, tktin_affected, batchid_old, batchid_new, result, browser_session, action_time, user_ip, user_ip_proxy) 
										VALUES ( 'MOVE', '".$_SESSION['WkAtToMrPa_idacname']."', '".$_SESSION['WkAtToMrPa_iduserrole']."', '".$fet_ticket['idtktinPK']."', '".$fet_tktin['wftasks_batch_idwftasks_batch']."', '".$batch_no."', 'OK', '".session_id()."', '".$timenowis."', '".$_SERVER['REMOTE_ADDR']."', '".$realip."')";
													
										} else {
										
										$sql_batchtkt="SELECT idtktinPK from tktin LIMIT 1";
										$sql_updatecount_old="SELECT idtktinPK from tktin LIMIT 1";
										
										//log audit 1
										$sql_audit1="INSERT INTO audit_wftasks_batch (action, actionby_idusrac, actionby_idusrrole, tktin_affected, batchid_old, batchid_new, result, browser_session, action_time, user_ip, user_ip_proxy) 
										VALUES ( 'NEW', '".$_SESSION['WkAtToMrPa_idacname']."', '".$_SESSION['WkAtToMrPa_iduserrole']."', '".$fet_ticket['idtktinPK']."', '0', '".$batch_no."', 'OK', '".session_id()."', '".$timenowis."', '".$_SERVER['REMOTE_ADDR']."', '".$realip."')";
										
										}
											
									$res_batchtkt=mysql_query($sql_batchtkt);
									$res_updatecount_old=mysql_query($sql_updatecount_old);
									$res_audit1=mysql_query($sql_audit1);
							
									//check the last batch_no
									$res_batchmeta=mysql_query("SELECT usrteamzone_idusrteamzone,wftasks_batchtype_idwftasks_batchtype FROM wftasks_batch WHERE idwftasks_batch=".$batch_no."");
									$fet_batchmeta=mysql_fetch_array($res_batchmeta);
									//changed to get the last max id given for this batch
						//			$sql_lastbatchno="SELECT max(voucher_number) as countbatch FROM tktin WHERE wftasks_batch_idwftasks_batch=".$batch_no."";
									$sql_lastbatchno="SELECT max(tktin.voucher_number) as countbatch,wftasks_batch.wftasks_batchtype_idwftasks_batchtype,wftasks_batch.batch_year FROM tktin
									INNER JOIN wftasks_batch ON tktin.wftasks_batch_idwftasks_batch=wftasks_batch.idwftasks_batch
									WHERE wftasks_batch.wftasks_batchtype_idwftasks_batchtype=".$fet_batchmeta['wftasks_batchtype_idwftasks_batchtype']."
									AND wftasks_batch.usrteamzone_idusrteamzone=".$fet_batchmeta['usrteamzone_idusrteamzone']."
									AND wftasks_batch.usrteamzone_idusrteamzone=".$_SESSION['WkAtToMrPa_userteamzoneid']."  ";//AND YEAR(createdon)='".$this_year."'
									$res_lastbatchno=mysql_query($sql_lastbatchno);
									$fet_lastbatchno=mysql_fetch_array($res_lastbatchno);
									
									//quick validation to avoid crossing over to another year in an older batch
									if ( ($fet_lastbatchno['countbatch']!='') && ($fet_lastbatchno['batch_year']!=$this_year) )
										{
										$error_batchoutdated="<div style=\"color:#ff0000\">You can't assign ".$fet_lastbatchno['batch_year']." in ".$this_year."  </div>";
										//exit;
										}
									
									//create the new batch_no
									$new_batchno=($fet_lastbatchno['countbatch']+1);							
									
									//new update the batch_no meta table
									$sql_updatecount="UPDATE wftasks_batch SET countbatch=(countbatch+1) WHERE idwftasks_batch=".$batch_no."";
									$res_updatecount=mysql_query($sql_updatecount);
									
									//get the tktid to update the tktin as well
									$sql_tktin=mysql_query("SELECT tktin_idtktin FROM wftasks WHERE idwftasks=".$_SESSION['wtaskid']." ");
									$fet_tktin=mysql_fetch_array($sql_tktin);
									
									//update the tkt as well
									$sql_batchtktnew="UPDATE tktin SET 
									wftasks_batch_idwftasks_batch='".$batch_no."',
									batch_number='".$new_batchno."',
									voucher_number='".$new_batchno."'
									WHERE idtktinPK=".$fet_ticket['idtktinPK']."";
									$res_batchtktnew=mysql_query($sql_batchtktnew);
									
									} else { //else if no batch now, then create some dummy queries to run the transction commit succssfully
									////////
									$res_tktin=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_batchtkt=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_lastbatchno=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_updatecount=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$sql_tktin=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_batchtktnew=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_batchtkt=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_updatecount_old=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_audit1=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_batchmeta=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									
									/////
									}//batch now
								
									
								if ( ($query_1) && ($query_2) && ($query_3) && (!isset($error_formdata)) && ($res_tktin) && (!isset($upload_error_1)) && (!isset($upload_error_2)) && (!isset($upload_error_3)) && (!isset($upload_error_4)) && ($res_batchtkt) && ($res_lastbatchno) && ($res_updatecount) && ($sql_tktin) && ($res_batchtktnew) && ($res_batchtkt) && ($res_updatecount_old) && ($res_audit1) && ($res_batchmeta) && (!isset($error_batchoutdated)) )
									{
									mysql_query("COMMIT");	
									///////////////////////////// close check and insert new subscriber ////////////////////////////
									//redirect to the correct page
									?>
									<script language="javascript">
									window.location='mytasks_history.php?tkt=<?php echo $fet_task['tktin_idtktin'];?>&task=<?php echo $fet_task['idwftasks'];?>&tab=2';
									
									</script>
									<?php
									exit;
									} else {
									mysql_query("ROLLBACK");
									?>
                                    <script language="javascript">
									 alert ('Sorry! Please Try Again!');
									</script>
                                    <?php
									if (isset($query_1)) { mysql_free_result($query_1);  }
									if (isset($query_2)) { mysql_free_result($query_2);  }
									if (isset($query_3)) { mysql_free_result($query_3); }	
									}
						} //if the no error 1_1
					
					} //Select Task Action 1
										
				///////////////  ACTION 2  ///////////////////////////////////////////////////////////////////////////////////////////////////
				if ($tktaction==2) 
					{ //Select Task Action 2 ie: pass it on

					//validate
					if (strlen($tkttskmsg2) < 1)
						{
						$error_2_1="<div class=\"msg_warning_small\">".$msg_warn_msgmis."</div>";
						}	
					if ( ($tktasito2<1) && (strlen($tktasito2) < 1) ) //to cater for exception
						{
						$error_2_2="<div class=\"msg_warning_small\">".$msg_warn_assign."</div>";
						}
					if  ( ($tktasito2=="other_exception") && (strlen($_POST['recepient_alt']) < 3) )
						{
						$error_2_3="<div class=\"msg_warning_small\">Please indicate the person to send the task to</div>";
						}
					if ($tktasito2=="other_exception") //if it's an exception
						{
						//check if the selected usr account is valid
									$str_prex=mysql_real_escape_string(trim($_POST['recepient_alt']));
									$str_ex=explode(',',$str_prex);
									
									//take ther ole
									$str_role=trim($str_ex[0]); //the first variable after comma
									$str_last=trim($str_ex[1]);
									
									$str_region=substr($str_last,-3,2);
									
									//get the id and user from the userac table
									$sql_userid="SELECT idusrac,usrrole_idusrrole FROM usrac 
									INNER JOIN usrrole ON usrac.usrrole_idusrrole=usrrole.idusrrole
									INNER JOIN usrteamzone ON usrrole.usrteamzone_idusrteamzone=usrteamzone.idusrteamzone
									WHERE usrrolename='".$str_role."' AND acstatus=1 LIMIT 1";
									$res_userid=mysql_query($sql_userid);
									$fet_userid=mysql_fetch_array($res_userid);
									$num_userid=mysql_num_rows($res_userid);
									//echo "line 1039: ".$sql_userid."<br>";

									if ($num_userid>0)
										{
										//echo $sql_userid;
										$recepient_roleid=$fet_userid['usrrole_idusrrole'];
										$recepient_usrid=$fet_userid['idusrac'];
										$recepient_groupid=0;
										} else {
										$error_2_4="<div class=\"msg_warning_small\">The Name or Role you entered does not exist or is not active</div>";
										}
	
						}
						
					//check if the selection is a Group or an Individual Role
					$tktasito2_prefix=substr($tktasito2,0,3); //if a group, the result should be GRP						
					
											
							if ( (!isset($error_2_1)) && (!isset($error_2_2)) && (!isset($error_2_3)) && (!isset($error_2_4)) )//if the no error 
								{
								
								mysql_query("BEGIN");
								
								//update this task 
								$sql_update_task="UPDATE wftasks SET wftskstatustypes_idwftskstatustypes='2',wftskstatusglobal_idwftskstatusglobal='2',timeactiontaken='".$timenowis."',actedon_idusrrole=".$_SESSION['WkAtToMrPa_iduserrole'].", actedon_idusrac='".$_SESSION['WkAtToMrPa_idacname']."' WHERE idwftasks=".$_SESSION['wtaskid']." LIMIT 1";
								$query_1=mysql_query($sql_update_task);
						
								//create an update message on the record
								$sql_update_msg="INSERT INTO wftskupdates (wftaskstrac_idwftaskstrac,usrrole_idusrrole,usrac_idusrac,wftskstatusglobal_idwftskstatusglobal,wftskstatustypes_idwftskstatustypes,wftasks_idwftasks,wftskupdate,createdby,createdon) 
								VALUES ('".$fet_idtask['wftaskstrac_idwftaskstrac']."','".$_SESSION['WkAtToMrPa_iduserrole']."','".$_SESSION['WkAtToMrPa_idacname']."','2','2','".$_SESSION['wtaskid']."','".$tkttskmsg2.$tktsms_record."','".$_SESSION['WkAtToMrPa_idacname']."','".$timenowis."')";
								$query_2=mysql_query($sql_update_msg);
						
								//get task details
								//if tskflow > 0 then ok 
								if ($fet_istskflow['wftskflow_idwftskflow'] > 0) //if there is a taskflow, then proceed
									{
									$sql_task_details = "SELECT wftasks.wftaskstrac_idwftaskstrac,wftasks.idwftasks,wftasks.usrrole_idusrrole,wftasks.wftasks_idwftasks,wftasks.wftskflow_idwftskflow,wftskflow.wfproc_idwfproc,wftasks.tktin_idtktin,wftasks.usrac_idusrac,wftasks.wftskstatustypes_idwftskstatustypes,wftasks.wftskstatusglobal_idwftskstatusglobal,wftasks.tasksubject,wftasks.taskdesc,wftasks.timeinactual,wftasks.timeoveralldeadline,wftasks.timetatstart,wftasks.timedeadline,wftasks.timeactiontaken,wftasks.sender_idusrrole,wftasks.sender_idusrac,wftskflow.listorder,wftskflow.idwftskflow,wftskflow.wftsktat,wfproc.wfproctat FROM wftasks 
									INNER JOIN wftskflow ON wftasks.wftskflow_idwftskflow=wftskflow.idwftskflow 
									INNER JOIN wfproc ON wftskflow.wfproc_idwfproc=wfproc.idwfproc
									WHERE idwftasks=".$_SESSION['wtaskid']." LIMIT 1";
									} else {
									/*$sql_task_details = "SELECT wftasks.wftaskstrac_idwftaskstrac,wftasks.idwftasks,wftasks.usrrole_idusrrole,wftasks.wftasks_idwftasks,wftasks.wftskflow_idwftskflow,link_tskcategory_wfproc.wfproc_idwfproc,wftasks.tktin_idtktin,wftasks.usrac_idusrac,wftasks.wftskstatustypes_idwftskstatustypes,wftasks.wftskstatusglobal_idwftskstatusglobal,wftasks.tasksubject,wftasks.taskdesc,wftasks.timeinactual,wftasks.timeoveralldeadline,wftasks.timetatstart,wftasks.timedeadline,wftasks.timeactiontaken,wftasks.sender_idusrrole,wftasks.sender_idusrac,wfproc.wfproctat FROM wftasks 
									INNER JOIN wftasks_exceptions ON wftasks.wftasks_idwftasks=wftasks_exceptions.wftasks_idwftasks								
									INNER JOIN tktin ON wftasks.tktin_idtktin=tktin.idtktinPK
									INNER JOIN link_tskcategory_wfproc ON tktin.tktcategory_idtktcategory=tktin.tktcategory_idtktcategory
                                    INNER JOIN wfproc ON link_tskcategory_wfproc.wfproc_idwfproc=wfproc.idwfproc
									WHERE idwftasks=".$_SESSION['wtaskid']." AND tktin.idtktinPK=".$_SESSION['tktin_idtktin']."
									AND link_tskcategory_wfproc.usrteam_idusrteam=".$_SESSION['WkAtToMrPa_idacteam']." LIMIT 1";*/
									$sql_task_details = "SELECT wftasks.wftaskstrac_idwftaskstrac,wftasks.idwftasks,wftasks.usrrole_idusrrole,wftasks.wftasks_idwftasks,wftasks.wftskflow_idwftskflow,link_tskcategory_wfproc.wfproc_idwfproc,wftasks.tktin_idtktin,wftasks.usrac_idusrac,wftasks.wftskstatustypes_idwftskstatustypes,wftasks.wftskstatusglobal_idwftskstatusglobal,wftasks.tasksubject,wftasks.taskdesc,wftasks.timeinactual,wftasks.timeoveralldeadline,wftasks.timetatstart,wftasks.timedeadline,wftasks.timeactiontaken,wftasks.sender_idusrrole,wftasks.sender_idusrac,wfproc.wfproctat FROM wftasks 
									INNER JOIN tktin ON wftasks.tktin_idtktin=tktin.idtktinPK
									INNER JOIN link_tskcategory_wfproc ON tktin.tktcategory_idtktcategory=tktin.tktcategory_idtktcategory
                                    INNER JOIN wfproc ON link_tskcategory_wfproc.wfproc_idwfproc=wfproc.idwfproc
									WHERE idwftasks=".$_SESSION['wtaskid']." AND tktin.idtktinPK=".$_SESSION['tktin_idtktin']."
									AND link_tskcategory_wfproc.usrteam_idusrteam=".$_SESSION['WkAtToMrPa_idacteam']." LIMIT 1";									
									}
								
								$res_task_details = mysql_query($sql_task_details);
								$fet_task_details = mysql_fetch_array($res_task_details);
								//echo "<br><br><br>".$sql_task_details."<br>";
								//exit;
								//}
								
								//the next task flow is depended on the value in a hidden field
								$wftaskflow_id_txtbox=mysql_real_escape_string(trim($_POST['wftaskflow_id']));
								
										
								////////////// START CALCULATION OF TIME /////////
								if ( ($tktasito2!="other_exception") || ($fet_task['wftskflow_idwftskflow']!=0) ) //if NOT other exception OR sender had idtksflow, then follow this steps
									{ 
										//lock the query below to the above variable
										//find the next tasks
										$sql_nextwf="SELECT idwftskflow,wftskflow.wfsymbol_idwfsymbol as wfsymbol,wfactors.usrrole_idusrrole as usrrole,wfactors.usrgroup_idusrgroup as usrgroup,wftsktat,expubholidays
										FROM wftskflow INNER JOIN wfactors ON wftskflow.idwftskflow=wfactors.wftskflow_idwftskflow 
										WHERE wfproc_idwfproc=".$fet_task_details['wfproc_idwfproc']." AND wftskflow.wfsymbol_idwfsymbol=2 AND wftskflow.idwftskflow=".$wftaskflow_id_txtbox." ORDER BY listorder ASC LIMIT 1";
										//echo $sql_nextwf."<br>";
										//exit;
										$res_nextwf=mysql_query($sql_nextwf);
										$num_nextwf=mysql_num_rows($res_nextwf);
										$fet_nextwf=mysql_fetch_array($res_nextwf);
										
	
												//1. construct deadlines and start times against TATs and time task was received
												$ticket_wday = date("w",strtotime($timenowis)); //ticket day of the week
												$ticket_hour = date("H:i",strtotime($timenowis)); //ticket hour	
												$ticket_actualtimein = $timenowis;
												//$ticket_timein = $timenowis;
												
												$task_starttime_raw = $ticket_actualtimein;//exactly the time the task came in on the record
												$task_deadline_raw = date("Y-m-d H:i:s",strtotime($ticket_actualtimein)+$fet_nextwf['wftsktat']); //this is for the specific task
												$task_overalldeadline_raw = date("Y-m-d H:i:s",strtotime($ticket_actualtimein)+$fet_task_details['wfproctat']); //this is the overall time set for the whole process should take
												
													
												//CONSTRUCT STARTING TIME
												//a) Did this task fall on a Weekday, Working hours ?
												if (($ticket_wday>0) && ($ticket_wday<6)) //Monday - Friday
													{
													//check if it was a weekday
													$sql_workinghrs="SELECT time_earliest,time_latest,wfworkingdays_idwfworkingdays FROM wfworkinghrs WHERE wftskflow_idwftskflow=".$fet_task_details['idwftskflow']." AND 	wfworkingdays_idwfworkingdays=1";
													$res_workinghrs=mysql_query($sql_workinghrs);
													$num_workinghrs=mysql_num_rows($res_workinghrs);
													$fet_workinghrs=mysql_fetch_array($res_workinghrs);
													
													//echo $sql_workinghrs;
													//check time in
													if ( ( ($ticket_hour>=$fet_workinghrs['time_earliest']) && ($ticket_hour<=$fet_workinghrs['time_latest']) ) || ($ticket_hour<$fet_workinghrs['time_earliest']) )
														{
														
														$push_weekday = 0;//then do not add a day to the start time
														
														} else {
														
														$push_weekday = 1;
														
														}
														
													} else {
													
													$push_weekday = 0;
													
													}//close Monday - Friday
													
													
												if ($ticket_wday==6) // Saturday
													{
													//check if the task applies for Saturdays
					
													$sql_saturdays="SELECT time_earliest,time_latest,wfworkingdays_idwfworkingdays FROM wfworkinghrs WHERE wftskflow_idwftskflow=".$fet_task_details['idwftskflow']." AND wfworkingdays_idwfworkingdays=2 LIMIT 1";
													$res_saturdays=mysql_query($sql_saturdays);
													$num_saturdays=mysql_num_rows($res_saturdays);
													$fet_saturdays=mysql_fetch_array($res_saturdays);
														
													
					
													if ( ($fet_saturdays['time_earliest']=='00:00:00') && ($fet_saturdays['time_latest']=='00:00:00') )
														{
														$push_saturday = 1; // push a day
														
														} else { //then if not set to 00:00:00 as per the query above, compare the timein
				
															//check the time the ticket came in
															if ( ( ($ticket_hour>=$fet_saturdays['time_earliest']) && ($ticket_hour<=$fet_saturdays['time_latest']) ) || ($ticket_hour<$fet_saturdays['time_earliest']) )
																{
																$push_saturday =0;//then do not add a day to the start time
																} else {
																$push_saturday =1;//then do not add a day to the start time
																}
																	
														} //close if not set to 00:00:00
													
													} else {
															
													$push_saturday=0;
																						
													} //close if saturday
														
													
												if ($ticket_wday==0) // Sunday
														{
														//check if the task applies for sundays
														$sql_sundays="SELECT time_earliest,time_latest,wfworkingdays_idwfworkingdays FROM wfworkinghrs WHERE wftskflow_idwftskflow=".$fet_nextwf['idwftskflow']." AND wfworkingdays_idwfworkingdays=3 LIMIT 1";
														
														$res_sundays=mysql_query($sql_sundays);
														$num_sundays=mysql_num_rows($res_sundays);
														$fet_sundays=mysql_fetch_array($res_sundays);
														
														if (($fet_sundays['time_earliest']=='00:00:00') && ($fet_sundays['time_latest']=='00:00:00')) 
															{
															$push_sunday = 1; // push a day
															} else { //then if not set to 00:00:00 as per the query above, compare the timein
															//check the time the ticket came in
															if ( ( ($ticket_hour>=$fet_sundays['time_earliest']) && ($ticket_hour<=$fet_sundays['time_latest']) ) || ($ticket_hour<$fet_sundays['time_earliest']) )
																{
																
																$push_sunday =0;//then do not add a day to the start time
																
																} else {
																
																$push_sunday =1;//then do not add a day to the start time
																
																} //close if not within the pre-set sunday time frames
																																			
															} //close if not set to 00:00:00
														
														} else {
														
														$push_sunday=0;
														
														} //close if a Sunday
												
												
												//Adjust the Start and Stop Times
												$total_pushes = ($push_weekday + $push_saturday + $push_sunday); //number of adjustments across
												$total_pushes_sec = ($total_pushes * 86400); //
												
												$task_starttime_refined = date("Y-m-d H:i:s",strtotime($task_starttime_raw) + $total_pushes_sec);
												$task_deadline_refined = date("Y-m-d H:i:s",strtotime($task_deadline_raw) + $total_pushes_sec);
												$task_overalldeadline_refined = date("Y-m-d H:i:s",strtotime($task_overalldeadline_raw) + $total_pushes_sec);
												
												
												//Are public holidays Excluded
												if ($fet_nextwf['expubholidays']==1) //if set, then find out how many public holidays will count between the new start and end dates
													{
													
													$sql_holidays = "SELECT idwftskholiday FROM wftskholiday WHERE wftskholidaydate>='".$task_starttime_refined."' AND  wftskholidaydate<='".$task_deadline_refined."' ";
													$res_holidays = mysql_query($sql_holidays);
													$num_holidays = mysql_num_rows($res_holidays);
													
													
													
													$push_holidays=($num_holidays * 86400);
													
													} else { //else not set, then no holiday found
													
													$push_holidays=0;
													
													}
													
												//start and end times almost final
												$task_starttime_prefinal = date("Y-m-d H:i:s",strtotime($task_starttime_refined) + $push_holidays);
												$task_deadline_prefinal = date("Y-m-d H:i:s",strtotime($task_deadline_refined) + $push_holidays);
												$task_overalldeadline_prefinal = date("Y-m-d H:i:s",strtotime($task_overalldeadline_refined) + $push_holidays);
											
													
												//finally, within the span of the refined Start and End days, find how many Saturdays and Sundays will be exempted if excempt

												if ($push_saturday==1) //if Saturday was excempted
													{
													
													$count_saturdays = 0;
													
													$start_ts = strtotime($task_starttime_prefinal); // start time stamp
													$end_ts = strtotime($task_deadline_prefinal); // end time stamp
					
													
													while ($start_ts<=$end_ts) 
														{
															$day = date('w', $start_ts);
																if ($day == 6) 
																	{ // this is a saturday
																	//echo date('d', $working_ts)."<br>";
																	$count_saturdays++;
																	}
															$start_ts = $start_ts + $day_sec;
														}
													
													//number of exempt saturdays


													$ex_saturdays = $count_saturdays;
													
													} else {
													
													$ex_saturdays = 0;
													
													} //if Saturday was excempted
												
												
												
												if ($push_sunday==1) //if sunday was excempted
													{
													
													$count_sundays = 0;
													
													$start_ts = strtotime($task_starttime_prefinal); // start time stamp
													$end_ts = strtotime($task_deadline_prefinal); // end time stamp
					
													
													while ($start_ts<=$end_ts) 
														{
															$day = date('w', $start_ts);
																if ($day == 0) 
																	{ // this is a sunday
																	//echo date('d', $working_ts)."<br>";
																	$count_sundays++;
																	}
															$start_ts = $start_ts + $day_sec;
														}
													
													//number of exempt sundays
													$ex_sundays = $count_sundays;
													
													} else {
													
													$ex_sundays = 0;
													
													} //if sunday was excempted
												
												
												
												//FINAL START AND END DAYS FOR THE TASKS THEN AS FOLLOWS
												$push_ex_saturdays = ($ex_saturdays * 86400);
												$push_ex_sundays = ($ex_sundays * 86400);
																			
												$next_workflow_id=$fet_nextwf['idwftskflow'];
												
												
											} //close if NO EXCEPTION
											
											//if this is an actor exception, then there is no Next Workflow 
											if ( ($tktasito2=="other_exception") || ($fet_task['wftskflow_idwftskflow']==0) ) //if NOT other exception, then follow this steps
												{ 
												//so just calculate manually
												$task_starttime_final = $timenowis; //time now
												$task_deadline_final = date("Y-m-d H:i:s",strtotime($timenowis) + (2*86400)); //just add 48 hours to the current
												$task_overalldeadline_final = $fet_task['timeoveralldeadline']; //as original time deadline for this task
												
												} else { //if not exception
												
												$task_starttime_final = $task_starttime_prefinal; //the start time remains the same... only adjust the deadline to discount off the extra weekend days
												$task_deadline_final = date("Y-m-d H:i:s",strtotime($task_deadline_prefinal) + ($push_ex_saturdays + $push_ex_sundays));
												$task_overalldeadline_final = date("Y-m-d H:i:s",strtotime($task_overalldeadline_prefinal) + ($push_ex_saturdays + $push_ex_sundays));
											
												}
												
								///////////// END CALCULATION OF TIME ////////
													
								///////////// GET THE RECEPIENT DETAILS DEPENDING ON CONDITIONS FULFILLED								
								//get user account id
								if ( ($tktasito2_prefix!="GRP") && ($tktasito2!="other_exception") ) //It's not a group and Not an exception, then great
									{
									$sql_userid="SELECT idusrac FROM usrac WHERE usrrole_idusrrole=".$tktasito2." LIMIT 1";
									$res_userid=mysql_query($sql_userid);
									$fet_userid=mysql_fetch_array($res_userid);
								//vars below
									$recepient_roleid=$tktasito2;
									$recepient_usrid=$fet_userid['idusrac'];
									$recepient_groupid=0;
									
									}
									
								if ($tktasito2_prefix=="GRP")  //It's  a group, then
									{
									
									$tktasito2_suffix=substr($tktasito2,3); //if a group, the result should be GRP
									
									$sql_userid="SELECT idwfactorsgroupname FROM wfactorsgroupname WHERE idwfactorsgroupname=".$tktasito2_suffix." LIMIT 1";
									$res_userid=mysql_query($sql_userid);
									$fet_userid=mysql_fetch_array($res_userid);
								//vars below
									$recepient_roleid=0;
									$recepient_usrid=0;
									$recepient_groupid=$tktasito2_suffix; //groups id is store on the same select menu as the rolws
								//	echo $sql_userid;
									}
								
								
									
							///////////// END OF RECEPIENT DETAILS //////////////////
						
							//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//
							
							//Route SMS Tickets to the right zones by using the ID of the task recepient after strictly the first task
							/////////////////////////////UPDATE TICKET USERTEAMZONE FOR NEW SMS TASKS////////////////////////////////////
							$sql_smswftasks="SELECT count(idwftasks) as tottasks FROM wftasks WHERE tktin_idtktin=".$fet_task_details['tktin_idtktin']."";
							$res_smswftasks=mysql_query($sql_smswftasks);
							$num_smswftasks=mysql_num_rows($res_smswftasks);
							$fet_smswftasks=mysql_fetch_array($res_smswftasks);
							
							if($num_smswftasks>0) //check if tasks exist
								{

								if($fet_smswftasks['tottasks']==1) //check if there is only one task > this is the first time the ticked is being actioned (by CCA)
									{

									$sql_getzoneid="SELECT usrteamzone_idusrteamzone FROM usrrole WHERE idusrrole=".$recepient_roleid." LIMIT 1";
									$res_getzoneid=mysql_query($sql_getzoneid);
									$fet_getzoneid=mysql_fetch_array($res_getzoneid);
									$num_getzoneid=mysql_num_rows($res_getzoneid);
									
									if($num_getzoneid>0)
										{
										$update_tktin_zoneid="UPDATE tktin SET usrteamzone_idusrteamzone=".$fet_getzoneid['usrteamzone_idusrteamzone']." 
										WHERE idtktinPK=".$fet_task_details['tktin_idtktin']." AND tktchannel_idtktchannel=2 LIMIT 1";
										mysql_query($update_tktin_zoneid);
										}									
									}
								}
							//////////////////////////////////////////////////////////////////////////////////////////////////////									
								
								//insert new task for the recepeint
								
								if ($fet_task_details['wftaskstrac_idwftaskstrac'] > 0) //ensure the track >0 - just a caution
									{
									$sql_new_task="INSERT INTO wftasks (wftaskstrac_idwftaskstrac,usrrole_idusrrole,wftasks_idwftasks,wftskflow_idwftskflow,tktin_idtktin,usrac_idusrac,wftskstatustypes_idwftskstatustypes,wftskstatusglobal_idwftskstatusglobal,tasksubject,taskdesc,timeinactual,timeoveralldeadline,timetatstart,timedeadline,timeactiontaken,sender_idusrrole,sender_idusrac,createdon,createdby,wfactorsgroup_idwfactorsgroup,wftasks_batch_idwftasks_batch,batch_number) 
									VALUES ('".$fet_task_details['wftaskstrac_idwftaskstrac']."','".$recepient_roleid."','".$fet_task_details['idwftasks']."','".$wftaskflow_id_txtbox."','".$fet_task_details['tktin_idtktin']."','".$recepient_usrid."','0','1','".$fet_task_details['tasksubject']."','".$tkttskmsg2."','".$timenowis."','".$fet_task_details['timeoveralldeadline']."','".$task_starttime_final."','".$task_deadline_final."','0000-00-00 00:00:00','".$_SESSION['WkAtToMrPa_iduserrole']."','".$_SESSION['WkAtToMrPa_idacname']."','".$timenowis."','".$_SESSION['WkAtToMrPa_idacname']."','".$recepient_groupid."','".$batch_no."','0')";
									$query_3=mysql_query($sql_new_task);
									}
						
								//exit;  								
								
							//check if there is a form data and if so, go ahead and process this transaction with inserts or updates
							if ( (isset($_POST['formdata_available'])) && ($_POST['formdata_available']==1) )
								{
								//echo "processed <br>";
								//check the db for this field by reusing the sql statement above
/*								$sql_val="SELECT idwfprocassetsaccess,assetname,perm_read,perm_write,perm_required,wfprocassets.wfprocdtype_idwfprocdtype,idwfprocassets FROM wfprocassetsaccess 
								INNER JOIN wfprocassets ON wfprocassetsaccess.wfprocassets_idwfprocassets=wfprocassets.idwfprocassets
								WHERE wftskflow_idwftskflow=".$fet_task['wftskflow_idwftskflow']." AND wfprocassetsaccess.perm_read=1 ORDER BY wfprocassets.ordering ASC";
*/								
								$sql_val="SELECT idwfprocassetsaccess,assetname,perm_read,perm_write,perm_required,wfprocassets.wfprocdtype_idwfprocdtype,idwfprocassets,wfprocassetsgrouplbl,sysprofiles_idsysprofiles,wfprocassetsaccess.wfprocforms_idwfprocforms
								FROM wfprocassetsaccess
								INNER JOIN wfprocassets ON wfprocassetsaccess.wfprocassets_idwfprocassets = wfprocassets.idwfprocassets
								INNER JOIN wfprocassetsgroup ON wfprocassets.wfprocassetsgroup_idwfprocassetsgroup = wfprocassetsgroup.idwfprocassetsgroup
								INNER JOIN wfprocforms_cats ON wfprocassetsaccess.wfprocforms_idwfprocforms = wfprocforms_cats.wfprocforms_idwfprocforms 
								WHERE sysprofiles_idsysprofiles=".$_SESSION['WkAtToMrPa_iduserprofile']." AND wfprocforms_cats.tktcategory_idtktcategory=".$fet_ticket['tktcategory_idtktcategory']." AND wfprocassetsaccess.perm_read=1 ORDER BY wfprocassetsgrouplbl ASC,ordering ASC";
							
								$res_val=mysql_query($sql_val);
								$num_val=mysql_num_rows($res_val);
								$fet_val=mysql_fetch_array($res_val);
//	echo $sql_val;
								if ($num_val > 0) //if there are some values, then
									{
									do {
									//master-checklist if  | it is required | there is a value | the data type to determine the field |  whether an update or insert
									
									//validate required
								//	echo "validation ";
								//	echo $_POST['required_'.$fet_val['idwfprocassetsaccess'].'']."<br>";
								//	echo $_POST['item_'.$fet_val['idwfprocassetsaccess'].''];
										if (
										(isset($_POST['required_'.$fet_val['idwfprocassetsaccess'].''])) 
										&& ($_POST['required_'.$fet_val['idwfprocassetsaccess'].'']==1) 
										&&  ($_POST['item_'.$fet_val['idwfprocassetsaccess'].'']=="")   
										)
											{
											//echo $_POST['item_'.$fet_val['idwfprocassetsaccess'].'']."<br>";
											$error_formdata=1;
											echo "<div class=\"msg_warning_small\">Form : ".$fet_val['assetname']." is required | <a href=\"mytasks_history.php\">Back to Task View</a></div>";
											
											}
									
									//if no error on the dataform, then process
									if (!isset($error_formdata))
										{	
			
										if ($_POST['transtype_'.$fet_val['idwfprocassetsaccess'].'']=="INSERT")
											{
											//check the form item type first
											$ttype=$_POST['itemtype_'.$fet_val['idwfprocassetsaccess'].''];
											
												
												if (($ttype==1) || ($ttype==4) || ($ttype==5) || ($ttype==6) || ($ttype==7) || ($ttype==8) || ($ttype==9) || ($ttype==10) ) //if textbox OR yes/no OR datepicker OR datetimepicker
													{
													$fvalue=mysql_real_escape_string(trim($_POST['item_'.$fet_val['idwfprocassetsaccess'].'']));
													
													//then process as below
													$sql_insert="INSERT INTO wfassetsdata (wfprocassetsaccess_idwfprocassetsaccess,
													wfprocassetschoice_idwfprocassetschoice,
													wfprocassets_idwfprocassets,
													wftasks_idwftasks,
													value_choice,
													value_path,
													wftaskstrac_idwftaskstrac,
													tktin_idtktin,
													createdby,
													createdon)
													VALUES ('".$fet_val['idwfprocassetsaccess']."',
													'0',
													'".$fet_val['idwfprocassets']."',
													'".$_SESSION['wtaskid']."',
													'".$fvalue."',
													'',
													'".$_SESSION['wftaskstrac']."',
													'".$_SESSION['tktin_idtktin']."',
													'".$_SESSION['WkAtToMrPa_idacname']."',
													'".$timenowis."'
													)";
													
													mysql_query($sql_insert);
													}
													
												if ($ttype==2)//if menulist
													{
													$fvalue=mysql_real_escape_string(trim($_POST['item_'.$fet_val['idwfprocassetsaccess'].'']));
													
													$sql_insert="INSERT INTO wfassetsdata (wfprocassetsaccess_idwfprocassetsaccess,
													wfprocassetschoice_idwfprocassetschoice,
													wfprocassets_idwfprocassets,
													wftasks_idwftasks,
													value_choice,
													value_path,
													wftaskstrac_idwftaskstrac,
													tktin_idtktin,
													createdby,
													createdon)
													VALUES ('".$fet_val['idwfprocassetsaccess']."',
													'".$fvalue."',
													'".$fet_val['idwfprocassets']."',
													'".$_SESSION['wtaskid']."',
													'',
													'',
													'".$_SESSION['wftaskstrac']."',
													'".$_SESSION['tktin_idtktin']."',
													'".$_SESSION['WkAtToMrPa_idacname']."',
													'".$timenowis."'
													)";
													
													mysql_query($sql_insert);
													//echo $sql_insert."<br><br>";
													
													}
													
										if ( ($ttype==3) && (isset($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])) && (strlen($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])>4) )//if file upload".$_SESSION['tktin_idtktin']."
											{
											$fvalue_upload=basename($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"]);
											$target_dir = "../documents/task_docs/".$today."/";
//											$docname=$_SESSION['tktin_idtktin']."_".basename($_FILES["fileToUpload"]["name"]);
											$docname=$_SESSION['tktin_idtktin']."_".$fvalue_upload;
											//we need to seed the document to make it unique_
											//lets include the ticket_ref number of the task to the name of the file
											$target_file = $target_dir . $docname;
											$uploadOk = 1;
											//just keep the file name only
											$file_name_only=$_SESSION['tktin_idtktin']."_".$_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"];
											$file_size_only=$_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["size"];
											//check if there is any document before proceeding
											if (strlen($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])>4)
												{
												
												$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
												
												//validation before uploading										 
												//check if file exists
												if (file_exists($target_file)) 
													{
													$upload_error_1 = "<div class=\"msg_warning_small\">File Missing</div>";
													}
												
												if ($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["size"] > 10485760) 
													{
													$upload_error_2 = "<div class=\"msg_warning_small\">File Max Size Exceeded( 10 MB)</div>";
													}
												
												if	($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
													&& $imageFileType != "gif" && $imageFileType != "doc" && $imageFileType != "docx" 
													&& $imageFileType != "pdf" && $imageFileType != "xls" && $imageFileType != "xlsx" 
													&& $imageFileType != "ppt" && $imageFileType != "pptx"  && $imageFileType != "csv"    ) {
														
													$upload_error_3 = "<div class=\"msg_warning_small\">Sorry, file format [".$imageFileType."] not allowed</div>";
													}
												//echo $upload_error_1.$upload_error_2.$upload_error_3;	
												//echo "Size -->".$_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["size"];
												if ( (!isset($upload_error_1)) && (!isset($upload_error_2)) && (!isset($upload_error_3)) )
													{
													 if (move_uploaded_file($_FILES['item_'.$fet_val['idwfprocassetsaccess'].'']["tmp_name"], $target_file)) 
														{
														$upload_success=1;
														//log the record into the Database
														$sql_insert="INSERT INTO wfassetsdata (wfprocassetsaccess_idwfprocassetsaccess,
														wfprocassetschoice_idwfprocassetschoice,
														wfprocassets_idwfprocassets,
														wftasks_idwftasks,
														value_choice,
														value_path,
														wftaskstrac_idwftaskstrac,
														tktin_idtktin,
														createdby,
														createdon)
														VALUES ('".$fet_val['idwfprocassetsaccess']."',
														'0',
														'".$fet_val['idwfprocassets']."',
														'".$_SESSION['wtaskid']."',
														'',
														'".$file_name_only."',
														'".$_SESSION['wftaskstrac']."',
														'".$_SESSION['tktin_idtktin']."',
														'".$_SESSION['WkAtToMrPa_idacname']."',
														'".$timenowis."'
														)";
														
														mysql_query($sql_insert);
														
														//create the audit log
														$sql_audit="INSERT INTO audit_docuploads ( doc_name, doc_ext, doc_size, tktin_idtktin, createdon, createdby, usersess, usrip, wfprocassets_idwfprocassets) 
														VALUES ('".$file_name_only."', '".$imageFileType."', '".$file_size_only."', '".$_SESSION['tktin_idtktin']."', '".$timenowis."', '".$_SESSION['WkAtToMrPa_idacname']."', '".session_id()."', '".$_SERVER['REMOTE_ADDR']."','".$fet_val['idwfprocassets']."')";
														mysql_query($sql_audit);
														
														} else {
															$upload_error_4 = "<div class=\"msg_warning_small\">Sorry, we are unable to upload that file</div>";
														}
													} //no error
												} //where strlen > 4
											} //type==3
											
											} //inserts end
											
											
										if ($_POST['transtype_'.$fet_val['idwfprocassetsaccess'].'']=="UPDATE")
									{
									$ttype=$_POST['itemtype_'.$fet_val['idwfprocassetsaccess'].''];
									$itempk=mysql_real_escape_string(trim($_POST['itempk_'.$fet_val['idwfprocassetsaccess'].'']));
									
									//value captured - //this hack for checkbox
									if ((($ttype==3)||($ttype==4)) && (!isset($_POST['item_'.$fet_val['idwfprocassetsaccess'].''])) )
										{
										$fvalue=0;
										} else {
										$fvalue=mysql_real_escape_string(trim($_POST['item_'.$fet_val['idwfprocassetsaccess'].'']));
										}
									
									//only if there are records
									if (
										( ($fvalue > 0) || (strlen($fvalue) > 0) && ($ttype!=4) ) 
										|| 
										( ($ttype==4) && (($fvalue=='') || ($fvalue==0) || ($fvalue!=0)) ) 
										) 
									/*if ( ($fvalue!='') && (strlen($fvalue) > 0) )*/
										{
											//check the form item type first
											if (($ttype==1) || ($ttype==4) || ($ttype==5) || ($ttype==6) || ($ttype==7) || ($ttype==8) || ($ttype==9) || ($ttype==10)   ) //if textbox OR yes/no OR datepicker OR datetimepicker
													{
													///audit log
													$sql_auditlog_form="INSERT INTO audit_wfassetsdata (idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice_prev, wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice_prev, value_choice_new, value_path_prev, value_path_new, wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon, modifiedby_new, modifiedon_new) 
													SELECT idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice, wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice, '".$fvalue."', value_path,'', wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon,".$_SESSION['WkAtToMrPa_idacname'].",'".$timenowis."' 
													FROM wfassetsdata
													WHERE idwfassetsdata=".$itempk." AND value_choice!='".$fvalue."' ";
													//echo $sql_auditlog_form."<br>";
													mysql_query($sql_auditlog_form);
													
													//then process as below
													$sql_update="UPDATE wfassetsdata SET 
													value_choice='".$fvalue."',
													wftaskstrac_idwftaskstrac='".$_SESSION['wftaskstrac']."',
													tktin_idtktin='".$_SESSION['tktin_idtktin']."',
													modifiedby='".$_SESSION['WkAtToMrPa_idacname']."',
													modifiedon='".$timenowis."'
													WHERE idwfassetsdata=".$itempk." LIMIT 1";
													
													mysql_query($sql_update);
													//echo $sql_update."<br><br>";
													}
											
											if ($ttype==2)//if menulist
													{
													//enter the audit trail only if there is a change
													$sql_auditlog_form="INSERT INTO audit_wfassetsdata (idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice_prev, wfprocassetschoice_idwfprocassetschoice_new, wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice_prev, value_choice_new, value_path_prev, value_path_new, wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon, modifiedby_new, modifiedon_new) 
													SELECT idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice,'".$fvalue."', wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice, '', value_path,'', wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon,".$_SESSION['WkAtToMrPa_idacname'].",'".$timenowis."' 
													FROM wfassetsdata
													WHERE idwfassetsdata=".$itempk." AND wfprocassetschoice_idwfprocassetschoice!='".$fvalue."' ";
													//echo $sql_auditlog_form."<br>";
													mysql_query($sql_auditlog_form);
													
													
													$sql_update="UPDATE wfassetsdata SET 
													wfprocassetschoice_idwfprocassetschoice='".$fvalue."',
													wftaskstrac_idwftaskstrac='".$_SESSION['wftaskstrac']."',
													tktin_idtktin='".$_SESSION['tktin_idtktin']."',
													modifiedby='".$_SESSION['WkAtToMrPa_idacname']."',
													modifiedon='".$timenowis."'
													WHERE idwfassetsdata=".$itempk." LIMIT 1";
													//echo $sql_update."<br><br>";
													mysql_query($sql_update);
													}
													
											if ( ($ttype==3) && (isset($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])) && (strlen($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])>4) )//if file upload".$_SESSION['tktin_idtktin']."
											{
											$fvalue_upload=basename($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"]);
											$target_dir = "../documents/task_docs/".$today."/";
//											$docname=$_SESSION['tktin_idtktin']."_".basename($_FILES["fileToUpload"]["name"]);
											$docname=$_SESSION['tktin_idtktin']."_".$fvalue_upload;
											//we need to seed the document to make it unique_
											//lets include the ticket_ref number of the task to the name of the file
											$target_file = $target_dir . $docname;
											$uploadOk = 1;
											$file_name_only=$_SESSION['tktin_idtktin']."_".$_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"];
											$file_size_only=$_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["size"];

											////check if there is any document before proceeding
											if (strlen($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])>4)
												{
											
													$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
													
													//validation before uploading											 
													//check if file exists
													if (file_exists($target_file)) 
														{
														$upload_error_1 = "<div class=\"msg_warning_small\">File Missing</div>";
														}
													
													if ($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["size"] > 10485760) 
														{
														$upload_error_2 = "<div class=\"msg_warning_small\">File Max Size Exceeded( 10 MB)</div>";
														}
													
													if	($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
														&& $imageFileType != "gif" && $imageFileType != "doc" && $imageFileType != "docx" 
														&& $imageFileType != "pdf" && $imageFileType != "xls" && $imageFileType != "xlsx" 
														&& $imageFileType != "ppt" && $imageFileType != "pptx" && $imageFileType != "csv"    ) {
															
														$upload_error_3 = "<div class=\"msg_warning_small\">Sorry, file format [".$imageFileType."] not allowed</div>";
														}

													if ( (!isset($upload_error_1)) && (!isset($upload_error_2)) && (!isset($upload_error_3)) )
														{
														//echo "soo farr soo good";
														 if (move_uploaded_file($_FILES['item_'.$fet_val['idwfprocassetsaccess'].'']["tmp_name"], $target_file)) 
															{
															$upload_success=1;
															
															$sql_auditlog_form="INSERT INTO audit_wfassetsdata (idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice_prev, wfprocassetschoice_idwfprocassetschoice_new, wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice_prev, value_choice_new, value_path_prev, value_path_new, wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon, modifiedby_new, modifiedon_new) 
															SELECT idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice,'', wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice, '', value_path,'".$file_name_only."', wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon,".$_SESSION['WkAtToMrPa_idacname'].",'".$timenowis."' 
															FROM wfassetsdata
															WHERE idwfassetsdata=".$itempk." AND value_path!='".$file_name_only."' ";
															//echo $sql_auditlog_form."<br>";
															mysql_query($sql_auditlog_form);
															
															//log the record into the Database
															$sql_update="UPDATE wfassetsdata SET 
															value_path='".$file_name_only."',
															modifiedby='".$_SESSION['WkAtToMrPa_idacname']."',
															modifiedon='".$timenowis."'
															WHERE idwfassetsdata=".$itempk." LIMIT 1";

															mysql_query($sql_update);
															
															//create the audit log
															$sql_audit="INSERT INTO audit_docuploads ( doc_name, doc_ext, doc_size, tktin_idtktin, createdon, createdby, usersess, usrip,wfprocassets_idwfprocassets) 
															VALUES ('".$file_name_only."', '".$imageFileType."', '".$file_size_only."', '".$_SESSION['tktin_idtktin']."', '".$timenowis."', '".$_SESSION['WkAtToMrPa_idacname']."', '".session_id()."', '".$_SERVER['REMOTE_ADDR']."',".$itempk.")";
															mysql_query($sql_audit);
																											
															} else {
																$upload_error_4 = "<div class=\"msg_warning_small\">Sorry, we are unable to upload that file</div>";
															}
														} //no error
													} // if fvalue strlen>4
											
												} //type==3			
											}	
										} //end update
											
										}
										
									} while ($fet_val=mysql_fetch_array($res_val));
									
									} //if record is > 0
								
								} //close form data checker
														
								//Feedback SMS to send customer/sender a message
								if ( (isset($tktsms)) && (strlen($tktsms)>15) && (strlen($tktsenderphone)==12) )
									{
									$sql_smsout="INSERT INTO mdata_out_sms (destnumber,msgtext)  
									VALUES ('".$tktsenderphone."','".$tktno." ".$tktsms."')";
									mysql_query($sql_smsout);
									}
							
									
		
									//notify if anyone is to be notified
									$sql_notify="SELECT idwfnotification,wfnotification.tktstatus_idtktstatus,usrrole_idusrrole,wftskflow_idwftskflow,notify_system,notify_email,notify_sms,idtktmsgs,tktmsg_sms,tktmsg_email,tktmsg_dashboard FROM wfnotification 
									INNER JOIN tktmsgs ON wfnotification.idwfnotification=tktmsgs.wfnotification_idwfnotification
									WHERE wftskflow_idwftskflow=".$_SESSION['thistskflow']." ORDER BY idwfnotification ASC";
									$res_notify=mysql_query($sql_notify);
									$num_notify=mysql_num_rows($res_notify);
									$fet_notify=mysql_fetch_array($res_notify);
									//echo $sql_notify;
									if ($num_notify > 0 ) // if there is a notification setting
										{
										do {			
										//check for each of the settings 
											if ( ($fet_notify['notify_system']==1) && (strlen($fet_notify['tktmsg_dashboard'])>2) ) //system dashboard set on
												{
												$sql_dash="INSERT INTO tktmsglogs_dashboard (tktmsgs_idtktmsgs,msgto_roleid,msgto_subject,msgto_body,createdon,readon)
												VALUES ('".$fet_notify['idtktmsgs']."','".$fet_notify['usrrole_idusrrole']."','Notification - Tkt No : ".$tktno.", ".$fet_ticketcat['tktcategoryname'].",'".$fet_notify['tktmsg_dashboard']." - ".$fet_ticketcat['tktcategoryname']."','".$timenowis."','0000-00-00 00:00:00')";
												mysql_query($sql_dash);									
												}// system dashboard set on
														
												//get this roles email address and phone numbers
												//ensure the account is active as well...
												$sql_rolecontacts="SELECT usremail,usrphone FROM usrac WHERE usrrole_idusrrole=".$fet_notify['usrrole_idusrrole']." AND acstatus=1 LIMIT 1";
												$res_rolecontacts=mysql_query($sql_rolecontacts);

												$fet_rolecontacts=mysql_fetch_array($res_rolecontacts);
												$num_rolecontacts=mysql_num_rows($res_rolecontacts);
												
												if ( ($fet_notify['notify_email']==1) && ($num_rolecontacts>0) && (strlen($fet_rolecontacts['usremail'])>6) && (strlen($fet_notify['tktmsg_email'])>2) )//email set on
													{
													$sql_email="INSERT INTO tktmsgslog_emails(tktmsgs_idtktmsgs,emailto,emailsubject,emailbody,createdon,senton) 
													VALUES ('".$fet_notify['idtktmsgs']."','".$fet_rolecontacts['usremail']."','Notification - Tkt No : ".$tktno.", ".$fet_ticketcat['tktcategoryname']."','".$fet_notify['tktmsg_email']." - Tkt No:".$tktno.", ".$fet_ticketcat['tktcategoryname']."','".$timenowis."','0000-00-00 00:00:00')";
														
													mysql_query($sql_email);
													}
													
													if ( ($fet_notify['notify_sms']==1) && ($num_rolecontacts>0) && (strlen($fet_rolecontacts['usrphone'])==13) )
													{
													$sql_sms="INSERT INTO mdata_out_sms (destnumber,msgtext) 
													VALUES ('".$fet_rolecontacts['usrphone']."',' Auto Notification - Tkt No:".$tktno.", ".$fet_ticketcat['tktcategoryname']." received')";
							
													mysql_query($sql_sms);
													}
														
											} while ($fet_notify=mysql_fetch_array($res_notify));								
												
										} //close - if there is a notification setting
							
							/////////////////////////////check and insert a new subscriber
							if ($fet_task['usrrole_idusrrole']==2) //if this is the first ticket from the customer, then do this...
								{
								//check if a subscriber with the same credentials matches
								$sql_subis="SELECT idsmssubs FROM ".$_SESSION['WkAtToMrPa_tblsmsbc']." WHERE subnumber='".$tktsenderphone."' AND usrtype=1";
								$res_subis=mysql_query($sql_subis);
								$num_subis=mysql_num_rows($res_subis);
								
								//if not, add the new credentials
								if ($num_subis==0)
									{
									$sql_subnew="INSERT INTO ".$_SESSION['WkAtToMrPa_tblsmsbc']." (wftskid,tktid,subnumber,idloctown,idusrteamzone,usrtype,createdon,createdby)
									VALUES ('".$_SESSION['wtaskid']."','".$fet_task['tktin_idtktin']."','".$fet_confirmloc['idloctowns']."','".$_SESSION['WkAtToMrPa_userteamzoneid']."','1','".$timenowis."','".$_SESSION['WkAtToMrPa_idacname']."')";
									mysql_query($sql_subnew);
									}
								}
							///////////////////////////// close check and insert new subscriber ////////////////////////////
							
							//if exception, then follow the following path...remember to add wftasks_exceptions table transaction
							if ($tktasito2=="other_exception") //if it's an exception
								{
								$sql_exceptionlog="INSERT INTO wftasks_exceptions (wftasks_idwftasks,wftskflow_idwftskflow,idusrrole_from,idusrac_from,idusrrole_to,idusrac_to,wfprocassetsaccess_idwfprocassetsaccess,createdon,createdby) 
								VALUES ('".$fet_task['idwftasks']."','".$fet_task['wftskflow_idwftskflow']."','".$_SESSION['WkAtToMrPa_iduserrole']."','".$_SESSION['WkAtToMrPa_idacname']."','".$recepient_roleid."','".$recepient_usrid."','0','".$timenowis."','".$_SESSION['WkAtToMrPa_idacname']."')";
								mysql_query($sql_exceptionlog);
								}
							
							//echo $sql_task_details."<br>".$sql_update_task."<br>".$sql_update_msg."<br>".$sql_new_task;
							//exit;
							//if no error, then redirect to the correct page
//							echo "<br><Br><br>batch=>".$batch_no;
							//if there was a batch, then you need to include that in the process
								if ( (isset($batch_no)) && ($batch_no>0) && ($fet_ticket['wftasks_batch_idwftasks_batch']!=$batch_no) )
									 {
									//first, lets check if this ticket already belonged to another batch before removing it
									$res_tktin=mysql_query("SELECT idtktinPK,wftasks_batch_idwftasks_batch,tktcategory_idtktcategory FROM tktin WHERE idtktinPK=".$fet_ticket['idtktinPK']."  ");
									$fet_tktin=mysql_fetch_array($res_tktin);
											
									if ($fet_tktin['wftasks_batch_idwftasks_batch']>0)
										{
										//echo "huu";
										//update the tkt as well
										$sql_batchtkt="UPDATE tktin SET 
										wftasks_batch_idwftasks_batch='0',
										batch_number='0',
										voucher_number='0'
										WHERE idtktinPK=".$fet_ticket['idtktinPK']."";
										
										//update the countbatch
										$sql_updatecount_old="UPDATE wftasks_batch SET countbatch=(countbatch-1) WHERE idwftasks_batch=".$fet_tktin['wftasks_batch_idwftasks_batch']."";
													
										//log audit 1
										$sql_audit1="INSERT INTO audit_wftasks_batch (action, actionby_idusrac, actionby_idusrrole, tktin_affected, batchid_old, batchid_new, result, browser_session, action_time, user_ip, user_ip_proxy) 
										VALUES ( 'MOVE', '".$_SESSION['WkAtToMrPa_idacname']."', '".$_SESSION['WkAtToMrPa_iduserrole']."', '".$fet_ticket['idtktinPK']."', '".$fet_tktin['wftasks_batch_idwftasks_batch']."', '".$batch_no."', 'OK', '".session_id()."', '".$timenowis."', '".$_SERVER['REMOTE_ADDR']."', '".$realip."')";
													
										} else {
										
										$sql_batchtkt="SELECT idtktinPK from tktin LIMIT 1";
										$sql_updatecount_old="SELECT idtktinPK from tktin LIMIT 1";
										
										//log audit 1
										$sql_audit1="INSERT INTO audit_wftasks_batch (action, actionby_idusrac, actionby_idusrrole, tktin_affected, batchid_old, batchid_new, result, browser_session, action_time, user_ip, user_ip_proxy) 
										VALUES ( 'NEW', '".$_SESSION['WkAtToMrPa_idacname']."', '".$_SESSION['WkAtToMrPa_iduserrole']."', '".$fet_ticket['idtktinPK']."', '0', '".$batch_no."', 'OK', '".session_id()."', '".$timenowis."', '".$_SERVER['REMOTE_ADDR']."', '".$realip."')";
										
										} //if found
											
									$res_batchtkt=mysql_query($sql_batchtkt);
									$res_updatecount_old=mysql_query($sql_updatecount_old);
									$res_audit1=mysql_query($sql_audit1);
													
									//check the last batch_no
									$res_batchmeta=mysql_query("SELECT usrteamzone_idusrteamzone,wftasks_batchtype_idwftasks_batchtype FROM wftasks_batch WHERE idwftasks_batch=".$batch_no."");
									$fet_batchmeta=mysql_fetch_array($res_batchmeta);
									//changed to get the last max id given for this batch
						//			$sql_lastbatchno="SELECT max(voucher_number) as countbatch FROM tktin WHERE wftasks_batch_idwftasks_batch=".$batch_no."";
									$sql_lastbatchno="SELECT max(tktin.voucher_number) as countbatch,wftasks_batch.wftasks_batchtype_idwftasks_batchtype,wftasks_batch.batch_year FROM tktin
									INNER JOIN wftasks_batch ON tktin.wftasks_batch_idwftasks_batch=wftasks_batch.idwftasks_batch
									WHERE wftasks_batch.wftasks_batchtype_idwftasks_batchtype=".$fet_batchmeta['wftasks_batchtype_idwftasks_batchtype']."
									AND wftasks_batch.usrteamzone_idusrteamzone=".$fet_batchmeta['usrteamzone_idusrteamzone']."
									AND wftasks_batch.usrteamzone_idusrteamzone=".$_SESSION['WkAtToMrPa_userteamzoneid']."  ";//AND YEAR(createdon)='".$this_year."'
									$res_lastbatchno=mysql_query($sql_lastbatchno);
									$fet_lastbatchno=mysql_fetch_array($res_lastbatchno);
									
									//quick validation to avoid crossing over to another year in an older batch
									if ( ($fet_lastbatchno['countbatch']!='') && ($fet_lastbatchno['batch_year']!=$this_year) )
										{
										$error_batchoutdated="<div style=\"color:#ff0000\">You can't assign ".$fet_lastbatchno['batch_year']." in ".$this_year."  </div>";
										//exit;
										}
									
									//create the new batch_no
									$new_batchno=($fet_lastbatchno['countbatch']+1);							
									
									//new update the batch_no meta table
									$sql_updatecount="UPDATE wftasks_batch SET countbatch=(countbatch+1) WHERE idwftasks_batch=".$batch_no."";
									$res_updatecount=mysql_query($sql_updatecount);
									
									//get the tktid to update the tktin as well
									$sql_tktin=mysql_query("SELECT tktin_idtktin FROM wftasks WHERE idwftasks=".$_SESSION['wtaskid']." ");
									$fet_tktin=mysql_fetch_array($sql_tktin);
									
									//update the tkt as well
									$sql_batchtktnew="UPDATE tktin SET 
									wftasks_batch_idwftasks_batch='".$batch_no."',
									batch_number='".$new_batchno."',
									voucher_number='".$new_batchno."'
									WHERE idtktinPK=".$fet_ticket['idtktinPK']."";
									$res_batchtktnew=mysql_query($sql_batchtktnew);
									
									} else { //else if no batch now, then create some dummy queries to run the transction commit succssfully
									////////
									$res_tktin=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_batchtkt=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_lastbatchno=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_updatecount=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$sql_tktin=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_batchtktnew=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_batchtkt=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_updatecount_old=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_audit1=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_batchmeta=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									/////
									}//batch now
							
							
							if ( (!isset($error_formdata)) && ($fet_task_details['wftaskstrac_idwftaskstrac']>0) )
								{
							//	echo "1>".$res_task_details."<br>2>".$query_1."<br>3>".$query_2."<br>4>".$query_3."<br>4>".$error_formdata."<br>5>".$res_tktin."<br>6>".$upload_error_1."<br>7>".$upload_error_2."<br>8>".$upload_error_3."<br>9>".$upload_error_4."<br>10>".$res_batchtkt."<br>11>".$res_lastbatchno."<br>12>".$res_updatecount."<br>13>".$sql_tktin."<br>14>".$res_batchtktnew."<br>15>".$res_batchtkt."<br>16>".$res_updatecount_old."<br>17>".$res_audit1;
							//	echo $res_batchtktnew."<br>";
							//	echo $sql_updatecount_old;
								if ( ($res_task_details) && ($query_1) && ($query_2) && ($query_3)  && (!isset($error_formdata)) && ($res_tktin) && (!isset($upload_error_1)) && (!isset($upload_error_2)) && (!isset($upload_error_3)) && (!isset($upload_error_4)) && ($res_batchtkt) && ($res_lastbatchno) && ($res_updatecount) && ($sql_tktin) && ($res_batchtktnew) && ($res_batchtkt) && ($res_updatecount_old) && ($res_audit1) && ($res_batchmeta) && (!isset($error_batchoutdated)) )
									{
									mysql_query("COMMIT");	
									///////////////////////////// close check and insert new subscriber ////////////////////////////
									//redirect to the correct page
									?>
									<script language="javascript">
									window.location='mytasks_history.php?tkt=<?php echo $fet_task['tktin_idtktin'];?>&task=<?php echo $fet_task['idwftasks'];?>';
									</script>
									<?php
									exit;
									} else {
									mysql_query("ROLLBACK");
									//$error_system="<div class=\"msg_warning_small\">Sorry! Please try again</div>";
									?>
                                    <script language="javascript">
									 alert ('Sorry! Please Try Again!');
									</script>
                                    <?php
									if (isset($query_1)) { mysql_free_result($query_1);  }
									if (isset($query_2)) { mysql_free_result($query_2);  }
									if (isset($query_3)) { mysql_free_result($query_3); }
									mysql_free_result($res_task_details);	
									} //if the no error 1_1

								} //if no error form data
								
							} //close no error on action 2
				
				} //close action 2
				
				
				
				
///////////////  ACTION 4  ///////////////////////////////////////////////////////////////////////////////////////////////////
				
				if ($tktaction==4) { //Select Task action 4 ie: Invalidate Task
				
					//validate
					if (strlen($tkttskmsg4) < 1)
						{
						$error_4_1="<div class=\"msg_warning_small\">".$msg_warn_msgmis."</div>";
						}
						
					if ($tktinvalidid=="-1") //invalid has to be categorised 
						{
						$error_4_3="<div class=\"msg_warning_small\">".$msg_warning_tktinvalid."</div>";
						}
						
					if (($tktinvalidid=="0") && (strlen($tktinvalidnew)<1) ) 
						{
								
						$error_4_4="<div class=\"msg_warning_small\">".$msg_warning_invalidincomplete."</div>";

						}
					
						
					if ( (!isset($error_4_1)) &&  (!isset($error_4_3)) && (!isset($error_4_4)) )//if the no error 
						{
						
						mysql_query("BEGIN");
						
						//if it is a new invalidation category, then add and retrieve it's id
						if (($tktinvalidid=="0") && (!isset($error_4_4)))
							{
							//insert
							$sql_newinvalid="INSERT INTO wftskinvalidlist (wfttaskinvalidlistlbl,createdby,createdon)
							VALUES ('".$tktinvalidnew."','".$_SESSION['WkAtToMrPa_idacname']."','".$timenowis."')";
							mysql_query($sql_newinvalid);
							
							//retrieve
							$sql_invid="SELECT idwftskinvalidlist FROM wftskinvalidlist WHERE createdby=".$_SESSION['WkAtToMrPa_idacname']." ORDER BY idwftskinvalidlist DESC LIMIT 1";
							$res_invid=mysql_query($sql_invid);
							$fet_invid=mysql_fetch_array($res_invid);
							
							$idinvalidcat=$fet_invid['idwftskinvalidlist'];
							
							}
							
							if ($tktinvalidid >0) //invalid greater than zero 
								{
								$idinvalidcat = $tktinvalidid;
								}
							
						//insert invalidity record
						$sql_invalidrec="INSERT INTO wftskinvalid (wftskinvalidlist_idwftskinvalidlist,wftasks_idwftasks,createdby,createdon) 
						VALUES ('".$idinvalidcat."','".$_SESSION['wtaskid']."','".$_SESSION['WkAtToMrPa_idacname']."','".$timenowis."')";
						$query_2=mysql_query($sql_invalidrec);
						
						//update this task 
						$sql_update_task="UPDATE wftasks SET wftskstatustypes_idwftskstatustypes='4',wftskstatusglobal_idwftskstatusglobal='3',timeactiontaken='".$timenowis."',actedon_idusrrole=".$_SESSION['WkAtToMrPa_iduserrole'].", actedon_idusrac='".$_SESSION['WkAtToMrPa_idacname']."'  WHERE idwftasks=".$_SESSION['wtaskid']." LIMIT 1";
						$query_3=mysql_query($sql_update_task);
						
						//create an update message on the record
						$sql_update_msg="INSERT INTO wftskupdates (wftaskstrac_idwftaskstrac,usrrole_idusrrole,usrac_idusrac,wftskstatusglobal_idwftskstatusglobal,wftskstatustypes_idwftskstatustypes,wftasks_idwftasks,wftskupdate,createdby,createdon) 
						VALUES ('".$fet_idtask['wftaskstrac_idwftaskstrac']."','".$_SESSION['WkAtToMrPa_iduserrole']."','".$_SESSION['WkAtToMrPa_idacname']."','3','4','".$_SESSION['wtaskid']."','".$tkttskmsg4.$tktsms_record."','".$_SESSION['WkAtToMrPa_idacname']."','".$timenowis."')";
						$query_4=mysql_query($sql_update_msg);
						//echo $sql_update_msg."<br>";
						
						//check if there is a form data and if so, go ahead and process this transaction with inserts or updates
					if ( (isset($_POST['formdata_available'])) && ($_POST['formdata_available']==1) )
						{
						//echo "processed <br>";
						//check the db for this field by reusing the sql statement above
			/*			
						$sql_val="SELECT idwfprocassetsaccess,assetname,perm_read,perm_write,perm_required,wfprocassets.wfprocdtype_idwfprocdtype,idwfprocassets FROM wfprocassetsaccess 
						INNER JOIN wfprocassets ON wfprocassetsaccess.wfprocassets_idwfprocassets=wfprocassets.idwfprocassets
						WHERE wftskflow_idwftskflow=".$fet_task['wftskflow_idwftskflow']." AND wfprocassetsaccess.perm_read=1 ORDER BY wfprocassets.ordering ASC";
			*/
						$sql_val="SELECT idwfprocassetsaccess,assetname,perm_read,perm_write,perm_required,wfprocassets.wfprocdtype_idwfprocdtype,idwfprocassets,wfprocassetsgrouplbl,sysprofiles_idsysprofiles,wfprocassetsaccess.wfprocforms_idwfprocforms
						FROM wfprocassetsaccess
						INNER JOIN wfprocassets ON wfprocassetsaccess.wfprocassets_idwfprocassets = wfprocassets.idwfprocassets
						INNER JOIN wfprocassetsgroup ON wfprocassets.wfprocassetsgroup_idwfprocassetsgroup = wfprocassetsgroup.idwfprocassetsgroup
						INNER JOIN wfprocforms_cats ON wfprocassetsaccess.wfprocforms_idwfprocforms = wfprocforms_cats.wfprocforms_idwfprocforms 
						WHERE sysprofiles_idsysprofiles=".$_SESSION['WkAtToMrPa_iduserprofile']." AND wfprocforms_cats.tktcategory_idtktcategory=".$fet_ticket['tktcategory_idtktcategory']." AND wfprocassetsaccess.perm_read=1 ORDER BY wfprocassetsgrouplbl ASC,ordering ASC";
			
						$res_val=mysql_query($sql_val);
						$num_val=mysql_num_rows($res_val);
						$fet_val=mysql_fetch_array($res_val);
//	echo $sql_val;
						if ($num_val > 0) //if there are some values, then
							{
							do {
							//master-checklist if  | it is required | there is a value | the data type to determine the field |  whether an update or insert
							
							//validate required
						//	echo "validation ";
						//	echo $_POST['required_'.$fet_val['idwfprocassetsaccess'].'']."<br>";
						//	echo $_POST['item_'.$fet_val['idwfprocassetsaccess'].''];
								if (
								(isset($_POST['required_'.$fet_val['idwfprocassetsaccess'].''])) 
								&& ($_POST['required_'.$fet_val['idwfprocassetsaccess'].'']==1) 
								&&  ($_POST['item_'.$fet_val['idwfprocassetsaccess'].'']=="")   
								)
									{
									//echo $_POST['item_'.$fet_val['idwfprocassetsaccess'].'']."<br>";
									$error_formdata=1;
									echo "<div class=\"msg_warning_small\"> Form : ".$fet_val['assetname']." is required</div> | <a href=\"mytasks_history.php\">Back to Task View</a>";
									
									}
								
							//if no error on the dataform, then process
							if (!isset($error_formdata))
								{	
								if ($_POST['transtype_'.$fet_val['idwfprocassetsaccess'].'']=="INSERT")
									{
									//check the form item type first
									$ttype=$_POST['itemtype_'.$fet_val['idwfprocassetsaccess'].''];
										
										if (($ttype==1) || ($ttype==4) || ($ttype==5) || ($ttype==6) || ($ttype==7) || ($ttype==8) || ($ttype==9) || ($ttype==10)   ) //if textbox OR yes/no OR datepicker OR datetimepicker
											{
											$fvalue=mysql_real_escape_string(trim($_POST['item_'.$fet_val['idwfprocassetsaccess'].'']));
											
											//then process as below
											$sql_insert="INSERT INTO wfassetsdata (wfprocassetsaccess_idwfprocassetsaccess,
											wfprocassetschoice_idwfprocassetschoice,
											wfprocassets_idwfprocassets,
											wftasks_idwftasks,
											value_choice,
											value_path,
											wftaskstrac_idwftaskstrac,
											tktin_idtktin,
											createdby,
											createdon)
											VALUES ('".$fet_val['idwfprocassetsaccess']."',
											'0',
											'".$fet_val['idwfprocassets']."',
											'".$_SESSION['wtaskid']."',
											'".$fvalue."',
											'',
											'".$_SESSION['wftaskstrac']."',
											'".$_SESSION['tktin_idtktin']."',
											'".$_SESSION['WkAtToMrPa_idacname']."',
											'".$timenowis."'
											)";
											
											mysql_query($sql_insert);
											//echo $sql_insert;
											//exit;
											}
											
										if ($ttype==2)//if menulist
											{
											$fvalue=mysql_real_escape_string(trim($_POST['item_'.$fet_val['idwfprocassetsaccess'].'']));
											
											$sql_insert="INSERT INTO wfassetsdata (wfprocassetsaccess_idwfprocassetsaccess,
											wfprocassetschoice_idwfprocassetschoice,
											wfprocassets_idwfprocassets,
											wftasks_idwftasks,
											value_choice,
											value_path,
											wftaskstrac_idwftaskstrac,
											tktin_idtktin,
											createdby,
											createdon)
											VALUES ('".$fet_val['idwfprocassetsaccess']."',
											'".$fvalue."',
											'".$fet_val['idwfprocassets']."',
											'".$_SESSION['wtaskid']."',
											'',
											'',
											'".$_SESSION['wftaskstrac']."',
											'".$_SESSION['tktin_idtktin']."',
											'".$_SESSION['WkAtToMrPa_idacname']."',
											'".$timenowis."'
											)";
											
											mysql_query($sql_insert);
											
											}
											
										if ( ($ttype==3) && (isset($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])) && (strlen($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])>4) )//if file upload".$_SESSION['tktin_idtktin']."
											{
											$fvalue_upload=basename($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"]);
											$target_dir = "../documents/task_docs/".$today."/";
//											$docname=$_SESSION['tktin_idtktin']."_".basename($_FILES["fileToUpload"]["name"]);
											$docname=$_SESSION['tktin_idtktin']."_".$fvalue_upload;
											//we need to seed the document to make it unique_
											//lets include the ticket_ref number of the task to the name of the file
											$target_file = $target_dir . $docname;
											$uploadOk = 1;
											//just keep the file name only
											$file_name_only=$_SESSION['tktin_idtktin']."_".$_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"];
											$file_size_only=$_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["size"];
											//check if there is any document before proceeding
											if (strlen($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])>4)
												{
												
												$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
												
												//validation before uploading										 
												//check if file exists
												if (file_exists($target_file)) 
													{
													$upload_error_1 = "<div class=\"msg_warning_small\">File Missing</div>";
													}
												
												if ($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["size"] > 10485760) 
													{
													$upload_error_2 = "<div class=\"msg_warning_small\">File Max Size Exceeded( 10 MB)</div>";
													}
												
												if	($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
													&& $imageFileType != "gif" && $imageFileType != "doc" && $imageFileType != "docx" 
													&& $imageFileType != "pdf" && $imageFileType != "xls" && $imageFileType != "xlsx" 
													&& $imageFileType != "ppt" && $imageFileType != "pptx"  && $imageFileType != "csv"    ) {

														
													$upload_error_3 = "<div class=\"msg_warning_small\">Sorry, file format [".$imageFileType."] not allowed</div>";
													}
												//echo $upload_error_1.$upload_error_2.$upload_error_3;	
												//echo "Size -->".$_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["size"];
												if ( (!isset($upload_error_1)) && (!isset($upload_error_2)) && (!isset($upload_error_3)) )
													{
													 if (move_uploaded_file($_FILES['item_'.$fet_val['idwfprocassetsaccess'].'']["tmp_name"], $target_file)) 
														{
														$upload_success=1;
														//log the record into the Database
														$sql_insert="INSERT INTO wfassetsdata (wfprocassetsaccess_idwfprocassetsaccess,
														wfprocassetschoice_idwfprocassetschoice,
														wfprocassets_idwfprocassets,
														wftasks_idwftasks,
														value_choice,
														value_path,
														wftaskstrac_idwftaskstrac,
														tktin_idtktin,
														createdby,
														createdon)
														VALUES ('".$fet_val['idwfprocassetsaccess']."',
														'0',
														'".$fet_val['idwfprocassets']."',
														'".$_SESSION['wtaskid']."',
														'',
														'".$file_name_only."',
														'".$_SESSION['wftaskstrac']."',
														'".$_SESSION['tktin_idtktin']."',
														'".$_SESSION['WkAtToMrPa_idacname']."',
														'".$timenowis."'
														)";
														
														mysql_query($sql_insert);
														
														//create the audit log
														$sql_audit="INSERT INTO audit_docuploads ( doc_name, doc_ext, doc_size, tktin_idtktin, createdon, createdby, usersess, usrip, wfprocassets_idwfprocassets) 
														VALUES ('".$file_name_only."', '".$imageFileType."', '".$file_size_only."', '".$_SESSION['tktin_idtktin']."', '".$timenowis."', '".$_SESSION['WkAtToMrPa_idacname']."', '".session_id()."', '".$_SERVER['REMOTE_ADDR']."','".$fet_val['idwfprocassets']."')";
														mysql_query($sql_audit);
														
														} else {
															$upload_error_4 = "<div class=\"msg_warning_small\">Sorry, we are unable to upload that file</div>";
														}
													} //no error
												} //where strlen > 4
											} //type==3
											
									
									}
									
								if ($_POST['transtype_'.$fet_val['idwfprocassetsaccess'].'']=="UPDATE")
									{
									$ttype=$_POST['itemtype_'.$fet_val['idwfprocassetsaccess'].''];
									$itempk=mysql_real_escape_string(trim($_POST['itempk_'.$fet_val['idwfprocassetsaccess'].'']));
									
									//value captured - //this hack for checkbox
									if ((($ttype==3)||($ttype==4)) && (!isset($_POST['item_'.$fet_val['idwfprocassetsaccess'].''])) )
										{
										$fvalue=0;
										} else {
										$fvalue=mysql_real_escape_string(trim($_POST['item_'.$fet_val['idwfprocassetsaccess'].'']));
										}
									
									//only if there are records
									if (
										( ($fvalue > 0) || (strlen($fvalue) > 0) && ($ttype!=4) ) 
										|| 
										( ($ttype==4) && (($fvalue=='') || ($fvalue==0) || ($fvalue!=0)) ) 
										) 
									/*if ( ($fvalue!='') && (strlen($fvalue) > 0) )*/
										{	
									//check the form item type first

									if (($ttype==1) || ($ttype==4) || ($ttype==5) || ($ttype==6) || ($ttype==7) || ($ttype==8) || ($ttype==9) || ($ttype==10)   ) //if textbox OR yes/no OR datepicker OR datetimepicker
											{
											
											///audit log
											$sql_auditlog_form="INSERT INTO audit_wfassetsdata (idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice_prev, wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice_prev, value_choice_new, value_path_prev, value_path_new, wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon, modifiedby_new, modifiedon_new) 
											SELECT idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice, wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice, '".$fvalue."', value_path,'', wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon,".$_SESSION['WkAtToMrPa_idacname'].",'".$timenowis."' 
											FROM wfassetsdata
											WHERE idwfassetsdata=".$itempk." AND value_choice!='".$fvalue."' ";
											//echo $sql_auditlog_form."<br>";
											mysql_query($sql_auditlog_form);
												
											//then process as below
											$sql_update="UPDATE wfassetsdata SET 
											value_choice='".$fvalue."',
											wftaskstrac_idwftaskstrac='".$_SESSION['wftaskstrac']."',
											tktin_idtktin='".$_SESSION['tktin_idtktin']."',
											modifiedby='".$_SESSION['WkAtToMrPa_idacname']."',
											modifiedon='".$timenowis."'
											WHERE idwfassetsdata=".$itempk." LIMIT 1";
											
											mysql_query($sql_update);
											//echo $sql_update;
											}
									
									if ( $ttype==2)//if menulist
											{
											//enter the audit trail only if there is a change
											$sql_auditlog_form="INSERT INTO audit_wfassetsdata (idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice_prev, wfprocassetschoice_idwfprocassetschoice_new, wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice_prev, value_choice_new, value_path_prev, value_path_new, wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon, modifiedby_new, modifiedon_new) 
											SELECT idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice,'".$fvalue."', wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice, '', value_path,'', wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon,".$_SESSION['WkAtToMrPa_idacname'].",'".$timenowis."' 
											FROM wfassetsdata
											WHERE idwfassetsdata=".$itempk." AND wfprocassetschoice_idwfprocassetschoice!='".$fvalue."' ";
											//echo $sql_auditlog_form."<br>";
											mysql_query($sql_auditlog_form);
											
											$sql_update="UPDATE wfassetsdata SET 
											wfprocassetschoice_idwfprocassetschoice='".$fvalue."',
											wftaskstrac_idwftaskstrac='".$_SESSION['wftaskstrac']."',
											tktin_idtktin='".$_SESSION['tktin_idtktin']."',
											modifiedby='".$_SESSION['WkAtToMrPa_idacname']."',
											modifiedon='".$timenowis."'
											WHERE idwfassetsdata=".$itempk." LIMIT 1";
											
											mysql_query($sql_update);
											}
											
									if ( ($ttype==3) && (isset($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])) && (strlen($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])>4) )//if file upload".$_SESSION['tktin_idtktin']."
											{
											$fvalue_upload=basename($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"]);
											$target_dir = "../documents/task_docs/".$today."/";
//											$docname=$_SESSION['tktin_idtktin']."_".basename($_FILES["fileToUpload"]["name"]);
											$docname=$_SESSION['tktin_idtktin']."_".$fvalue_upload;
											//we need to seed the document to make it unique_
											//lets include the ticket_ref number of the task to the name of the file
											$target_file = $target_dir . $docname;
											$uploadOk = 1;
											$file_name_only=$_SESSION['tktin_idtktin']."_".$_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"];
											$file_size_only=$_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["size"];

											////check if there is any document before proceeding
											if (strlen($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])>4)
												{
											
													$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
													
													//validation before uploading											 
													//check if file exists
													if (file_exists($target_file)) 
														{
														$upload_error_1 = "<div class=\"msg_warning_small\">File Missing</div>";
														}
													
													if ($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["size"] > 10485760) 
														{
														$upload_error_2 = "<div class=\"msg_warning_small\">File Max Size Exceeded( 10 MB)</div>";
														}
													
													if	($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
														&& $imageFileType != "gif" && $imageFileType != "doc" && $imageFileType != "docx" 
														&& $imageFileType != "pdf" && $imageFileType != "xls" && $imageFileType != "xlsx" 
														&& $imageFileType != "ppt" && $imageFileType != "pptx" && $imageFileType != "csv"    ) {
															
														$upload_error_3 = "<div class=\"msg_warning_small\">Sorry, file format [".$imageFileType."] not allowed</div>";
														}

													if ( (!isset($upload_error_1)) && (!isset($upload_error_2)) && (!isset($upload_error_3)) )
														{
														//echo "soo farr soo good";
														 if (move_uploaded_file($_FILES['item_'.$fet_val['idwfprocassetsaccess'].'']["tmp_name"], $target_file)) 
															{
															$upload_success=1;
															
															$sql_auditlog_form="INSERT INTO audit_wfassetsdata (idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice_prev, wfprocassetschoice_idwfprocassetschoice_new, wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice_prev, value_choice_new, value_path_prev, value_path_new, wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon, modifiedby_new, modifiedon_new) 
															SELECT idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice,'', wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice, '', value_path,'".$file_name_only."', wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon,".$_SESSION['WkAtToMrPa_idacname'].",'".$timenowis."' 
															FROM wfassetsdata
															WHERE idwfassetsdata=".$itempk." AND value_path!='".$file_name_only."' ";
															//echo $sql_auditlog_form."<br>";
															mysql_query($sql_auditlog_form);
															
															//log the record into the Database
															$sql_update="UPDATE wfassetsdata SET 
															value_path='".$file_name_only."',
															modifiedby='".$_SESSION['WkAtToMrPa_idacname']."',
															modifiedon='".$timenowis."'
															WHERE idwfassetsdata=".$itempk." LIMIT 1";

															mysql_query($sql_update);
															
															//create the audit log
															$sql_audit="INSERT INTO audit_docuploads ( doc_name, doc_ext, doc_size, tktin_idtktin, createdon, createdby, usersess, usrip,wfprocassets_idwfprocassets) 
															VALUES ('".$file_name_only."', '".$imageFileType."', '".$file_size_only."', '".$_SESSION['tktin_idtktin']."', '".$timenowis."', '".$_SESSION['WkAtToMrPa_idacname']."', '".session_id()."', '".$_SERVER['REMOTE_ADDR']."',".$itempk.")";
															mysql_query($sql_audit);
																											
															} else {
																$upload_error_4 = "<div class=\"msg_warning_small\">Sorry, we are unable to upload that file</div>";
															}
														} //no error
													} // if fvalue strlen>4
											
											} //type==3	
										}	
									
									} //end update
									
								}
								
							} while ($fet_val=mysql_fetch_array($res_val));
							
							} //if record is > 0
						
						} //close form data checker
						
						//Feedback SMS to send customer/sender a message
						if (  (isset($tktsms)) && (strlen($tktsms)>15) && (strlen($tktsenderphone)==12) )
							{
							$sql_smsout="INSERT INTO mdata_out_sms (destnumber,msgtext)  
							VALUES ('".$tktsenderphone."','".$tktno." ".$tktsms."')";
							mysql_query($sql_smsout);
							}
					
							//Update the ticket status
							
								
								$sql_updatetkt="UPDATE tktin SET 
								tktstatus_idtktstatus='5',
								timeclosed='".$timenowis."'
								WHERE idtktinPK=".$_SESSION['tktupdate']." 
								LIMIT 1";								
								$query_5=mysql_query($sql_updatetkt);
								

							//notify if anyone is to be notified
							$sql_notify="SELECT idwfnotification,wfnotification.tktstatus_idtktstatus,usrrole_idusrrole,wftskflow_idwftskflow,notify_system,notify_email,notify_sms,idtktmsgs,tktmsg_sms,tktmsg_email,tktmsg_dashboard FROM wfnotification 
							INNER JOIN tktmsgs ON wfnotification.idwfnotification=tktmsgs.wfnotification_idwfnotification
							WHERE wftskflow_idwftskflow=".$_SESSION['thistskflow']." ORDER BY idwfnotification ASC";
							$res_notify=mysql_query($sql_notify);
							$num_notify=mysql_num_rows($res_notify);
							$fet_notify=mysql_fetch_array($res_notify);
							
							if ($num_notify > 0 ) // if there is a notification setting
								{
								do {			
								//check for each of the settings 
									if ( ($fet_notify['notify_system']==1) && (strlen($fet_notify['tktmsg_dashboard'])>2) ) //system dashboard set on
										{
										$sql_dash="INSERT INTO tktmsglogs_dashboard (tktmsgs_idtktmsgs,msgto_roleid,msgto_subject,msgto_body,createdon,readon)
										VALUES ('".$fet_notify['idtktmsgs']."','".$fet_notify['usrrole_idusrrole']."','Notification - Tkt No : ".$tktno.", ".$fet_ticketcat['tktcategoryname'].",'".$fet_notify['tktmsg_dashboard']." - ".$fet_ticketcat['tktcategoryname']."','".$timenowis."','0000-00-00 00:00:00')";
										mysql_query($sql_dash);									
										}// system dashboard set on
												
										//get this roles email address and phone numbers
										//ensure the account is active as well...
										$sql_rolecontacts="SELECT usremail,usrphone FROM usrac WHERE usrrole_idusrrole=".$fet_notify['usrrole_idusrrole']." AND acstatus=1 LIMIT 1";
										$res_rolecontacts=mysql_query($sql_rolecontacts);
										$fet_rolecontacts=mysql_fetch_array($res_rolecontacts);
										$num_rolecontacts=mysql_num_rows($res_rolecontacts);
										
										if ( ($fet_notify['notify_email']==1) && ($num_rolecontacts>0) && (strlen($fet_rolecontacts['usremail'])>6) && (strlen($fet_notify['tktmsg_email'])>2) )//email set on
											{
											$sql_email="INSERT INTO tktmsgslog_emails(tktmsgs_idtktmsgs,emailto,emailsubject,emailbody,createdon,senton) 
											VALUES ('".$fet_notify['idtktmsgs']."','".$fet_rolecontacts['usremail']."','Notification - Tkt No : ".$tktno.", ".$fet_ticketcat['tktcategoryname']."','".$fet_notify['tktmsg_email']." - Tkt No:".$tktno.", ".$fet_ticketcat['tktcategoryname']."','".$timenowis."','0000-00-00 00:00:00')";
												
											mysql_query($sql_email);
											}
											
											if ( ($fet_notify['notify_sms']==1) && ($num_rolecontacts>0) && (strlen($fet_rolecontacts['usrphone'])==13) )
											{
											$sql_sms="INSERT INTO mdata_out_sms (destnumber,msgtext) 
											VALUES ('".$fet_rolecontacts['usrphone']."',' Auto Notification - Tkt No:".$tktno.", ".$fet_ticketcat['tktcategoryname']." received')";
					
											mysql_query($sql_sms);
											}
												
									} while ($fet_notify=mysql_fetch_array($res_notify));								
										
								} //close - if there is a notification setting
					
					/////////////////////////////check and insert a new subscriber
					if ($fet_task['usrrole_idusrrole']==2) //if this is the first ticket from the customer, then do this...
						{
						//check if a subscriber with the same credentials matches
						$sql_subis="SELECT idsmssubs FROM ".$_SESSION['WkAtToMrPa_tblsmsbc']." WHERE subnumber='".$tktsenderphone."' AND usrtype=1";
						$res_subis=mysql_query($sql_subis);
						$num_subis=mysql_num_rows($res_subis);
						
						//if not, add the new credentials
						if ($num_subis==0)
							{
							$sql_subnew="INSERT INTO ".$_SESSION['WkAtToMrPa_tblsmsbc']." (wftskid,tktid,subnumber,idloctown,idusrteamzone,usrtype,createdon,createdby)
							VALUES ('".$_SESSION['wtaskid']."','".$fet_task['tktin_idtktin']."','".$fet_confirmloc['idloctowns']."','".$_SESSION['WkAtToMrPa_userteamzoneid']."','1','".$timenowis."','".$_SESSION['WkAtToMrPa_idacname']."')";
							mysql_query($sql_subnew);
							}
						}
					///////////////////////////// close check and insert new subscriber ////////////////////////////
					
if ( (isset($batch_no)) && ($batch_no>0) && ($fet_ticket['wftasks_batch_idwftasks_batch']!=$batch_no) )
										{
									//first, lets check if this ticket already belonged to another batch before removing it
									$res_tktin=mysql_query("SELECT idtktinPK,wftasks_batch_idwftasks_batch,tktcategory_idtktcategory FROM tktin WHERE idtktinPK=".$fet_ticket['idtktinPK']."  ");
									$fet_tktin=mysql_fetch_array($res_tktin);
											
									if ($fet_tktin['wftasks_batch_idwftasks_batch']>0)
										{
										//update the tkt as well
										$sql_batchtkt="UPDATE tktin SET 
										wftasks_batch_idwftasks_batch='0',
										batch_number='0',
										voucher_number='0'
										WHERE idtktinPK=".$fet_ticket['idtktinPK']."";
										
										//update the countbatch
										$sql_updatecount_old="UPDATE wftasks_batch SET countbatch=(countbatch-1) WHERE idwftasks_batch=".$fet_tktin['wftasks_batch_idwftasks_batch']."";
													
										//log audit 1
										$sql_audit1="INSERT INTO audit_wftasks_batch (action, actionby_idusrac, actionby_idusrrole, tktin_affected, batchid_old, batchid_new, result, browser_session, action_time, user_ip, user_ip_proxy) 
										VALUES ( 'MOVE', '".$_SESSION['WkAtToMrPa_idacname']."', '".$_SESSION['WkAtToMrPa_iduserrole']."', '".$fet_ticket['idtktinPK']."', '".$fet_tktin['wftasks_batch_idwftasks_batch']."', '".$batch_no."', 'OK', '".session_id()."', '".$timenowis."', '".$_SERVER['REMOTE_ADDR']."', '".$realip."')";
													
										} else {
										
										$sql_batchtkt="SELECT idtktinPK from tktin LIMIT 1";
										$sql_updatecount_old="SELECT idtktinPK from tktin LIMIT 1";
										
										//log audit 1
										$sql_audit1="INSERT INTO audit_wftasks_batch (action, actionby_idusrac, actionby_idusrrole, tktin_affected, batchid_old, batchid_new, result, browser_session, action_time, user_ip, user_ip_proxy) 
										VALUES ( 'NEW', '".$_SESSION['WkAtToMrPa_idacname']."', '".$_SESSION['WkAtToMrPa_iduserrole']."', '".$fet_ticket['idtktinPK']."', '0', '".$batch_no."', 'OK', '".session_id()."', '".$timenowis."', '".$_SERVER['REMOTE_ADDR']."', '".$realip."')";
										
										}
											
									$res_batchtkt=mysql_query($sql_batchtkt);
									$res_updatecount_old=mysql_query($sql_updatecount_old);
									$res_audit1=mysql_query($sql_audit1);
							
									//check the last batch_no
									$res_batchmeta=mysql_query("SELECT usrteamzone_idusrteamzone,wftasks_batchtype_idwftasks_batchtype FROM wftasks_batch WHERE idwftasks_batch=".$batch_no."");
									$fet_batchmeta=mysql_fetch_array($res_batchmeta);
									//changed to get the last max id given for this batch
						//			$sql_lastbatchno="SELECT max(voucher_number) as countbatch FROM tktin WHERE wftasks_batch_idwftasks_batch=".$batch_no."";
									$sql_lastbatchno="SELECT max(tktin.voucher_number) as countbatch,wftasks_batch.wftasks_batchtype_idwftasks_batchtype,wftasks_batch.batch_year FROM tktin
									INNER JOIN wftasks_batch ON tktin.wftasks_batch_idwftasks_batch=wftasks_batch.idwftasks_batch
									WHERE wftasks_batch.wftasks_batchtype_idwftasks_batchtype=".$fet_batchmeta['wftasks_batchtype_idwftasks_batchtype']."
									AND wftasks_batch.usrteamzone_idusrteamzone=".$fet_batchmeta['usrteamzone_idusrteamzone']."
									AND wftasks_batch.usrteamzone_idusrteamzone=".$_SESSION['WkAtToMrPa_userteamzoneid']."  ";//AND YEAR(createdon)='".$this_year."'
									$res_lastbatchno=mysql_query($sql_lastbatchno);
									$fet_lastbatchno=mysql_fetch_array($res_lastbatchno);
									
									//quick validation to avoid crossing over to another year in an older batch
									if ( ($fet_lastbatchno['countbatch']!='') && ($fet_lastbatchno['batch_year']!=$this_year) )
										{
										$error_batchoutdated="<div style=\"color:#ff0000\">You can't assign ".$fet_lastbatchno['batch_year']." in ".$this_year."  </div>";
										//exit;
										}
									
									//create the new batch_no
									$new_batchno=($fet_lastbatchno['countbatch']+1);							
									
									//new update the batch_no meta table
									$sql_updatecount="UPDATE wftasks_batch SET countbatch=(countbatch+1) WHERE idwftasks_batch=".$batch_no."";
									$res_updatecount=mysql_query($sql_updatecount);
									
									//get the tktid to update the tktin as well
									$sql_tktin=mysql_query("SELECT tktin_idtktin FROM wftasks WHERE idwftasks=".$_SESSION['wtaskid']." ");
									$fet_tktin=mysql_fetch_array($sql_tktin);

									
									//update the tkt as well
									$sql_batchtktnew="UPDATE tktin SET 
									wftasks_batch_idwftasks_batch='".$batch_no."',
									batch_number='".$new_batchno."',
									voucher_number='".$new_batchno."'
									WHERE idtktinPK=".$fet_ticket['idtktinPK']."";
									$res_batchtktnew=mysql_query($sql_batchtktnew);
									
									} else { //else if no batch now, then create some dummy queries to run the transction commit succssfully
									////////
									$res_tktin=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_batchtkt=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_lastbatchno=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_updatecount=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$sql_tktin=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_batchtktnew=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_batchtkt=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_updatecount_old=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_audit1=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_batchmeta=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									/////
									}//batch now
					
					//redirect to the correct page
							if ( ($query_2) && ($query_3)  && ($query_4) && ($query_5)  && (!isset($error_formdata))  && ($res_tktin) && (!isset($upload_error_1)) && (!isset($upload_error_2)) && (!isset($upload_error_3)) && (!isset($upload_error_4)) && ($res_batchtkt) && ($res_lastbatchno) && ($res_updatecount) && ($sql_tktin) && ($res_batchtktnew) && ($res_batchtkt) && ($res_updatecount_old) && ($res_audit1) && ($res_batchmeta) && (!isset($error_batchoutdated)))
									{
									mysql_query("COMMIT");	
									///////////////////////////// close check and insert new subscriber ////////////////////////////
									//redirect to the correct page
									?>
									<script language="javascript">
									window.location='mytasks_history.php?tkt=<?php echo $fet_task['tktin_idtktin'];?>&task=<?php echo $fet_task['idwftasks'];?>&tab=2';
									</script>
									<?php
									exit;
									} else {
									mysql_query("ROLLBACK");
									?>
                                    <script language="javascript">
									 alert ('Sorry! Please Try Again!');
									</script>
                                    <?php
									if (isset($query_2)) { mysql_free_result($query_2);  }
									if (isset($query_3)) { mysql_free_result($query_3); }	
									mysql_free_result($query_4);
									} //if the no error 1_1
						
						
						} //close no error on action 4
				
				} //close action 4				
					

///////////////  ACTION 6  ///////////////////////////////////////////////////////////////////////////////////////////////////
				
				if ($tktaction==6) { //Select Task action 6 ie: status update
				
					//validate
					if (strlen($tkttskmsg6) < 1)
						{
						$error_6_1="<div class=\"msg_warning_small\">".$msg_warn_msgmis."</div>";
						}
						
						
					if (!isset($error_6_1) )//if the no error 
						{
						
						mysql_query("BEGIN");
						
						$tktnewdeadlinefinal=date("Y-m-d H:i:s",strtotime($tktnewdeadline_fin));

						//task details
						$sql_task_details = "SELECT wftasks.usrrole_idusrrole,wftasks.usrac_idusrac FROM wftasks 
						WHERE idwftasks=".$_SESSION['wtaskid']." LIMIT 1";
						$res_task_details = mysql_query($sql_task_details);
						$fet_task_details = mysql_fetch_array($res_task_details);
						
						//update this task 
						$sql_update_task="UPDATE wftasks SET wftskstatustypes_idwftskstatustypes='6',wftskstatusglobal_idwftskstatusglobal='2',timeactiontaken='".$timenowis."',actedon_idusrrole=".$_SESSION['WkAtToMrPa_iduserrole'].", actedon_idusrac='".$_SESSION['WkAtToMrPa_idacname']."'  WHERE idwftasks=".$_SESSION['wtaskid']." LIMIT 1";
						$query_1=mysql_query($sql_update_task);						
						
						//create an update message on the record
						$sql_update_msg="INSERT INTO wftskupdates (wftaskstrac_idwftaskstrac,usrrole_idusrrole,usrac_idusrac,wftskstatusglobal_idwftskstatusglobal,wftskstatustypes_idwftskstatustypes,wftasks_idwftasks,wftskupdate,createdby,createdon) 
						VALUES ('".$fet_idtask['wftaskstrac_idwftaskstrac']."','".$_SESSION['WkAtToMrPa_iduserrole']."','".$_SESSION['WkAtToMrPa_idacname']."','2','6','".$_SESSION['wtaskid']."','".$tkttskmsg6.$tktsms_record."','".$_SESSION['WkAtToMrPa_idacname']."','".$timenowis."')";
						$query_2=mysql_query($sql_update_msg);
						
						
					//send email to the person CC'D
						if ( (isset($_POST['progress_update_emails'])) && (strlen($_POST['progress_update_emails'])>5) )
							{
							$emailcc=mysql_real_escape_string(trim($_POST['progress_update_emails']));
							//ensure it is clean with correct email format
							if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $emailcc)) 
								{
								$error_ccmail="<div class=\"msg_warning_small\">Wrong email address</div>";
								
								} else {
								
								//go ahead and process
								$to = $emailcc;
						
								$message = "
								Dear Sir/Madam, <br>
								The message below was sent to you via .
								<br>
								".$tkttskmsg6."
								<br>
								To get further details, please log in to your  account at ".$url_absolute."
								<br><br>
								From ".$_SESSION['WkAtToMrPa_usrtitle']." ".$_SESSION['WkAtToMrPa_usrfname'].",<br>
								Support Team,<br>
								.
								<br><br>
								<div>
								DISCLAIMER: You received this email because your email address was used on .com
								The Information contained in this email, including the links, is intended solely for the use of the designated recipient.
								If you have received this e-mail message in error please notify the  team through e-mail myaccount@.com and delete it immediately
								</div>";
										
								// To send HTML mail, the Content-type header must be set
								$headers  = 'MIME-Version: 1.0' . "\r\n";
								$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
								
								// Additional headers
								$sendername=''.$_SESSION['WkAtToMrPa_usrfname'].' '.$_SESSION['WkAtToMrPa_usrlname'].'';
								//	$headers .= 'To: '..' <'.$youremail.'>' . "\r\n";
								$headers .= 'From: '.$_SESSION['WkAtToMrPa_usrfname'].' '.$_SESSION['WkAtToMrPa_usrlname'].' <'.$_SESSION['WkAtToMrPa_usremail'].'>' . "\r\n";
								//$headers .= 'Bcc: admin@.com' . "\r\n";
										
								$subject = " Advice ";
								// Mail it
								//mail($to, $subject, $message, $headers);
									
							//	if ($mailserver_avail==1)
							//		{
							//		mail($to,$subject,$message,$headers);
							//		} else {
									//if mail server is not available, then save the function in a variable and parse this to the online server for processing
									$sql_mailout="INSERT INTO mdata_emailsout (email_to,email_subject,email_message,email_headers,createdon) 
									VALUES ('".$to."','".$subject."','".$message."','".$headers."','".$timenowis."')";
									mysql_query($sql_mailout);
							//		}
								
								} //no email error
							
							} //if cc mail update is set
						
							//check if there is a form data and if so, go ahead and process this transaction with inserts or updates
						if ( (isset($_POST['formdata_available'])) && ($_POST['formdata_available']==1) )
							{
						//echo "processed <br>";
						//check the db for this field by reusing the sql statement above
						/*
						$sql_val="SELECT idwfprocassetsaccess,assetname,perm_read,perm_write,perm_required,wfprocassets.wfprocdtype_idwfprocdtype,idwfprocassets FROM wfprocassetsaccess 
						INNER JOIN wfprocassets ON wfprocassetsaccess.wfprocassets_idwfprocassets=wfprocassets.idwfprocassets
						WHERE wftskflow_idwftskflow=".$fet_task['wftskflow_idwftskflow']." AND wfprocassetsaccess.perm_read=1 ORDER BY wfprocassets.ordering ASC";
						*/
						$sql_val="SELECT idwfprocassetsaccess,assetname,perm_read,perm_write,perm_required,wfprocassets.wfprocdtype_idwfprocdtype,idwfprocassets,wfprocassetsgrouplbl,sysprofiles_idsysprofiles,wfprocassetsaccess.wfprocforms_idwfprocforms
						FROM wfprocassetsaccess
						INNER JOIN wfprocassets ON wfprocassetsaccess.wfprocassets_idwfprocassets = wfprocassets.idwfprocassets
						INNER JOIN wfprocassetsgroup ON wfprocassets.wfprocassetsgroup_idwfprocassetsgroup = wfprocassetsgroup.idwfprocassetsgroup
						INNER JOIN wfprocforms_cats ON wfprocassetsaccess.wfprocforms_idwfprocforms = wfprocforms_cats.wfprocforms_idwfprocforms 
						WHERE sysprofiles_idsysprofiles=".$_SESSION['WkAtToMrPa_iduserprofile']." AND wfprocforms_cats.tktcategory_idtktcategory=".$fet_ticket['tktcategory_idtktcategory']." AND wfprocassetsaccess.perm_read=1 ORDER BY wfprocassetsgrouplbl ASC,ordering ASC";

						$res_val=mysql_query($sql_val);
						$num_val=mysql_num_rows($res_val);
						$fet_val=mysql_fetch_array($res_val);
//	echo "sql val-->".$sql_val."<br><br>";
						if ($num_val > 0) //if there are some values, then
							{
							do {
							//master-checklist if  | it is required | there is a value | the data type to determine the field |  whether an update or insert
							
							//validate required
						//	echo "validation ";
						//	echo $_POST['required_'.$fet_val['idwfprocassetsaccess'].'']."<br>";
						//	echo $_POST['item_'.$fet_val['idwfprocassetsaccess'].''];
								if (
								(isset($_POST['required_'.$fet_val['idwfprocassetsaccess'].''])) 
								&& ($_POST['required_'.$fet_val['idwfprocassetsaccess'].'']==1) 
								&&  ($_POST['item_'.$fet_val['idwfprocassetsaccess'].'']=="")   
								)
									{
									//echo $_POST['item_'.$fet_val['idwfprocassetsaccess'].'']."<br>";
									$error_formdata=1;
									echo "<div class=\"msg_warning_small\"> Form : ".$fet_val['assetname']." is required | <a href=\"mytasks_history.php\">Back to Task View</a></div>";
									
									}
								
							//if no error on the dataform, then process
							if (!isset($error_formdata))
								{	
								if ($_POST['transtype_'.$fet_val['idwfprocassetsaccess'].'']=="INSERT")
									{
									//check the form item type first
									$ttype=$_POST['itemtype_'.$fet_val['idwfprocassetsaccess'].''];
																			
										if (($ttype==1) || ($ttype==4) || ($ttype==5) || ($ttype==6) || ($ttype==7) || ($ttype==8) || ($ttype==9) || ($ttype==10)) //if textbox OR yes/no OR datepicker OR datetimepicker
											{
											//
											$fvalue=mysql_real_escape_string(trim($_POST['item_'.$fet_val['idwfprocassetsaccess'].'']));
											
											//then process as below
											$sql_insert="INSERT INTO wfassetsdata (wfprocassetsaccess_idwfprocassetsaccess,
											wfprocassetschoice_idwfprocassetschoice,
											wfprocassets_idwfprocassets,
											wftasks_idwftasks,
											value_choice,
											value_path,
											wftaskstrac_idwftaskstrac,
											tktin_idtktin,
											createdby,
											createdon)
											VALUES ('".$fet_val['idwfprocassetsaccess']."',
											'0',
											'".$fet_val['idwfprocassets']."',
											'".$_SESSION['wtaskid']."',
											'".$fvalue."',
											'',
											'".$_SESSION['wftaskstrac']."',
											'".$_SESSION['tktin_idtktin']."',
											'".$_SESSION['WkAtToMrPa_idacname']."',
											'".$timenowis."'
											)";
											
											mysql_query($sql_insert);
										//	echo $sql_insert."<br>";

											//exit;
											}
											
										if ($ttype==2 )//if menulist
											{
											///
											$fvalue=mysql_real_escape_string(trim($_POST['item_'.$fet_val['idwfprocassetsaccess'].'']));
											//
											$sql_insert="INSERT INTO wfassetsdata (wfprocassetsaccess_idwfprocassetsaccess,
											wfprocassetschoice_idwfprocassetschoice,
											wfprocassets_idwfprocassets,
											wftasks_idwftasks,
											value_choice,
											value_path,
											wftaskstrac_idwftaskstrac,
											tktin_idtktin,
											createdby,
											createdon)
											VALUES ('".$fet_val['idwfprocassetsaccess']."',
											'".$fvalue."',
											'".$fet_val['idwfprocassets']."',
											'".$_SESSION['wtaskid']."',
											'',
											'',
											'".$_SESSION['wftaskstrac']."',
											'".$_SESSION['tktin_idtktin']."',
											'".$_SESSION['WkAtToMrPa_idacname']."',
											'".$timenowis."'
											)";
											
											mysql_query($sql_insert);
											
											}
										
										if ( ($ttype==3) && (isset($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])) && (strlen($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])>4) )//if file upload".$_SESSION['tktin_idtktin']."
											{
											$fvalue_upload=basename($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"]);
											$target_dir = "../documents/task_docs/".$today."/";
//											$docname=$_SESSION['tktin_idtktin']."_".basename($_FILES["fileToUpload"]["name"]);
											$docname=$_SESSION['tktin_idtktin']."_".$fvalue_upload;
											//we need to seed the document to make it unique_
											//lets include the ticket_ref number of the task to the name of the file
											$target_file = $target_dir . $docname;
											$uploadOk = 1;
											//just keep the file name only
											$file_name_only=$_SESSION['tktin_idtktin']."_".$_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"];
											$file_size_only=$_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["size"];
											//check if there is any document before proceeding
											if (strlen($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])>4)
												{
												
												$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
												
												//validation before uploading										 
												//check if file exists
												if (file_exists($target_file)) 
													{
													$upload_error_1 = "<div class=\"msg_warning_small\">File Missing</div>";
													}
												
												if ($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["size"] > 10485760) 
													{
													$upload_error_2 = "<div class=\"msg_warning_small\">File Max Size Exceeded( 10 MB)</div>";
													}
												
												if	($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
													&& $imageFileType != "gif" && $imageFileType != "doc" && $imageFileType != "docx" 
													&& $imageFileType != "pdf" && $imageFileType != "xls" && $imageFileType != "xlsx" 
													&& $imageFileType != "ppt" && $imageFileType != "pptx"  && $imageFileType != "csv"    ) {
														
													$upload_error_3 = "<div class=\"msg_warning_small\">Sorry, file format [".$imageFileType."] not allowed</div>";
													}
												//echo $upload_error_1.$upload_error_2.$upload_error_3;	
												//echo "Size -->".$_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["size"];
												if ( (!isset($upload_error_1)) && (!isset($upload_error_2)) && (!isset($upload_error_3)) )
													{
													 if (move_uploaded_file($_FILES['item_'.$fet_val['idwfprocassetsaccess'].'']["tmp_name"], $target_file)) 
														{
														$upload_success=1;
														//log the record into the Database
														$sql_insert="INSERT INTO wfassetsdata (wfprocassetsaccess_idwfprocassetsaccess,
														wfprocassetschoice_idwfprocassetschoice,
														wfprocassets_idwfprocassets,
														wftasks_idwftasks,
														value_choice,
														value_path,
														wftaskstrac_idwftaskstrac,
														tktin_idtktin,
														createdby,
														createdon)
														VALUES ('".$fet_val['idwfprocassetsaccess']."',
														'0',
														'".$fet_val['idwfprocassets']."',
														'".$_SESSION['wtaskid']."',
														'',
														'".$file_name_only."',
														'".$_SESSION['wftaskstrac']."',
														'".$_SESSION['tktin_idtktin']."',
														'".$_SESSION['WkAtToMrPa_idacname']."',
														'".$timenowis."'
														)";
														
														mysql_query($sql_insert);
														
														//create the audit log
														$sql_audit="INSERT INTO audit_docuploads ( doc_name, doc_ext, doc_size, tktin_idtktin, createdon, createdby, usersess, usrip, wfprocassets_idwfprocassets) 
														VALUES ('".$file_name_only."', '".$imageFileType."', '".$file_size_only."', '".$_SESSION['tktin_idtktin']."', '".$timenowis."', '".$_SESSION['WkAtToMrPa_idacname']."', '".session_id()."', '".$_SERVER['REMOTE_ADDR']."','".$fet_val['idwfprocassets']."')";
														mysql_query($sql_audit);
														
														} else {
															$upload_error_4 = "<div class=\"msg_warning_small\">Sorry, we are unable to upload that file</div>";
														}
													} //no error
												} //where strlen > 4
											} //type==3
									
									}
							
							
								if ($_POST['transtype_'.$fet_val['idwfprocassetsaccess'].'']=="UPDATE")
									{
									$ttype=$_POST['itemtype_'.$fet_val['idwfprocassetsaccess'].''];
									$itempk=mysql_real_escape_string(trim($_POST['itempk_'.$fet_val['idwfprocassetsaccess'].'']));
									
									//value captured - //this hack for checkbox
									if ((($ttype==3)||($ttype==4)) && (!isset($_POST['item_'.$fet_val['idwfprocassetsaccess'].''])) )
										{
										$fvalue=0;
										} else {
										$fvalue=mysql_real_escape_string(trim($_POST['item_'.$fet_val['idwfprocassetsaccess'].'']));
										}
									
									//only if there are records
									if (
										( ($fvalue > 0) || (strlen($fvalue) > 0) && ($ttype!=4) ) 
										|| 
										( ($ttype==4) && (($fvalue=='') || ($fvalue==0) || ($fvalue!=0)) ) 
										) 
									/*if ( ($fvalue!='') && (strlen($fvalue) > 0) )*/
										{
									//check the form item type first
									if (($ttype==1) ||($ttype==4) || ($ttype==5) || ($ttype==6) || ($ttype==7) || ($ttype==8) || ($ttype==9)  || ($ttype==10) ) //if textbox OR yes/no OR datepicker OR datetimepicker
											{											

											//then process as below
											
												//enter the audit trail only if there is a change
												$sql_auditlog_form="INSERT INTO audit_wfassetsdata (idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice_prev, wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice_prev, value_choice_new, value_path_prev, value_path_new, wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon, modifiedby_new, modifiedon_new) 
												SELECT idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice, wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice, '".$fvalue."', value_path,'', wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon,".$_SESSION['WkAtToMrPa_idacname'].",'".$timenowis."' 
												FROM wfassetsdata
												WHERE idwfassetsdata=".$itempk." AND value_choice!='".$fvalue."' ";
												//echo $sql_auditlog_form."<br>";
												mysql_query($sql_auditlog_form);
												
												$sql_update="UPDATE wfassetsdata SET 
												value_choice='".$fvalue."',
												wftaskstrac_idwftaskstrac='".$_SESSION['wftaskstrac']."',
												tktin_idtktin='".$_SESSION['tktin_idtktin']."',
												modifiedby='".$_SESSION['WkAtToMrPa_idacname']."',
												modifiedon='".$timenowis."'
												WHERE idwfassetsdata=".$itempk." LIMIT 1";
											//	echo "<br><br><br><br><br><br><br><bR><br>".$sql_update;
												mysql_query($sql_update);
												
												
												}
											
									
									if ($ttype==2 )//if menulist
											{
											
											//enter the audit trail only if there is a change
												$sql_auditlog_form="INSERT INTO audit_wfassetsdata (idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice_prev, wfprocassetschoice_idwfprocassetschoice_new, wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice_prev, value_choice_new, value_path_prev, value_path_new, wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon, modifiedby_new, modifiedon_new) 
												SELECT idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice,'".$fvalue."', wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice, '', value_path,'', wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon,".$_SESSION['WkAtToMrPa_idacname'].",'".$timenowis."' 
												FROM wfassetsdata
												WHERE idwfassetsdata=".$itempk." AND wfprocassetschoice_idwfprocassetschoice!='".$fvalue."' ";
												//echo $sql_auditlog_form."<br>";
												mysql_query($sql_auditlog_form);
																					
												$sql_update="UPDATE wfassetsdata SET 
												wfprocassetschoice_idwfprocassetschoice='".$fvalue."',
												wftaskstrac_idwftaskstrac='".$_SESSION['wftaskstrac']."',
												tktin_idtktin='".$_SESSION['tktin_idtktin']."',
												modifiedby='".$_SESSION['WkAtToMrPa_idacname']."',
												modifiedon='".$timenowis."'
												WHERE idwfassetsdata=".$itempk." LIMIT 1";
												//echo "Status Update->--".$sql_update."<br><br>";
												mysql_query($sql_update);
											}
											
									if ( ($ttype==3) && (isset($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])) && (strlen($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])>4) )//if file upload".$_SESSION['tktin_idtktin']."
											{
											$fvalue_upload=basename($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"]);
											$target_dir = "../documents/task_docs/".$today."/";
//											$docname=$_SESSION['tktin_idtktin']."_".basename($_FILES["fileToUpload"]["name"]);
											$docname=$_SESSION['tktin_idtktin']."_".$fvalue_upload;
											//we need to seed the document to make it unique_
											//lets include the ticket_ref number of the task to the name of the file
											$target_file = $target_dir . $docname;
											$uploadOk = 1;
											$file_name_only=$_SESSION['tktin_idtktin']."_".$_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"];
											$file_size_only=$_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["size"];

											////check if there is any document before proceeding
											if (strlen($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])>4)
												{
											
													$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
													
													//validation before uploading											 
													//check if file exists
													if (file_exists($target_file)) 
														{
														$upload_error_1 = "<div class=\"msg_warning_small\">File Missing</div>";
														}
													
													if ($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["size"] > 10485760) 
														{
														$upload_error_2 = "<div class=\"msg_warning_small\">File Max Size Exceeded( 10 MB)</div>";
														}
													
													if	($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
														&& $imageFileType != "gif" && $imageFileType != "doc" && $imageFileType != "docx" 
														&& $imageFileType != "pdf" && $imageFileType != "xls" && $imageFileType != "xlsx" 
														&& $imageFileType != "ppt" && $imageFileType != "pptx" && $imageFileType != "csv"    ) {
															
														$upload_error_3 = "<div class=\"msg_warning_small\">Sorry, file format [".$imageFileType."] not allowed</div>";
														}

													if ( (!isset($upload_error_1)) && (!isset($upload_error_2)) && (!isset($upload_error_3)) )
														{
														//echo "soo farr soo good";
														 if (move_uploaded_file($_FILES['item_'.$fet_val['idwfprocassetsaccess'].'']["tmp_name"], $target_file)) 
															{
															$upload_success=1;
															
															$sql_auditlog_form="INSERT INTO audit_wfassetsdata (idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice_prev, wfprocassetschoice_idwfprocassetschoice_new, wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice_prev, value_choice_new, value_path_prev, value_path_new, wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon, modifiedby_new, modifiedon_new) 
															SELECT idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice,'', wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice, '', value_path,'".$file_name_only."', wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon,".$_SESSION['WkAtToMrPa_idacname'].",'".$timenowis."' 
															FROM wfassetsdata
															WHERE idwfassetsdata=".$itempk." AND value_path!='".$file_name_only."' ";
															//echo $sql_auditlog_form."<br>";
															mysql_query($sql_auditlog_form);
															
															//log the record into the Database
															$sql_update="UPDATE wfassetsdata SET 
															value_path='".$file_name_only."',
															modifiedby='".$_SESSION['WkAtToMrPa_idacname']."',
															modifiedon='".$timenowis."'
															WHERE idwfassetsdata=".$itempk." LIMIT 1";

															mysql_query($sql_update);
															
															//create the audit log
															$sql_audit="INSERT INTO audit_docuploads ( doc_name, doc_ext, doc_size, tktin_idtktin, createdon, createdby, usersess, usrip,wfprocassets_idwfprocassets) 
															VALUES ('".$file_name_only."', '".$imageFileType."', '".$file_size_only."', '".$_SESSION['tktin_idtktin']."', '".$timenowis."', '".$_SESSION['WkAtToMrPa_idacname']."', '".session_id()."', '".$_SERVER['REMOTE_ADDR']."',".$itempk.")";
															mysql_query($sql_audit);
																											
															} else {
																$upload_error_4 = "<div class=\"msg_warning_small\">Sorry, we are unable to upload that file</div>";
															}
														} //no error
													} // if fvalue strlen>4
											
											} //type==3		
										//echo ">>>".$sql_update."<br>";	
										
										} //closed if there data
										
									}
									
								}
								
							} while ($fet_val=mysql_fetch_array($res_val));
							
							} //if record is > 0
						
						} //close form data checker
						
						//Feedback SMS to send customer/sender a message
						if ( (isset($tktsms)) && (strlen($tktsms)>15) && (strlen($tktsenderphone)==12) )
							{
							$sql_smsout="INSERT INTO mdata_out_sms (destnumber,msgtext)  
							VALUES ('".$tktsenderphone."','".$tktno." ".$tktsms."')";

							mysql_query($sql_smsout);
							}
					
						
							//notify if anyone is to be notified
							$sql_notify="SELECT idwfnotification,wfnotification.tktstatus_idtktstatus,usrrole_idusrrole,wftskflow_idwftskflow,notify_system,notify_email,notify_sms,idtktmsgs,tktmsg_sms,tktmsg_email,tktmsg_dashboard FROM wfnotification 
							INNER JOIN tktmsgs ON wfnotification.idwfnotification=tktmsgs.wfnotification_idwfnotification
							WHERE wftskflow_idwftskflow=".$_SESSION['thistskflow']." ORDER BY idwfnotification ASC";
							$res_notify=mysql_query($sql_notify);
							$num_notify=mysql_num_rows($res_notify);
							$fet_notify=mysql_fetch_array($res_notify);
							
							if ($num_notify > 0 ) // if there is a notification setting
								{
								do {			
								//check for each of the settings 
									if ( ($fet_notify['notify_system']==1) && (strlen($fet_notify['tktmsg_dashboard'])>2) ) //system dashboard set on
										{
										$sql_dash="INSERT INTO tktmsglogs_dashboard (tktmsgs_idtktmsgs,msgto_roleid,msgto_subject,msgto_body,createdon,readon)
										VALUES ('".$fet_notify['idtktmsgs']."','".$fet_notify['usrrole_idusrrole']."','Notification - Tkt No : ".$tktno.", ".$fet_ticketcat['tktcategoryname'].",'".$fet_notify['tktmsg_dashboard']." - ".$fet_ticketcat['tktcategoryname']."','".$timenowis."','0000-00-00 00:00:00')";
										mysql_query($sql_dash);									
										}// system dashboard set on
												
										//get this roles email address and phone numbers
										//ensure the account is active as well...
										$sql_rolecontacts="SELECT usremail,usrphone FROM usrac WHERE usrrole_idusrrole=".$fet_notify['usrrole_idusrrole']." AND acstatus=1 LIMIT 1";
										$res_rolecontacts=mysql_query($sql_rolecontacts);
										$fet_rolecontacts=mysql_fetch_array($res_rolecontacts);
										$num_rolecontacts=mysql_num_rows($res_rolecontacts);
										
										if ( ($fet_notify['notify_email']==1) && ($num_rolecontacts>0) && (strlen($fet_rolecontacts['usremail'])>6) && (strlen($fet_notify['tktmsg_email'])>2) )//email set on
											{
											$sql_email="INSERT INTO tktmsgslog_emails(tktmsgs_idtktmsgs,emailto,emailsubject,emailbody,createdon,senton) 
											VALUES ('".$fet_notify['idtktmsgs']."','".$fet_rolecontacts['usremail']."','Notification - Tkt No : ".$tktno.", ".$fet_ticketcat['tktcategoryname']."','".$fet_notify['tktmsg_email']." - Tkt No:".$tktno.", ".$fet_ticketcat['tktcategoryname']."','".$timenowis."','0000-00-00 00:00:00')";
												
											mysql_query($sql_email);
											}
											
											if ( ($fet_notify['notify_sms']==1) && ($num_rolecontacts>0) && (strlen($fet_rolecontacts['usrphone'])==13) )
											{
											$sql_sms="INSERT INTO mdata_out_sms (destnumber,msgtext) 
											VALUES ('".$fet_rolecontacts['usrphone']."',' Auto Notification - Tkt No:".$tktno.", ".$fet_ticketcat['tktcategoryname']." received')";
					
											mysql_query($sql_sms);
											}
												
									} while ($fet_notify=mysql_fetch_array($res_notify));								
										
								} //close - if there is a notification setting
					
					//redirect to the correct page
					//header('pop_viewtaskhistory.php?tkt=<?php echo $fet_task['tktin_idtktin'];
					/////////////////////////////check and insert a new subscriber
					if ($fet_task['usrrole_idusrrole']==2) //if this is the first ticket from the customer, then do this...
						{
						//check if a subscriber with the same credentials matches
						$sql_subis="SELECT idsmssubs FROM ".$_SESSION['WkAtToMrPa_tblsmsbc']." WHERE subnumber='".$tktsenderphone."' AND usrtype=1";
						$res_subis=mysql_query($sql_subis);
						$num_subis=mysql_num_rows($res_subis);
						
						//if not, add the new credentials
						if ($num_subis==0)
							{
							$sql_subnew="INSERT INTO ".$_SESSION['WkAtToMrPa_tblsmsbc']." (wftskid,tktid,subnumber,idloctown,idusrteamzone,usrtype,createdon,createdby)
							VALUES ('".$_SESSION['wtaskid']."','".$fet_task['tktin_idtktin']."','".$fet_confirmloc['idloctowns']."','".$_SESSION['WkAtToMrPa_userteamzoneid']."','1','".$timenowis."','".$_SESSION['WkAtToMrPa_idacname']."')";
							mysql_query($sql_subnew);
							}
						}
						
						if ( (isset($batch_no)) && ($batch_no>0) && ($fet_ticket['wftasks_batch_idwftasks_batch']!=$batch_no) )
										{
									//first, lets check if this ticket already belonged to another batch before removing it
									$res_tktin=mysql_query("SELECT idtktinPK,wftasks_batch_idwftasks_batch,tktcategory_idtktcategory FROM tktin WHERE idtktinPK=".$fet_ticket['idtktinPK']."  ");
									$fet_tktin=mysql_fetch_array($res_tktin);
											
									if ($fet_tktin['wftasks_batch_idwftasks_batch']>0)
										{
										//update the tkt as well
										$sql_batchtkt="UPDATE tktin SET 
										wftasks_batch_idwftasks_batch='0',
										batch_number='0',
										voucher_number='0'
										WHERE idtktinPK=".$fet_ticket['idtktinPK']."";
										
										//update the countbatch
										$sql_updatecount_old="UPDATE wftasks_batch SET countbatch=(countbatch-1) WHERE idwftasks_batch=".$fet_tktin['wftasks_batch_idwftasks_batch']."";
													
										//log audit 1
										$sql_audit1="INSERT INTO audit_wftasks_batch (action, actionby_idusrac, actionby_idusrrole, tktin_affected, batchid_old, batchid_new, result, browser_session, action_time, user_ip, user_ip_proxy) 
										VALUES ( 'MOVE', '".$_SESSION['WkAtToMrPa_idacname']."', '".$_SESSION['WkAtToMrPa_iduserrole']."', '".$fet_ticket['idtktinPK']."', '".$fet_tktin['wftasks_batch_idwftasks_batch']."', '".$batch_no."', 'OK', '".session_id()."', '".$timenowis."', '".$_SERVER['REMOTE_ADDR']."', '".$realip."')";
													
										} else {
										
										$sql_batchtkt="SELECT idtktinPK from tktin LIMIT 1";
										$sql_updatecount_old="SELECT idtktinPK from tktin LIMIT 1";
										
										//log audit 1
										$sql_audit1="INSERT INTO audit_wftasks_batch (action, actionby_idusrac, actionby_idusrrole, tktin_affected, batchid_old, batchid_new, result, browser_session, action_time, user_ip, user_ip_proxy) 
										VALUES ( 'NEW', '".$_SESSION['WkAtToMrPa_idacname']."', '".$_SESSION['WkAtToMrPa_iduserrole']."', '".$fet_ticket['idtktinPK']."', '0', '".$batch_no."', 'OK', '".session_id()."', '".$timenowis."', '".$_SERVER['REMOTE_ADDR']."', '".$realip."')";
										
										}
											
									$res_batchtkt=mysql_query($sql_batchtkt);
									$res_updatecount_old=mysql_query($sql_updatecount_old);
									$res_audit1=mysql_query($sql_audit1);
							
									//check the last batch_no
									$res_batchmeta=mysql_query("SELECT usrteamzone_idusrteamzone,wftasks_batchtype_idwftasks_batchtype FROM wftasks_batch WHERE idwftasks_batch=".$batch_no."");
									$fet_batchmeta=mysql_fetch_array($res_batchmeta);
									//changed to get the last max id given for this batch
						//			$sql_lastbatchno="SELECT max(voucher_number) as countbatch FROM tktin WHERE wftasks_batch_idwftasks_batch=".$batch_no."";
									$sql_lastbatchno="SELECT max(tktin.voucher_number) as countbatch,wftasks_batch.wftasks_batchtype_idwftasks_batchtype,wftasks_batch.batch_year FROM tktin
									INNER JOIN wftasks_batch ON tktin.wftasks_batch_idwftasks_batch=wftasks_batch.idwftasks_batch
									WHERE wftasks_batch.wftasks_batchtype_idwftasks_batchtype=".$fet_batchmeta['wftasks_batchtype_idwftasks_batchtype']."
									AND wftasks_batch.usrteamzone_idusrteamzone=".$fet_batchmeta['usrteamzone_idusrteamzone']."
									AND wftasks_batch.usrteamzone_idusrteamzone=".$_SESSION['WkAtToMrPa_userteamzoneid']."  ";//AND YEAR(createdon)='".$this_year."'
									$res_lastbatchno=mysql_query($sql_lastbatchno);
									$fet_lastbatchno=mysql_fetch_array($res_lastbatchno);
									
									//quick validation to avoid crossing over to another year in an older batch
									if ( ($fet_lastbatchno['countbatch']!='') && ($fet_lastbatchno['batch_year']!=$this_year) )
										{
										$error_batchoutdated="<div style=\"color:#ff0000\">You can't assign ".$fet_lastbatchno['batch_year']." in ".$this_year."  </div>";
										//exit;
										}
									
									//create the new batch_no
									$new_batchno=($fet_lastbatchno['countbatch']+1);							
									
									//new update the batch_no meta table
									$sql_updatecount="UPDATE wftasks_batch SET countbatch=(countbatch+1) WHERE idwftasks_batch=".$batch_no."";
									$res_updatecount=mysql_query($sql_updatecount);
									
									//get the tktid to update the tktin as well
									$sql_tktin=mysql_query("SELECT tktin_idtktin FROM wftasks WHERE idwftasks=".$_SESSION['wtaskid']." ");
									$fet_tktin=mysql_fetch_array($sql_tktin);

									
									//update the tkt as well
									$sql_batchtktnew="UPDATE tktin SET 
									wftasks_batch_idwftasks_batch='".$batch_no."',
									batch_number='".$new_batchno."',
									voucher_number='".$new_batchno."'
									WHERE idtktinPK=".$fet_ticket['idtktinPK']."";
									$res_batchtktnew=mysql_query($sql_batchtktnew);
									
									} else { //else if no batch now, then create some dummy queries to run the transction commit succssfully
									////////
									$res_tktin=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_batchtkt=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_lastbatchno=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_updatecount=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$sql_tktin=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_batchtktnew=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_batchtkt=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_updatecount_old=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_audit1=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_batchmeta=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									/////
									}//batch now

					///////////////////////////// close check and insert new subscriber ////////////////////////////
							if ( ($query_1)  && (!isset($error_formdata)) && ($query_2) && (!isset($upload_error_1)) && (!isset($upload_error_2)) && (!isset($upload_error_3)) && (!isset($upload_error_4)) && ($res_tktin) && ($res_batchtkt) && ($res_lastbatchno) && ($res_updatecount) && ($sql_tktin) && ($res_batchtktnew) && ($res_batchtkt) && ($res_updatecount_old) && ($res_audit1) && ($res_batchmeta) && (!isset($error_batchoutdated)))
									{
									mysql_query("COMMIT");	
									///////////////////////////// close check and insert new subscriber ////////////////////////////
									//redirect to the correct page
									?>
									<script language="javascript">
									window.location='mytasks_history.php?tkt=<?php echo $fet_task['tktin_idtktin'];?>&task=<?php echo $fet_task['idwftasks'];?>&tab=2';
									</script>
									<?php
									exit;
									} else {
									mysql_query("ROLLBACK");
									?>
                                    <script language="javascript">
									 alert ('Sorry! Please Try Again!');
									</script>
                                    <?php
									if ($query_1) { mysql_free_result($query_1);  }
									if ($query_2) { mysql_free_result($query_2);  }
									} //if the no error 1_1
						
						
						} //close no error on action 6
				
				} //close action 6
					
					
///////////////  ACTION 9  ///////////////////////////////////////////////////////////////////////////////////////////////////
				
				if ($tktaction==9) { //Select Task Action 9 ie: Return to Sender
				
					//validate
					if (strlen($tkttskmsg9) < 1)
						{
						$error_9_1="<div class=\"msg_warning_small\">".$msg_warn_msgmis."</div>";
						}
					if ($tktasito9<1)
						{
						$error_9_2="<div class=\"msg_warning_small\">".$msg_warn_assign."</div>";
						}
						
					
					if ( (!isset($error_9_1)) && (!isset($error_9_2)) )//if the no error 
						{
						
						mysql_query("BEGIN");
						
						//get the value of the person who originally sent the task
						$task_sent_from=mysql_real_escape_string(trim($_POST['task_sent_from']));	
						
						//update this task 
						$sql_update_task="UPDATE wftasks SET wftskstatustypes_idwftskstatustypes='2',wftskstatusglobal_idwftskstatusglobal='2',timeactiontaken='".$timenowis."'  WHERE idwftasks=".$_SESSION['wtaskid']." LIMIT 1";
						$query_1=mysql_query($sql_update_task);
//echo $sql_update_task;
						
						//create an update message on the record
						$sql_update_msg="INSERT INTO wftskupdates (wftaskstrac_idwftaskstrac,usrrole_idusrrole,usrac_idusrac,wftskstatusglobal_idwftskstatusglobal,wftskstatustypes_idwftskstatustypes,wftasks_idwftasks,wftskupdate,createdby,createdon) 
						VALUES ('".$fet_idtask['wftaskstrac_idwftaskstrac']."','".$_SESSION['WkAtToMrPa_iduserrole']."','".$_SESSION['WkAtToMrPa_idacname']."','2','9','".$_SESSION['wtaskid']."','".$tkttskmsg9.$tktsms_record."','".$_SESSION['WkAtToMrPa_idacname']."','".$timenowis."')";
						$query_2=mysql_query($sql_update_msg);

						//get task details
						$sql_task_details = "SELECT wftasks.wftaskstrac_idwftaskstrac,wftasks.idwftasks,wftasks.usrrole_idusrrole,wftasks.wftasks_idwftasks,wftasks.wftskflow_idwftskflow,wftskflow.wfproc_idwfproc,wftasks.tktin_idtktin,wftasks.usrac_idusrac,wftasks.wftskstatustypes_idwftskstatustypes,wftasks.wftskstatusglobal_idwftskstatusglobal,wftasks.tasksubject,wftasks.taskdesc,wftasks.timeinactual,wftasks.timeoveralldeadline,wftasks.timetatstart,wftasks.timedeadline,wftasks.timeactiontaken,wftasks.sender_idusrrole,wftasks.sender_idusrac,wftskflow.listorder,wftskflow.idwftskflow,wftskflow.wftsktat,wfproc.wfproctat FROM wftasks 
						INNER JOIN wftskflow ON wftasks.wftskflow_idwftskflow=wftskflow.idwftskflow 
						INNER JOIN wfproc ON wftskflow.wfproc_idwfproc=wfproc.idwfproc
						WHERE idwftasks=".$_SESSION['wtaskid']." LIMIT 1";
						$res_task_details = mysql_query($sql_task_details);
						$fet_task_details = mysql_fetch_array($res_task_details);
					
						////////////// START CALCULATION OF TIME /////////
						//find previous task step in this case should be back to the previous step
						$sql_nextwf="SELECT idwftskflow,wftskflow.wfsymbol_idwfsymbol as wfsymbol,wfactors.usrrole_idusrrole as usrrole,wfactors.usrgroup_idusrgroup as usrgroup,wftsktat,expubholidays,sender_idusrrole 
						FROM wftskflow 
						INNER JOIN wfactors ON wftskflow.idwftskflow=wfactors.wftskflow_idwftskflow 
						INNER JOIN wftasks ON wftskflow.idwftskflow=wftasks.wftskflow_idwftskflow
						WHERE 
						wftasks.sender_idusrrole=".$task_sent_from." 
						AND wftasks.usrrole_idusrrole=".$_SESSION['WkAtToMrPa_iduserrole']."
						AND wfproc_idwfproc=".$fet_task_details['wfproc_idwfproc']." 
						AND wftskflow.wfsymbol_idwfsymbol=2 
						AND listorder<'".$fet_task_details['listorder']."'
						GROUP BY idwftskflow ORDER BY listorder DESC LIMIT 1";
						$res_nextwf=mysql_query($sql_nextwf);
						$num_nextwf=mysql_num_rows($res_nextwf);
						$fet_nextwf=mysql_fetch_array($res_nextwf);
						
						
						//get user account id
						$sql_userid="SELECT idusrac FROM usrac WHERE usrrole_idusrrole=".$tktasito9." LIMIT 1";
						$res_userid=mysql_query($sql_userid);
						$fet_userid=mysql_fetch_array($res_userid);
						
						//insert new task for the recepeint
						$sql_new_task="INSERT INTO wftasks (wftaskstrac_idwftaskstrac,usrrole_idusrrole,wftasks_idwftasks,wftskflow_idwftskflow,tktin_idtktin,usrac_idusrac,wftskstatustypes_idwftskstatustypes,wftskstatusglobal_idwftskstatusglobal,tasksubject,taskdesc,timeinactual,timeoveralldeadline,timetatstart,timedeadline,timeactiontaken,sender_idusrrole,sender_idusrac,createdon) 
						VALUES ('".$fet_task_details['wftaskstrac_idwftaskstrac']."','".$tktasito9."','".$fet_task_details['idwftasks']."','".$fet_nextwf['idwftskflow']."','".$fet_task_details['tktin_idtktin']."','".$fet_userid['idusrac']."','0','1','".$fet_task_details['tasksubject']."','".$tkttskmsg9."','".$timenowis."','".$fet_task_details['timeoveralldeadline']."','".$fet_task_details['timetatstart']."','".$fet_task_details['timedeadline']."','0000-00-00 00:00:00','".$_SESSION['WkAtToMrPa_iduserrole']."','".$_SESSION['WkAtToMrPa_idacname']."','".$timenowis."')";
						
						if ($fet_task_details['wftaskstrac_idwftaskstrac'] > 0)
							{
							$query_3=mysql_query($sql_new_task);
							}
					
						//check if there is a form data and if so, go ahead and process this transaction with inserts or updates
					if ( (isset($_POST['formdata_available'])) && ($_POST['formdata_available']==1) )
						{
						//echo "processed <br>";
						//check the db for this field by reusing the sql statement above
						/*
						$sql_val="SELECT idwfprocassetsaccess,assetname,perm_read,perm_write,perm_required,wfprocassets.wfprocdtype_idwfprocdtype,idwfprocassets FROM wfprocassetsaccess 
						INNER JOIN wfprocassets ON wfprocassetsaccess.wfprocassets_idwfprocassets=wfprocassets.idwfprocassets
						WHERE wftskflow_idwftskflow=".$fet_task['wftskflow_idwftskflow']." AND wfprocassetsaccess.perm_read=1 ORDER BY wfprocassets.ordering ASC";
						*/
						$sql_val="SELECT idwfprocassetsaccess,assetname,perm_read,perm_write,perm_required,wfprocassets.wfprocdtype_idwfprocdtype,idwfprocassets,wfprocassetsgrouplbl,sysprofiles_idsysprofiles,wfprocassetsaccess.wfprocforms_idwfprocforms
						FROM wfprocassetsaccess
						INNER JOIN wfprocassets ON wfprocassetsaccess.wfprocassets_idwfprocassets = wfprocassets.idwfprocassets
						INNER JOIN wfprocassetsgroup ON wfprocassets.wfprocassetsgroup_idwfprocassetsgroup = wfprocassetsgroup.idwfprocassetsgroup
						INNER JOIN wfprocforms_cats ON wfprocassetsaccess.wfprocforms_idwfprocforms = wfprocforms_cats.wfprocforms_idwfprocforms 
						WHERE sysprofiles_idsysprofiles=".$_SESSION['WkAtToMrPa_iduserprofile']." AND wfprocforms_cats.tktcategory_idtktcategory=".$fet_ticket['tktcategory_idtktcategory']." AND wfprocassetsaccess.perm_read=1 ORDER BY wfprocassetsgrouplbl ASC,ordering ASC";

						$res_val=mysql_query($sql_val);
						$num_val=mysql_num_rows($res_val);
						$fet_val=mysql_fetch_array($res_val);
//	echo $sql_val;
						if ($num_val > 0) //if there are some values, then
							{
							do {
							//master-checklist if  | it is required | there is a value | the data type to determine the field |  whether an update or insert
							
							//validate required
						//	echo "validation ";
						//	echo $_POST['required_'.$fet_val['idwfprocassetsaccess'].'']."<br>";
						//	echo $_POST['item_'.$fet_val['idwfprocassetsaccess'].''];
								if (
								(isset($_POST['required_'.$fet_val['idwfprocassetsaccess'].''])) 
								&& ($_POST['required_'.$fet_val['idwfprocassetsaccess'].'']==1) 
								&&  ($_POST['item_'.$fet_val['idwfprocassetsaccess'].'']=="")   
								)
									{
									//echo $_POST['item_'.$fet_val['idwfprocassetsaccess'].'']."<br>";
									$error_formdata=1;
									echo "<div class=\"msg_warning_small\"> Form : ".$fet_val['assetname']." is required | <a href=\"mytasks_history.php\">Back to Task View</a></div>";
									
									}
								
							//if no error on the dataform, then process
							if (!isset($error_formdata))
								{	
								if ($_POST['transtype_'.$fet_val['idwfprocassetsaccess'].'']=="INSERT")
									{
									//check the form item type first
									$ttype=$_POST['itemtype_'.$fet_val['idwfprocassetsaccess'].''];
										
										if (($ttype==1) || ($ttype==4) || ($ttype==5) || ($ttype==6) || ($ttype==7) || ($ttype==8) || ($ttype==9) || ($ttype==10)   ) //if textbox OR yes/no OR datepicker OR datetimepicker
											{
											$fvalue=mysql_real_escape_string(trim($_POST['item_'.$fet_val['idwfprocassetsaccess'].'']));
											
											//then process as below
											$sql_insert="INSERT INTO wfassetsdata (wfprocassetsaccess_idwfprocassetsaccess,
											wfprocassetschoice_idwfprocassetschoice,
											wfprocassets_idwfprocassets,
											wftasks_idwftasks,
											value_choice,
											value_path,
											wftaskstrac_idwftaskstrac,
											tktin_idtktin,
											createdby,
											createdon)
											VALUES ('".$fet_val['idwfprocassetsaccess']."',
											'0',

											'".$fet_val['idwfprocassets']."',
											'".$_SESSION['wtaskid']."',
											'".$fvalue."',
											'',
											'".$_SESSION['wftaskstrac']."',
											'".$_SESSION['tktin_idtktin']."',
											'".$_SESSION['WkAtToMrPa_idacname']."',
											'".$timenowis."'
											)";
											
											mysql_query($sql_insert);
											//echo $sql_insert;
											//exit;
											}
											
										if ($ttype==2)//if menulist
											{
											
											$fvalue=mysql_real_escape_string(trim($_POST['item_'.$fet_val['idwfprocassetsaccess'].'']));
											
											$sql_insert="INSERT INTO wfassetsdata (wfprocassetsaccess_idwfprocassetsaccess,
											wfprocassetschoice_idwfprocassetschoice,
											wfprocassets_idwfprocassets,
											wftasks_idwftasks,
											value_choice,
											value_path,
											wftaskstrac_idwftaskstrac,
											tktin_idtktin,
											createdby,
											createdon)
											VALUES ('".$fet_val['idwfprocassetsaccess']."',
											'".$fvalue."',
											'".$fet_val['idwfprocassets']."',
											'".$_SESSION['wtaskid']."',
											'',
											'',
											'".$_SESSION['wftaskstrac']."',
											'".$_SESSION['tktin_idtktin']."',
											'".$_SESSION['WkAtToMrPa_idacname']."',
											'".$timenowis."'
											)";
											
											mysql_query($sql_insert);
											
											}
										
										if ( ($ttype==3) && (isset($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])) && (strlen($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])>4) )//if file upload".$_SESSION['tktin_idtktin']."
											{
											$fvalue_upload=basename($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"]);
											$target_dir = "../documents/task_docs/".$today."/";
//											$docname=$_SESSION['tktin_idtktin']."_".basename($_FILES["fileToUpload"]["name"]);
											$docname=$_SESSION['tktin_idtktin']."_".$fvalue_upload;
											//we need to seed the document to make it unique_
											//lets include the ticket_ref number of the task to the name of the file
											$target_file = $target_dir . $docname;
											$uploadOk = 1;
											//just keep the file name only
											$file_name_only=$_SESSION['tktin_idtktin']."_".$_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"];
											$file_size_only=$_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["size"];
											//check if there is any document before proceeding
											if (strlen($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])>4)
												{
												
												$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
												
												//validation before uploading										 
												//check if file exists
												if (file_exists($target_file)) 
													{
													$upload_error_1 = "<div class=\"msg_warning_small\">File Missing</div>";
													}
												
												if ($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["size"] > 10485760) 
													{
													$upload_error_2 = "<div class=\"msg_warning_small\">File Max Size Exceeded( 10 MB)</div>";
													}
												
												if	($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
													&& $imageFileType != "gif" && $imageFileType != "doc" && $imageFileType != "docx" 
													&& $imageFileType != "pdf" && $imageFileType != "xls" && $imageFileType != "xlsx" 
													&& $imageFileType != "ppt" && $imageFileType != "pptx"  && $imageFileType != "csv"    ) {
														
													$upload_error_3 = "<div class=\"msg_warning_small\">Sorry, file format [".$imageFileType."] not allowed</div>";
													}
												//echo $upload_error_1.$upload_error_2.$upload_error_3;	
												//echo "Size -->".$_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["size"];
												if ( (!isset($upload_error_1)) && (!isset($upload_error_2)) && (!isset($upload_error_3)) )
													{
													 if (move_uploaded_file($_FILES['item_'.$fet_val['idwfprocassetsaccess'].'']["tmp_name"], $target_file)) 
														{
														$upload_success=1;
														//log the record into the Database
														$sql_insert="INSERT INTO wfassetsdata (wfprocassetsaccess_idwfprocassetsaccess,
														wfprocassetschoice_idwfprocassetschoice,
														wfprocassets_idwfprocassets,
														wftasks_idwftasks,
														value_choice,
														value_path,
														wftaskstrac_idwftaskstrac,
														tktin_idtktin,
														createdby,
														createdon)
														VALUES ('".$fet_val['idwfprocassetsaccess']."',
														'0',
														'".$fet_val['idwfprocassets']."',
														'".$_SESSION['wtaskid']."',
														'',
														'".$file_name_only."',
														'".$_SESSION['wftaskstrac']."',
														'".$_SESSION['tktin_idtktin']."',
														'".$_SESSION['WkAtToMrPa_idacname']."',
														'".$timenowis."'
														)";
														
														mysql_query($sql_insert);
														
														//create the audit log
														$sql_audit="INSERT INTO audit_docuploads ( doc_name, doc_ext, doc_size, tktin_idtktin, createdon, createdby, usersess, usrip, wfprocassets_idwfprocassets) 
														VALUES ('".$file_name_only."', '".$imageFileType."', '".$file_size_only."', '".$_SESSION['tktin_idtktin']."', '".$timenowis."', '".$_SESSION['WkAtToMrPa_idacname']."', '".session_id()."', '".$_SERVER['REMOTE_ADDR']."','".$fet_val['idwfprocassets']."')";
														mysql_query($sql_audit);
														
														} else {
															$upload_error_4 = "<div class=\"msg_warning_small\">Sorry, we are unable to upload that file</div>";
														}
													} //no error
												} //where strlen > 4
											} //type==3
									
									} //insert ends here
									
								if ($_POST['transtype_'.$fet_val['idwfprocassetsaccess'].'']=="UPDATE")
									{
									$ttype=$_POST['itemtype_'.$fet_val['idwfprocassetsaccess'].''];
									$itempk=mysql_real_escape_string(trim($_POST['itempk_'.$fet_val['idwfprocassetsaccess'].'']));
									
									//value captured - //this hack for checkbox

									if ((($ttype==3)||($ttype==4)) && (!isset($_POST['item_'.$fet_val['idwfprocassetsaccess'].''])) )
										{
										$fvalue=0;
										} else {
										$fvalue=mysql_real_escape_string(trim($_POST['item_'.$fet_val['idwfprocassetsaccess'].'']));
										}
									
									//only if there are records
									if (
										( ($fvalue > 0) || (strlen($fvalue) > 0) && ($ttype!=4) ) 
										|| 
										( ($ttype==4) && (($fvalue=='') || ($fvalue==0) || ($fvalue!=0)) ) 
										) 
									/*if ( ($fvalue!='') && (strlen($fvalue) > 0) )*/
										{
									//check the form item type first
									if (($ttype==1) || ($ttype==4) || ($ttype==5) || ($ttype==6) || ($ttype==7) || ($ttype==8) || ($ttype==9) || ($ttype==10)   ) //if textbox OR yes/no OR datepicker OR datetimepicker
											{
											
											///audit log
											$sql_auditlog_form="INSERT INTO audit_wfassetsdata (idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice_prev, wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice_prev, value_choice_new, value_path_prev, value_path_new, wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon, modifiedby_new, modifiedon_new) 
											SELECT idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice, wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice, '".$fvalue."', value_path,'', wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon,".$_SESSION['WkAtToMrPa_idacname'].",'".$timenowis."' 
											FROM wfassetsdata
											WHERE idwfassetsdata=".$itempk." AND value_choice!='".$fvalue."' ";
											//echo $sql_auditlog_form."<br>";
											mysql_query($sql_auditlog_form);
											
											//then process as below
											$sql_update="UPDATE wfassetsdata SET 
											value_choice='".$fvalue."',
											wftaskstrac_idwftaskstrac='".$_SESSION['wftaskstrac']."',
											tktin_idtktin='".$_SESSION['tktin_idtktin']."',
											modifiedby='".$_SESSION['WkAtToMrPa_idacname']."',
											modifiedon='".$timenowis."'
											WHERE idwfassetsdata=".$itempk." LIMIT 1";
											
											mysql_query($sql_update);
											//echo $sql_update;
											}
									
									if ($ttype==2 )//if menulist
											{
											//enter the audit trail only if there is a change
											$sql_auditlog_form="INSERT INTO audit_wfassetsdata (idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice_prev, wfprocassetschoice_idwfprocassetschoice_new, wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice_prev, value_choice_new, value_path_prev, value_path_new, wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon, modifiedby_new, modifiedon_new) 
											SELECT idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice,'".$fvalue."', wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice, '', value_path,'', wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon,".$_SESSION['WkAtToMrPa_idacname'].",'".$timenowis."' 
											FROM wfassetsdata
											WHERE idwfassetsdata=".$itempk." AND wfprocassetschoice_idwfprocassetschoice!='".$fvalue."' ";
											//echo $sql_auditlog_form."<br>";
											mysql_query($sql_auditlog_form);
											
											$sql_update="UPDATE wfassetsdata SET 
											wfprocassetschoice_idwfprocassetschoice='".$fvalue."',
											wftaskstrac_idwftaskstrac='".$_SESSION['wftaskstrac']."',
											tktin_idtktin='".$_SESSION['tktin_idtktin']."',
											modifiedby='".$_SESSION['WkAtToMrPa_idacname']."',
											modifiedon='".$timenowis."'
											WHERE idwfassetsdata=".$itempk." LIMIT 1";
											
											mysql_query($sql_update);
											}
									
									if ( ($ttype==3) && (isset($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])) && (strlen($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])>4) )//if file upload".$_SESSION['tktin_idtktin']."
											{
											$fvalue_upload=basename($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"]);
											$target_dir = "../documents/task_docs/".$today."/";
//											$docname=$_SESSION['tktin_idtktin']."_".basename($_FILES["fileToUpload"]["name"]);
											$docname=$_SESSION['tktin_idtktin']."_".$fvalue_upload;
											//we need to seed the document to make it unique_
											//lets include the ticket_ref number of the task to the name of the file
											$target_file = $target_dir . $docname;
											$uploadOk = 1;
											$file_name_only=$_SESSION['tktin_idtktin']."_".$_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"];
											$file_size_only=$_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["size"];

											////check if there is any document before proceeding
											if (strlen($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["name"])>4)
												{
											
													$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
													
													//validation before uploading											 
													//check if file exists
													if (file_exists($target_file)) 
														{
														$upload_error_1 = "<div class=\"msg_warning_small\">File Missing</div>";
														}
													
													if ($_FILES["item_".$fet_val['idwfprocassetsaccess'].""]["size"] > 10485760) 
														{
														$upload_error_2 = "<div class=\"msg_warning_small\">File Max Size Exceeded( 10 MB)</div>";
														}
													
													if	($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
														&& $imageFileType != "gif" && $imageFileType != "doc" && $imageFileType != "docx" 
														&& $imageFileType != "pdf" && $imageFileType != "xls" && $imageFileType != "xlsx" 
														&& $imageFileType != "ppt" && $imageFileType != "pptx" && $imageFileType != "csv"    ) {
															
														$upload_error_3 = "<div class=\"msg_warning_small\">Sorry, file format [".$imageFileType."] not allowed</div>";
														}

													if ( (!isset($upload_error_1)) && (!isset($upload_error_2)) && (!isset($upload_error_3)) )
														{
														//echo "soo farr soo good";
														 if (move_uploaded_file($_FILES['item_'.$fet_val['idwfprocassetsaccess'].'']["tmp_name"], $target_file)) 
															{
															$upload_success=1;
															
															$sql_auditlog_form="INSERT INTO audit_wfassetsdata (idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice_prev, wfprocassetschoice_idwfprocassetschoice_new, wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice_prev, value_choice_new, value_path_prev, value_path_new, wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon, modifiedby_new, modifiedon_new) 
															SELECT idwfassetsdata, wfprocassetsaccess_idwfprocassetsaccess, wfprocassetschoice_idwfprocassetschoice,'', wfprocassets_idwfprocassets, wftasks_idwftasks, wftskupdates_idwftskupdates, value_choice, '', value_path,'".$file_name_only."', wftaskstrac_idwftaskstrac, tktin_idtktin, createdby, createdon,".$_SESSION['WkAtToMrPa_idacname'].",'".$timenowis."' 
															FROM wfassetsdata
															WHERE idwfassetsdata=".$itempk." AND value_path!='".$file_name_only."' ";
															//echo $sql_auditlog_form."<br>";
															mysql_query($sql_auditlog_form);
															
															//log the record into the Database
															$sql_update="UPDATE wfassetsdata SET 
															value_path='".$file_name_only."',
															modifiedby='".$_SESSION['WkAtToMrPa_idacname']."',
															modifiedon='".$timenowis."'
															WHERE idwfassetsdata=".$itempk." LIMIT 1";

															mysql_query($sql_update);
															
															//create the audit log
															$sql_audit="INSERT INTO audit_docuploads ( doc_name, doc_ext, doc_size, tktin_idtktin, createdon, createdby, usersess, usrip,wfprocassets_idwfprocassets) 
															VALUES ('".$file_name_only."', '".$imageFileType."', '".$file_size_only."', '".$_SESSION['tktin_idtktin']."', '".$timenowis."', '".$_SESSION['WkAtToMrPa_idacname']."', '".session_id()."', '".$_SERVER['REMOTE_ADDR']."',".$itempk.")";
															mysql_query($sql_audit);
																											
															} else {
																$upload_error_4 = "<div class=\"msg_warning_small\">Sorry, we are unable to upload that file</div>";
															}
														} //no error
													} // if fvalue strlen>4
											
											} //type==3			
										
									} //end update
									
									}
								}
								
							} while ($fet_val=mysql_fetch_array($res_val));
							
							} //if record is > 0
						
						} //close form data checker
												
						//Feedback SMS to send customer/sender a message
						if ( (isset($tktsms)) && (strlen($tktsms)>15) && (strlen($tktsenderphone)==12) )
							{
							$sql_smsout="INSERT INTO mdata_out_sms (destnumber,msgtext)  
							VALUES ('".$tktsenderphone."','".$tktno." ".$tktsms."')";
							mysql_query($sql_smsout);
							}
					
							
							//notify if anyone is to be notified
							$sql_notify="SELECT idwfnotification,wfnotification.tktstatus_idtktstatus,usrrole_idusrrole,wftskflow_idwftskflow,notify_system,notify_email,notify_sms,idtktmsgs,tktmsg_sms,tktmsg_email,tktmsg_dashboard FROM wfnotification 
							INNER JOIN tktmsgs ON wfnotification.idwfnotification=tktmsgs.wfnotification_idwfnotification
							WHERE wftskflow_idwftskflow=".$_SESSION['thistskflow']." ORDER BY idwfnotification ASC";
							$res_notify=mysql_query($sql_notify);
							$num_notify=mysql_num_rows($res_notify);
							$fet_notify=mysql_fetch_array($res_notify);
							
							if ($num_notify > 0 ) // if there is a notification setting
								{
								do {			
								//check for each of the settings 
									if ( ($fet_notify['notify_system']==1) && (strlen($fet_notify['tktmsg_dashboard'])>2) ) //system dashboard set on
										{
										$sql_dash="INSERT INTO tktmsglogs_dashboard (tktmsgs_idtktmsgs,msgto_roleid,msgto_subject,msgto_body,createdon,readon)
										VALUES ('".$fet_notify['idtktmsgs']."','".$fet_notify['usrrole_idusrrole']."','Notification - Tkt No : ".$tktno.", ".$fet_ticketcat['tktcategoryname'].",'".$fet_notify['tktmsg_dashboard']." - ".$fet_ticketcat['tktcategoryname']."','".$timenowis."','0000-00-00 00:00:00')";
										mysql_query($sql_dash);									
										}// system dashboard set on
												
										//get this roles email address and phone numbers
										//ensure the account is active as well...
										$sql_rolecontacts="SELECT usremail,usrphone FROM usrac WHERE usrrole_idusrrole=".$fet_notify['usrrole_idusrrole']." AND acstatus=1 LIMIT 1";
										$res_rolecontacts=mysql_query($sql_rolecontacts);
										$fet_rolecontacts=mysql_fetch_array($res_rolecontacts);

										$num_rolecontacts=mysql_num_rows($res_rolecontacts);
										
										if ( ($fet_notify['notify_email']==1) && ($num_rolecontacts>0) && (strlen($fet_rolecontacts['usremail'])>6) && (strlen($fet_notify['tktmsg_email'])>2) )//email set on
											{
											$sql_email="INSERT INTO tktmsgslog_emails(tktmsgs_idtktmsgs,emailto,emailsubject,emailbody,createdon,senton) 
											VALUES ('".$fet_notify['idtktmsgs']."','".$fet_rolecontacts['usremail']."','Notification - Tkt No : ".$tktno.", ".$fet_ticketcat['tktcategoryname']."','".$fet_notify['tktmsg_email']." - Tkt No:".$tktno.", ".$fet_ticketcat['tktcategoryname']."','".$timenowis."','0000-00-00 00:00:00')";
												
											mysql_query($sql_email);
											}
											
											if ( ($fet_notify['notify_sms']==1) && ($num_rolecontacts>0) && (strlen($fet_rolecontacts['usrphone'])==13) )
											{
											$sql_sms="INSERT INTO mdata_out_sms (destnumber,msgtext) 
											VALUES ('".$fet_rolecontacts['usrphone']."',' Auto Notification - Tkt No:".$tktno.", ".$fet_ticketcat['tktcategoryname']." received')";
					
											mysql_query($sql_sms);
											}
												
									} while ($fet_notify=mysql_fetch_array($res_notify));								
										
								} //close - if there is a notification setting
					
					/////////////////////////////check and insert a new subscriber
					if ($fet_task['usrrole_idusrrole']==2) //if this is the first ticket from the customer, then do this...
						{
						//check if a subscriber with the same credentials matches
						$sql_subis="SELECT idsmssubs FROM ".$_SESSION['WkAtToMrPa_tblsmsbc']." WHERE subnumber='".$tktsenderphone."' AND usrtype=1";
						$res_subis=mysql_query($sql_subis);
						$num_subis=mysql_num_rows($res_subis);
						
						//if not, add the new credentials
						if ($num_subis==0)
							{
							$sql_subnew="INSERT INTO ".$_SESSION['WkAtToMrPa_tblsmsbc']." (wftskid,tktid,subnumber,idloctown,idusrteamzone,usrtype,createdon,createdby)

							VALUES ('".$_SESSION['wtaskid']."','".$fet_task['tktin_idtktin']."','".$fet_confirmloc['idloctowns']."','".$_SESSION['WkAtToMrPa_userteamzoneid']."','1','".$timenowis."','".$_SESSION['WkAtToMrPa_idacname']."')";
							mysql_query($sql_subnew);
							}
						}
					///////////////////////////// close check and insert new subscriber ////////////////////////////
					if ( (isset($batch_no)) && ($batch_no>0) && ($fet_ticket['wftasks_batch_idwftasks_batch']!=$batch_no) )
										{
									//first, lets check if this ticket already belonged to another batch before removing it
									$res_tktin=mysql_query("SELECT idtktinPK,wftasks_batch_idwftasks_batch,tktcategory_idtktcategory FROM tktin WHERE idtktinPK=".$fet_ticket['idtktinPK']."  ");
									$fet_tktin=mysql_fetch_array($res_tktin);
											
									if ($fet_tktin['wftasks_batch_idwftasks_batch']>0)
										{
										//update the tkt as well
										$sql_batchtkt="UPDATE tktin SET 
										wftasks_batch_idwftasks_batch='0',
										batch_number='0',
										voucher_number='0'
										WHERE idtktinPK=".$fet_ticket['idtktinPK']."";
										
										//update the countbatch
										$sql_updatecount_old="UPDATE wftasks_batch SET countbatch=(countbatch-1) WHERE idwftasks_batch=".$fet_tktin['wftasks_batch_idwftasks_batch']."";
													
										//log audit 1
										$sql_audit1="INSERT INTO audit_wftasks_batch (action, actionby_idusrac, actionby_idusrrole, tktin_affected, batchid_old, batchid_new, result, browser_session, action_time, user_ip, user_ip_proxy) 
										VALUES ( 'MOVE', '".$_SESSION['WkAtToMrPa_idacname']."', '".$_SESSION['WkAtToMrPa_iduserrole']."', '".$fet_ticket['idtktinPK']."', '".$fet_tktin['wftasks_batch_idwftasks_batch']."', '".$batch_no."', 'OK', '".session_id()."', '".$timenowis."', '".$_SERVER['REMOTE_ADDR']."', '".$realip."')";
													
										} else {
										
										$sql_batchtkt="SELECT idtktinPK from tktin LIMIT 1";
										$sql_updatecount_old="SELECT idtktinPK from tktin LIMIT 1";
										
										//log audit 1
										$sql_audit1="INSERT INTO audit_wftasks_batch (action, actionby_idusrac, actionby_idusrrole, tktin_affected, batchid_old, batchid_new, result, browser_session, action_time, user_ip, user_ip_proxy) 
										VALUES ( 'NEW', '".$_SESSION['WkAtToMrPa_idacname']."', '".$_SESSION['WkAtToMrPa_iduserrole']."', '".$fet_ticket['idtktinPK']."', '0', '".$batch_no."', 'OK', '".session_id()."', '".$timenowis."', '".$_SERVER['REMOTE_ADDR']."', '".$realip."')";
										
										}
											
									$res_batchtkt=mysql_query($sql_batchtkt);
									$res_updatecount_old=mysql_query($sql_updatecount_old);
									$res_audit1=mysql_query($sql_audit1);
							
									//check the last batch_no
									$res_batchmeta=mysql_query("SELECT usrteamzone_idusrteamzone,wftasks_batchtype_idwftasks_batchtype FROM wftasks_batch WHERE idwftasks_batch=".$batch_no."");
									$fet_batchmeta=mysql_fetch_array($res_batchmeta);
									//changed to get the last max id given for this batch
						//			$sql_lastbatchno="SELECT max(voucher_number) as countbatch FROM tktin WHERE wftasks_batch_idwftasks_batch=".$batch_no."";
									$sql_lastbatchno="SELECT max(tktin.voucher_number) as countbatch,wftasks_batch.wftasks_batchtype_idwftasks_batchtype,wftasks_batch.batch_year FROM tktin
									INNER JOIN wftasks_batch ON tktin.wftasks_batch_idwftasks_batch=wftasks_batch.idwftasks_batch
									WHERE wftasks_batch.wftasks_batchtype_idwftasks_batchtype=".$fet_batchmeta['wftasks_batchtype_idwftasks_batchtype']."
									AND wftasks_batch.usrteamzone_idusrteamzone=".$fet_batchmeta['usrteamzone_idusrteamzone']."
									AND wftasks_batch.usrteamzone_idusrteamzone=".$_SESSION['WkAtToMrPa_userteamzoneid']."  ";//AND YEAR(createdon)='".$this_year."'
									$res_lastbatchno=mysql_query($sql_lastbatchno);
									$fet_lastbatchno=mysql_fetch_array($res_lastbatchno);
									
									//quick validation to avoid crossing over to another year in an older batch
									if ( ($fet_lastbatchno['countbatch']!='') && ($fet_lastbatchno['batch_year']!=$this_year) )
										{
										$error_batchoutdated="<div style=\"color:#ff0000\">You can't assign ".$fet_lastbatchno['batch_year']." in ".$this_year."  </div>";
										//exit;
										}
									
									//create the new batch_no
									$new_batchno=($fet_lastbatchno['countbatch']+1);							
									
									//new update the batch_no meta table
									$sql_updatecount="UPDATE wftasks_batch SET countbatch=(countbatch+1) WHERE idwftasks_batch=".$batch_no."";
									$res_updatecount=mysql_query($sql_updatecount);
									
									//get the tktid to update the tktin as well
									$sql_tktin=mysql_query("SELECT tktin_idtktin FROM wftasks WHERE idwftasks=".$_SESSION['wtaskid']." ");
									$fet_tktin=mysql_fetch_array($sql_tktin);
									
									//update the tkt as well
									$sql_batchtktnew="UPDATE tktin SET 
									wftasks_batch_idwftasks_batch='".$batch_no."',
									batch_number='".$new_batchno."',
									voucher_number='".$new_batchno."'
									WHERE idtktinPK=".$fet_ticket['idtktinPK']."";
									$res_batchtktnew=mysql_query($sql_batchtktnew);
									
									} else { //else if no batch now, then create some dummy queries to run the transction commit succssfully
									////////
									$res_tktin=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_batchtkt=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_lastbatchno=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_updatecount=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$sql_tktin=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_batchtktnew=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_batchtkt=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_updatecount_old=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_audit1=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									$res_batchmeta=mysql_query("SELECT idtktinPK from tktin LIMIT 1");
									/////
									}//batch now
					
					
					if ($fet_task_details['wftaskstrac_idwftaskstrac'] > 0 )
						{
					//redirect to the correct page
							if ( ($query_1) && ($query_2) && ($query_3)  && ($res_tktin) && (!isset($upload_error_1)) && (!isset($upload_error_2)) && (!isset($upload_error_3)) && (!isset($upload_error_4)) && ($res_batchtkt) && ($res_lastbatchno) && ($res_updatecount) && ($sql_tktin) && ($res_batchtktnew) && ($res_batchtkt) && ($res_updatecount_old) && ($res_audit1) && ($res_batchmeta) && (!isset($error_batchoutdated)) )
									{
									mysql_query("COMMIT");	
									///////////////////////////// close check and insert new subscriber ////////////////////////////
									//redirect to the correct page
									?>
									<script language="javascript">
									window.location='mytasks_history.php?tkt=<?php echo $fet_task['tktin_idtktin'];?>&task=<?php echo $fet_task['idwftasks'];?>&tab=2';
									</script>
									<?php
									exit;
									} else {
									mysql_query("ROLLBACK");
									?>
                                    <script language="javascript">
									 alert ('Sorry! Please Try Again!');
									</script>
                                    <?php
									if (isset($query_1)) { mysql_free_result($query_1);  }
									if (isset($query_2)) { mysql_free_result($query_2);  }
									if (isset($query_3)) { mysql_free_result($query_3); }
									}
								}
									
							} //if the no error 1_1
									
                    } //close no error on action 9
				
				} //close action 9				
						
		
		} //close form submission

		//Error trapping
		
		
		if ( 
		(strlen($error_1)>0) || (strlen($error_2)>0) || (strlen($error_3)>0) || (strlen($error_4)>0) ||
		(strlen($error_1_1)>0) || (strlen($error_1_2)>0) || 
		(strlen($error_2_1)>0) || (strlen($error_2_2)>0) || (strlen($error_2_3)>0) || (strlen($error_2_4)>0) ||
		(strlen($error_3_1)>0) || (strlen($error_3_2)>0) || (strlen($error_3_3)>0) || (strlen($error_3_4)>0) ||
		(strlen($error_4_1)>0) || (strlen($error_4_2)>0) || (strlen($error_4_3)>0) || (strlen($error_4_4)>0) ||
		(strlen($error_5_1)>0) || (strlen($error_5_2)>0) ||
		(strlen($error_6_1)>0) ||
		(strlen($error_8_1)>0) || (strlen($error_8_2)>0) ||
		(strlen($error_9_1)>0) || (strlen($error_9_2)>0) ||
		(strlen($upload_error_1)>0) || (strlen($upload_error_2)>0) || (strlen($upload_error_3)>0) || (strlen($upload_error_4)>0) ||
		(strlen($error_batchoutdated)>0)
		)
			{
			//redirect to the correct page
			$error_all=$error_1."+".$error_2."+".$error_3."+".$error_4."+".$error_1_1."+".$error_1_2."+".$error_2_1."+".$error_2_2."+".$error_2_3."+".$error_2_4."+".$error_3_1."+".$error_3_2."+".$error_3_3."+".$error_3_4."+".$error_4_1."+".$error_4_2."+".$error_4_3."+".$error_4_4."+".$error_5_1."+".$error_5_2."+".$error_6_1."+".$error_8_1."+".$error_8_2."+".$error_9_1."+".$error_9_2."+".$upload_error_1."+".$upload_error_2."+".$upload_error_3."+".$upload_error_4."+".$error_batchoutdated;
			?>
			<script language="javascript">
			window.location='mytasks_view.php?tkt=<?php echo $fet_task['tktin_idtktin'];?>&task=<?php echo $fet_task['idwftasks'];?>&err=<?php echo $error_all; ?>';
			</script>
			<?php
			exit;				
			}
		
/*		if (isset($error_1)) { echo $error_1; }
		if (isset($error_2)) { echo $error_2; }
		if (isset($error_3)) { echo $error_3; }
		if (isset($error_4)) { echo $error_4; }
		if (isset($error_1_1)) { echo $error_1_1; }
		if (isset($error_1_2)) { echo $error_1_2; }
		if (isset($error_2_1)) { echo $error_2_1; }
		if (isset($error_2_2)) { echo $error_2_2; }
		if (isset($error_2_3)) { echo $error_2_3; }
		if (isset($error_2_4)) { echo $error_2_4; }
		if (isset($error_3_1)) { echo $error_3_1; }
		if (isset($error_3_2)) { echo $error_3_2; }
		if (isset($error_3_3)) { echo $error_3_3; }
		if (isset($error_3_4)) { echo $error_3_4; }
		if (isset($error_4_1)) { echo $error_4_1; }
		if (isset($error_4_3)) { echo $error_4_3; }
		if (isset($error_4_4)) { echo $error_4_4; }
		if (isset($error_5_1)) { echo $error_5_1; }
		if (isset($error_5_2)) { echo $error_5_2; }
		if (isset($error_6_1)) { echo $error_6_1; }
		if (isset($error_8_1)) { echo $error_8_1; }
		if (isset($error_8_2)) { echo $error_8_2; }
		if (isset($error_9_1)) { echo $error_9_1; }
		if (isset($error_9_2)) { echo $error_9_2; }
		if (isset($upload_error_1)) { echo $upload_error_1; }
		if (isset($upload_error_2)) { echo $upload_error_2; }
		if (isset($upload_error_3)) { echo $upload_error_3; }
		if (isset($upload_error_4)) { echo $upload_error_4.$imageFileType; }
		if (isset($error_batchoutdated)) { echo $error_batchoutdated; }
*/	?>