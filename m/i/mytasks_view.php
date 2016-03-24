<?php
require_once('../required/config.php');
require_once('../../Connections/connSystem.php'); 
mysql_select_db($database_connSystem, $connSystem); //connect to DB

require_once('../required/check_user_mobile.php');
$display="none";

if (isset($_GET['tw']))
	{
	$tw=mysql_escape_string(trim($_GET['tw']));
	//echo $_GET['tw'];
	if ($tw=="new_tasks" )
		{
		$_SESSION['tw']="New Tasks";
		$_SESSION['tw_url']="mytasks_new.php";
		}
	}

if( (isset($_GET['pview'])) && ($_GET['pview']=='stkts') )
	{
	$_SESSION['pview']=preg_replace('/[^a-z\-_0-9\.:\/]/i','',mysql_real_escape_string(trim($_GET['pview'])));
	}

//error display
if(isset($_GET['err'])) 
	{ 
	$error_disp=$_GET['err']; 
	}

if (isset($_GET['task']))
	{
	$_SESSION['wtaskid']=preg_replace('/[^a-z\-_0-9\.:\/]/i','',mysql_real_escape_string(trim($_GET['task'])));
	}

//CHECK IF I HAVE BEEN DELEGATED TASKS TO DETERMINE IF THE LIST SHOWS OR NOT
$sql_delegated="SELECT usrrolename,utitle,fname,lname,wftasksdeleg_meta.idusrrole_from,usrrole.sysprofiles_idsysprofiles FROM wftasksdeleg_meta 
INNER JOIN usrrole ON wftasksdeleg_meta.idusrrole_from=usrrole.idusrrole
INNER JOIN usrac ON usrrole.idusrrole=usrac.usrrole_idusrrole
WHERE wftasksdeleg_meta.idusrrole_to=".$_SESSION['WkAtToMrPa_iduserrole']."
AND wftasksdeleg_meta.deleg_status=1";
$res_delegated=mysql_query($sql_delegated);
$num_delegated=mysql_num_rows($res_delegated);
$fet_delegated=mysql_fetch_array($res_delegated);

if ($num_delegated > 0)
	{
	$_SESSION['delegated']=1;
	$_SESSION['delegated_to']=$fet_delegated['usrrolename'];
	} else {
	$_SESSION['delegated']=0;
	}

//echo "<br><br><br><br><br><br><br>";
	
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
//echo "<br><br>".$sql_task;
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
	
$sql_co="SELECT idwftasks_co,fname,lname,usrrole.sysprofiles_idsysprofiles,idusrrole FROM wftasks_co 
INNER JOIN usrac ON wftasks_co.idusrrole_owner=usrac.usrrole_idusrrole 
INNER JOIN usrrole ON usrac.usrrole_idusrrole =usrrole.idusrrole 
WHERE idusrrole_acting=".$_SESSION['WkAtToMrPa_iduserrole']."
AND	idusrrole_owner=".$fet_task['usrrole_idusrrole']." AND co_status=1";
$res_co=mysql_query($sql_co);
$num_co=mysql_num_rows($res_co);
$fet_co=mysql_fetch_array($res_co);
//echo "<br><br>".$sql_co;
//create the variable
if ($num_co > 0)
	{
	$_SESSION['var_wftaskco']=$fet_co['idwftasks_co'];

	} else {
	$_SESSION['var_wftaskco']=0;
	}	
	
//find out where the task is as at now
		$sql_taskat="SELECT usrrole_idusrrole,wftaskstrac_idwftaskstrac FROM wftasks WHERE wftaskstrac_idwftaskstrac=".$fet_task['wftaskstrac_idwftaskstrac']." ORDER BY idwftasks DESC LIMIT 1";
		$res_taskat=mysql_query($sql_taskat);
		$fet_taskat=mysql_fetch_array($res_taskat);
//		echo "<br><br>".$sql_taskat;	
//echo "<br><br>".$sql_task;				

//Get the Ticket Details on the form
$sql_ticket="SELECT idtktinPK,tktchannelname,tktstatusname,tktcategoryname,locationname,tktlang_idtktlang,usrteamzone_idusrteamzone,
usrteam_idusrteam,tktin.tktgroup_idtktgroup,tktin.tktchannel_idtktchannel,tktin.tktstatus_idtktstatus,tktin.tktcategory_idtktcategory,
tktin.tkttype_idtkttype,sendername,senderphone,senderemail,refnumber,tktdesc,timereported,timedeadline,timeclosed,city_town,
loctowns_idloctowns,road_street,building_estate,unitno,waterac,kioskno,tkttype.idtkttype,tkttype.tkttypename,tktin.landmark,
tktin.sendergender,refnumber_prev,wftasks_batch_idwftasks_batch,voucher_number 
FROM tktin
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

//added by Dickson to facilitate the transaction on process_task_m.php
$_SESSION['tktno']=$fet_ticket['refnumber'];
$_SESSION['tktcat']=$fet_ticket['tktcategory_idtktcategory'];
$_SESSION['tktacno']=$fet_ticket['waterac'];
$_SESSION['tktkiosk']=$fet_ticket['kioskno'];
$_SESSION['tktsender']=$fet_ticket['sendername'];
$_SESSION['tktsenderphone']=$fet_ticket['senderphone'];
$_SESSION['tktsenderemail']=$fet_ticket['senderemail'];
$_SESSION['tktstreet']=$fet_ticket['road_street'];
$_SESSION['tktbuilding']=$fet_ticket['building_estate'];
$_SESSION['tktunitno']=$fet_ticket['unitno'];
$_SESSION['tktloc']=$fet_ticket['locationname'];
$_SESSION['usrgender']=$fet_ticket['sendergender'];
$_SESSION['directions']=$fet_ticket['landmark'];
$_SESSION['timereported']=$fet_ticket['timereported'];
$_SESSION['tktchannelname']=$fet_ticket['tktchannelname'];
$_SESSION['tktdesc']=$fet_ticket['tktdesc'];

//$tktaction=preg_replace('/[^a-z\-_0-9\.:\/]/i','',mysql_real_escape_string(trim($_POST['action_to'])));
//$updateperm=preg_replace('/[^a-z\-_0-9\.:\/]/i','',mysql_real_escape_string(trim($_POST['up'])));

//echo "<br><br><br>".$sql_ticket;
//ADDITION TO CATER FOR SECONDARY COMPLAINTS HISTORY --- BY DICKSON MARIRA ON JULY 25TH 2014
//If its a secondary ticket -- Get the ID of the primary ticket
//echo $fet_ticket['refnumber_prev']."----";

if(strlen($fet_ticket['refnumber_prev'])>0)
	{
	$sql_prevtkt="SELECT idtktinPK FROM tktin 
	WHERE refnumber='".$fet_ticket['refnumber_prev']."' LIMIT 1";
	$res_prevtkt=mysql_query($sql_prevtkt);
	$fet_prevtkt=mysql_fetch_array($res_prevtkt);
	$num_prevtkt=mysql_num_rows($res_prevtkt);
		
	if($num_prevtkt>0)
		{
		$_SESSION['prevtktid']=$fet_prevtkt['idtktinPK'];
		
		//Get the last task for this primary ticket.
		$sql_prevtsk="SELECT idwftasks FROM wftasks WHERE tktin_idtktin=".$fet_prevtkt['idtktinPK']." ORDER BY idwftasks DESC LIMIT 1";
		$res_prevtsk=mysql_query($sql_prevtsk);
		$fet_prevtsk=mysql_fetch_array($res_prevtsk);
		$num_prevtsk=mysql_num_rows($res_prevtsk);

		if($num_prevtsk>0)
			{
			$_SESSION['prevtskid']=$fet_prevtsk['idwftasks'];
			}
		}	
	}

//set the global tktid for this ticket
$_SESSION['tktid']=$fet_task['tktin_idtktin'];

flush();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8"> 
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<title><?php echo $app_title;?></title>
<link rel="stylesheet" href="../m_assets/main.css" />
<!-- <link rel="stylesheet" href="../../assets/online.css" /> -->
<script type="text/javascript" src="../m_assets/scripts/jquery.js"></script>
<script language="javascript" type="text/javascript" src="../m_assets/scripts/datetimepicker.js"></script>
<script type="text/javascript" src="../m_assets/scripts/jquery-ui.min.js"></script>
<script type="text/javascript" src="../m_assets/scripts/jquery-ui-timepicker-addon_.js"></script>
<script type="text/javascript" src="../m_assets/scripts/jquery.autocomplete.js"></script>
<script type="text/javascript" src="../m_assets/scripts/animatedcollapse.js">
/***********************************************
* Animated Collapsible DIV v2.4- (c) Dynamic Drive DHTML code library (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit Dynamic Drive at http://www.dynamicdrive.com/ for this script and 100s more
***********************************************/
</script>
<script type="text/javascript">
animatedcollapse.addDiv('details', 'fade=0,speed=400,group=pets')
animatedcollapse.addDiv('contacts', 'fade=0,speed=400,group=pets,persist=1,hide=1')
animatedcollapse.addDiv('dataform1', 'fade=0,speed=400,group=pets,hide=0')
animatedcollapse.addDiv('dataform2', 'fade=0,speed=400,group=pets,hide=0')
animatedcollapse.addDiv('dataform3', 'fade=0,speed=400,group=pets,hide=0')
animatedcollapse.addDiv('dataform4', 'fade=0,speed=400,group=pets,hide=0')
animatedcollapse.addDiv('dataform5', 'fade=0,speed=400,group=pets,hide=0')
animatedcollapse.addDiv('dataform6', 'fade=0,speed=400,group=pets,hide=0')
animatedcollapse.addDiv('dataform7', 'fade=0,speed=400,group=pets,hide=0')
animatedcollapse.addDiv('dataform8', 'fade=0,speed=400,group=pets,hide=0')
animatedcollapse.addDiv('dataform9', 'fade=0,speed=400,group=pets,hide=0')
animatedcollapse.addDiv('dataform10', 'fade=0,speed=400,group=pets,hide=0')
animatedcollapse.addDiv('feedback', 'fade=0,speed=400,group=pets,hide=1')
animatedcollapse.ontoggle=function($, divobj, state){ //fires each time a DIV is expanded/contracted
	//$: Access to jQuery
	//divobj: DOM reference to DIV being expanded/ collapsed. Use "divobj.id" to get its ID
	//state: "block" or "none", depending on state
}

animatedcollapse.init()
</script>
<script language="javascript">
//restrict to numbers or alpha
var numb = "0123456789.-";
var alpha = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ ";
function res(t,v){
var w = "";
for (i=0; i < t.value.length; i++) {
x = t.value.charAt(i);
if (v.indexOf(x,0) != -1)
w += x;
}
t.value = w;
}


function getAJAXHTTPREQ() { //fuction to return the xml http object
		var xmlhttp=false;	
		try{
			xmlhttp=new XMLHttpRequest();
		}
		catch(e)	{		
			try{			
				xmlhttp= new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch(e){
				try{
				xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
				}
				catch(e1){
					xmlhttp=false;
				}
			}
		}
		 	
		return xmlhttp;
    }
	
	
function getwkflow(tktcatId) {		
		
		var strURL="../ajax_calls/findworkflow_step_1.php?tktcat="+tktcatId;
		var req = getAJAXHTTPREQ();
			
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {						
						document.getElementById('workflowdiv').innerHTML=req.responseText;						
					} else {
						alert("There was a problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}		
	}	
	

//autocomplete the Location
$().ready(function() {
	$("#locationtown").autocomplete("../ajax_calls/findlocation_2.php", {
		width: 350,
		matchContains: true,
		//mustMatch: true,
		//minChars: 0,
		//multiple: true,
		//highlight: true,
		//multipleSeparator: ",",
		selectFirst: false
	});
});


//autocomplete the staff 
$().ready(function() {
	$("#recepient_alt").autocomplete("../ajax_calls/findrole_alt.php", {
		width: 450,
		matchContains: true,
		//mustMatch: true
		//minChars: 0,
		//multiple: true,
		//highlight: true,
		//multipleSeparator: ",",
		selectFirst: false
	});
});

</script>
<style type='text/css'>
    .actionlist {
     display: none;
	 padding:0px;
	 margin:0px;   
}

.optionvalue {
     border: 0px;   
}

</style>
<script type='text/javascript'>
//list the relevant fields basedon the action selected by the user
//<![CDATA[ 
$(window).load(function(){
$('.switchaction').change(function(){
    var selected = $(this).find(':selected');
    $('.actionlist').hide();
   $('.'+selected.val()).show(); 
    $('.optionvalue').html(selected.html());
});
});//]]>  


//hide show invalid reasons text box
    $(function() {
        $('#invalid_id').change(function(){
            $('.invalid_new').hide();
            $('#' + $(this).val()).show();
        });
    });
	
//hide or show the exceptional recepients
function showstuff(element){
    document.getElementById("other_exception").style.display = element=="other_exception"?"block":"none";
}

</script>
<!-- Preloader on Click Below -->
<script type="text/javascript" src="../../uilock/jquery.uilock.js"></script>
<script language="javascript">
			$(document).ready(function() {
				//$('#lock').click(function(){
				$('#button_passiton').click(function(){
				
					// To lock user interface interactions
					// Optinal: put html on top of the lock section,
					// like animated loading gif
					
					//$.uiLock('some html and <a href="#" onclick="$.uiUnlock();">unlock</a>');
				$.uiLock('<center class=msg_ok_overlay>Please Wait One Moment ...</center>');
					
				});
				
				
				// To unlock user interface interactions
				//$.uiUnlock();

			});
			
			
			$(document).ready(function() {
				//$('#lock').click(function(){
				$('#button_progup').click(function(){
				
					// To lock user interface interactions
					// Optinal: put html on top of the lock section,
					// like animated loading gif
					
					//$.uiLock('some html and <a href="#" onclick="$.uiUnlock();">unlock</a>');
				$.uiLock('<center class=msg_ok_overlay>Please Wait One Moment ...</center>');
					
				});
				
				
				// To unlock user interface interactions
				//$.uiUnlock();

			});
			
			

//get the next workflow step in casese of exceptions
//get the next workflow step in casese of exceptions
function nextstep(nextstepId) {		
		
		var strURL="../ajax_calls/ajax_nextstep.php?nextstep="+nextstepId;
		var req = getAJAXHTTPREQ();
			
		if (req) {
			
			req.onreadystatechange = function() {
				if (req.readyState == 4) {
					// only if "OK"
					if (req.status == 200) {						
						document.getElementById('nextstepdiv').innerHTML=req.responseText;						
					} else {
						alert("There was a problem while using XMLHTTP:\n" + req.statusText);
					}
				}				
			}			
			req.open("GET", strURL, true);
			req.send(null);
		}	
	}
	
	function lookup(inputString) {
		if(inputString.length == 0) {
			// Hide the suggestion box.
			$('#suggestions').hide();
		} else {
			$.post("../ajax_calls/ajax_nextstep_rec.php", {queryString: ""+inputString+""}, function(data){
				if(data.length >0) {
					$('#suggestions').show();
					$('#autoSuggestionsList').html(data);
				}
			});
		}
	} // lookup
	
	function fill(thisValue) {
		$('#inputString').val(thisValue);
		setTimeout("$('#suggestions').hide();", 200);
	}
</script>
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
                  	<div class="header_font_m"><a href="search.php" class="button_small">Customer Tickets</a> &raquo; Task Details</div>
                <?php } else { ?>
                	<div class="header_font_m"><a href="mytasks.php" class="button_small">My Tasks</a><a href="<?php echo $_SESSION['tw_url'];?>" class="button_small"><?php echo $_SESSION['tw'];?></a></div>
                <?php } ?>
                </div>
          </div>
   
<form method="post" action="process_task_m.php" name="task" id="task" autocomplete="off" enctype="multipart/form-data">   

   <table border="0" cellpadding="2" cellspacing="0" width="100%">
			<tr>
            	<td class="tbl_h">
                   <?php if(isset($_SESSION['pview'])) { ?>Task Details<?php } else { ?>Manage Task<?php } ?>
                </td>
            </tr>
            <tr>
            	<td class="tbl_data">
                <div class="small_font">Ticket No</div>
                <div class="d_font"><?php echo $fet_ticket['refnumber'];?></div>
                </td>
            </tr>
            <?php if(strlen($fet_ticket['waterac'])>1) { ?>
            <tr>
            	<td class="tbl_data">
                <div class="small_font">Account No</div>
                <div class="d_font"><?php echo $fet_ticket['waterac'];?></div>
                </td>
            </tr>
            <?php } ?>
            <tr>
            	<td class="tbl_data">
                <div class="small_font">Category</div>
                <div class="d_font"><?php echo $fet_ticket['tktcategoryname'];?></div>
                </td>
               
            </tr>
            <tr>
            	<td class="tbl_data">
                <div class="small_font">Customers Message</div>
                <div class="d_font"><?php echo $fet_ticket['tktdesc'];?></div>
                </td>
            </tr>
            <tr>
            	<td class="tbl_data">
                <div class="small_font">Location</div>
                <div class="d_font"><?php echo $fet_ticket['road_street']." ".$fet_ticket['building_estate']." ".$fet_ticket['unitno']." ".$fet_ticket['city_town']." ".$fet_ticket['landmark']; ?><?php echo $fet_ticket['landmark']; ?></div>
                </td>
            </tr>
            <tr>
            	<td class="tbl_data">
                <div class="small_font">Reported by</div>
                <div class="d_font"><?php echo $fet_ticket['sendername'];?></div>
                <div class="d_font"><a href="tel:<?php echo $fet_ticket['senderphone'];?>"><?php echo $fet_ticket['senderphone'];?></a></div>
                </td>
            </tr>
            <tr>
           	  <td class="tbl_data">
                <div class="small_font">Task From:</div>
                <div class="d_font"><?php
						if ($fet_task['sender_idusrrole']>0)
							{ 
							$sql_sender="SELECT usrrole.usrrolename,usrac.utitle,usrac.lname,usrac.fname,idusrrole,usrac.usrname FROM usrrole,usrac	
							WHERE usrrole.idusrrole=".$fet_task['sender_idusrrole']." AND usrac.idusrac=".$fet_task['sender_idusrac']." LIMIT 1";
							$res_sender=mysql_query($sql_sender);

							$fet_sender=mysql_fetch_array($res_sender);
							echo $fet_sender['usrrolename'] ."<br><small>".$fet_sender['utitle']." ".$fet_sender['fname']." ".$fet_sender['lname']."</small>";
							//hidden field of the sender id
							echo "<input type=\"hidden\" name=\"task_sent_from\" value=\"".$fet_sender['idusrrole']."\" >";
							} else {
							echo $lbl_system;
							}
							//store this usrname account on a session temporarily
							$_SESSION['NoRTS']=" usrname!='0'";
							
						?>
                        </div>
              </td>
            </tr>
            <tr>
            	<td class="tbl_data" style="background-color:#FFFFCC">
                <div class="small_font">Task Message:</div>
                <div class="d_font"><?php echo $fet_task['taskdesc'];;?></div>
                <div class="d_font">
                	<div>
					<?php if( (isset($_SESSION['prevtktid'])) && (isset($_SESSION['prevtskid'])) ) { ?>
                        <a href="mytasks_history.php?tkt=<?php echo $fet_task['tktin_idtktin'];?>&task=<?php echo $_SESSION['wtaskid'];?>&tkt_st=<?php echo $_SESSION['prevtktid'];?>&task_st=<?php echo $_SESSION['prevtskid'];?>"> View Task History &raquo;</a>
                    <?php } else { ?>     
                        <a href="mytasks_history.php?tkt=<?php echo $fet_task['tktin_idtktin'];?>&task=<?php echo $_SESSION['wtaskid'];?>"> View Task History &raquo;</a>
                    <?php } ?>
                    </div>
                </div>
                </td>
               
            </tr>
            
            <!-- START ERROR DISPLAY HERE -->
            <?php if (isset($error_disp)) {?> <tr><td class="tbl_data"><?php echo $error_disp; ?></td></tr><?php } ?>
            <tr>
            	<td>
                <!-- START EXTRA FORMS LOAD HERE -->
                    <?php
                     //DISPLAY THE FORM FOR THIS TASK
                     //but first, determine if the taskflow is zero=0 and if so, get the one to use from the exceptions table
                   /*  if ($fet_task['wftskflow_idwftskflow'] > 0)
                        {
                        $wftskflow=$fet_task['wftskflow_idwftskflow'];
                        } else {
                        $sql_newtskflow="SELECT wftskflow_idwftskflow FROM wftasks_exceptions WHERE wftasks_idwftasks=".$fet_task['wftasks_idwftasks']." LIMIT 1";
                        $res_newtskflow=mysql_query($sql_newtskflow);
                        $fet_newtskflow=mysql_fetch_array($res_newtskflow);
                        
                        $wftskflow=$fet_newtskflow['wftskflow_idwftskflow'];
                        }
			 */
					 //the dataform is determined by
					 //a) userprofileid
					 //b) category of task
					 //The userprofile id is dtermined by the OWNER of the task. In case the task is delegated 
					 //or under care/off, then we need to change the query 
					 
					if ($fet_delegated['sysprofiles_idsysprofiles']>0) //delegate
						{
						$task_profile_owner=$fet_delegated['sysprofiles_idsysprofiles'];
						} else if ($fet_co['sysprofiles_idsysprofiles']>0) { //care of
						$task_profile_owner=$fet_co['sysprofiles_idsysprofiles'];
						} else { //else none of the above
						$task_profile_owner=$_SESSION['WkAtToMrPa_iduserprofile'];
						}
						
					 
					$sql_formdata="SELECT idwfprocassetsaccess, assetname, perm_read, perm_write, perm_required, wfprocassets.wfprocdtype_idwfprocdtype, idwfprocassets, wfprocassetsgrouplbl,sysprofiles_idsysprofiles,wfprocassetsaccess.wfprocforms_idwfprocforms
					FROM wfprocassetsaccess
					INNER JOIN wfprocassets ON wfprocassetsaccess.wfprocassets_idwfprocassets = wfprocassets.idwfprocassets
					INNER JOIN wfprocassetsgroup ON wfprocassets.wfprocassetsgroup_idwfprocassetsgroup = wfprocassetsgroup.idwfprocassetsgroup
					INNER JOIN wfprocforms_cats ON wfprocassetsaccess.wfprocforms_idwfprocforms = wfprocforms_cats.wfprocforms_idwfprocforms 
					WHERE sysprofiles_idsysprofiles=".$task_profile_owner." AND wfprocforms_cats.tktcategory_idtktcategory=".$fet_ticket['tktcategory_idtktcategory']." AND wfprocassetsaccess.perm_read=1 ORDER BY wfprocassetsgrouplbl ASC,ordering ASC";
		
					 $res_formdata=mysql_query($sql_formdata);
					 $num_formdata=mysql_num_rows($res_formdata);
					 $fet_formdata=mysql_fetch_array($res_formdata);
					//echo $sql_formdata;
					 $lastTFM_nest = ""; //for nesting
					//echo $sql_formdata;

				 if ($num_formdata > 0)
					{ //[001]
				/*	//process if form fields are required
					if ( (isset($_POST['formaction'])) && ($_POST['formaction']=="process_task") )
						{
						//echo $_POST['formaction'];
						//echo "processed <br>";
						//check the db for this field by reusing the sql statement above
						$sql_val="SELECT idwfprocassetsaccess,assetname,perm_read,perm_write,perm_required,wfprocassets.wfprocdtype_idwfprocdtype,idwfprocassets FROM wfprocassetsaccess 
						INNER JOIN wfprocassets ON wfprocassetsaccess.wfprocassets_idwfprocassets=wfprocassets.idwfprocassets
						WHERE sysprofiles_idsysprofiles=".$_SESSION['WkAtToMrPa_iduserprofile']." AND wfprocassetsaccess.perm_read=1 ORDER BY wfprocassets.ordering ASC";
						$res_val=mysql_query($sql_val);
						$num_val=mysql_num_rows($res_val);
						$fet_val=mysql_fetch_array($res_val);
					
					//process if the form fields have values
						}*/
	
				echo "<input type=\"hidden\" name=\"formdata_available\" value=\"1\">";				
				 ?>
                    <?php
			$dmn=1;

			do 	{
				$TFM_nest = $fet_formdata['wfprocassetsgrouplbl'];
				
				if ($lastTFM_nest != $TFM_nest) 
					{ 
					$lastTFM_nest = $TFM_nest; 
					
					 if ($dmn>1)
						{
						echo "</div>";
						}
					?>	
            
                    <a href="#" style="text-decoration:none" rel="toggle[dataform<?php echo $dmn;?>]" data-openimage="../../be_assets/btns/btn_collapse.gif" data-closedimage="../../be_assets/btns/btn_expand.gif">
                    <div class="divcol">
                    <img src="../../be_assets/btns/btn_collapse.gif" border="0" align="absmiddle" /> <?php echo $fet_formdata['wfprocassetsgrouplbl'];?>                    </div>
                    </a>     
                    
                	<div id="dataform<?php echo $dmn;?>" > 
		            <?php 

					$dmn=$dmn+1;
					} //End of Basic-UltraDev Simulated Nested Repeat?>
                                
                    <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="25%" class="tbl_data">
                        <?php
                        if ($fet_formdata['perm_required']==1) 
                        {
                        echo $lbl_asterik;
                        }
                        echo $fet_formdata['assetname'];
                        ?>
                        </td>
                      <td class="tbl_data">
                        <?php
						if (
							($fet_task['usrrole_idusrrole']!=$_SESSION['WkAtToMrPa_iduserrole']) 
							&& ($fet_task['usrrole_idusrrole']!=$fet_delegated['idusrrole_from']) 
							&& ((!isset($is_group_task)) || ($is_group_task==0)) 
							&& ($_SESSION['var_wftaskco']==0)
							)
								{
								$lock_values=1;
								} else {
								$lock_values=0;
								}
						//	echo $lock_values;
                        //retreieve the date from the db
                        //check the db for values for this field /$fet_task['idwftasks']
                        
                        /*if ( ($fet_formdata['wfprocdtype_idwfprocdtype']==1) || ($fet_formdata['wfprocdtype_idwfprocdtype']==5) || ($fet_formdata['wfprocdtype_idwfprocdtype']==6) || ($fet_formdata['wfprocdtype_idwfprocdtype']==7))
                            {
                            $sql_data="SELECT idwfassetsdata,value_choice,wfprocassetschoice_idwfprocassetschoice FROM wfassetsdata WHERE wfprocassetsaccess_idwfprocassetsaccess=".$fet_formdata['idwfprocassetsaccess']." AND wftasks_idwftasks=".$fet_task['idwftasks']." LIMIT 1";
                            } else if ($fet_formdata['wfprocdtype_idwfprocdtype']==2) { //menulist
                            $sql_data="SELECT idwfassetsdata,value_choice,wfprocassetschoice_idwfprocassetschoice FROM wfassetsdata WHERE wfprocassetsaccess_idwfprocassetsaccess=".$fet_formdata['idwfprocassetsaccess']." AND wftasks_idwftasks=".$fet_task['idwftasks']." LIMIT 1";
                            }
                            */
    //						$sql_data="SELECT idwfassetsdata,value_choice,wfprocassetschoice_idwfprocassetschoice FROM wfassetsdata WHERE wfprocassetsaccess_idwfprocassetsaccess=".$fet_formdata['idwfprocassetsaccess']." AND wftasks_idwftasks=".$fet_task['idwftasks']." LIMIT 1";	
                        /*	$sql_data="SELECT idwfassetsdata,value_choice,wfprocassetschoice_idwfprocassetschoice FROM wfassetsdata 
                            INNER JOIN wfprocassets ON wfassetsdata.wfprocassets_idwfprocassets=wfprocassets.idwfprocassets
                            WHERE wfprocassets.wfproc_idwfproc=".$_SESSION['wfproc_idwfproc']." 
                            AND wfprocassets.wfprocdtype_idwfprocdtype=".$fet_formdata['wfprocdtype_idwfprocdtype']."
                            AND wftaskstrac_idwftaskstrac=".$_SESSION['wftaskstrac']."
                            LIMIT 1";
                            */
                            $sql_data="SELECT idwfassetsdata,value_choice,value_path,wfprocassetschoice_idwfprocassetschoice,date(wfassetsdata.createdon) as asset_date FROM wfassetsdata 
                            INNER JOIN wfprocassets ON wfassetsdata.wfprocassets_idwfprocassets=wfprocassets.idwfprocassets 
                            WHERE wfprocassets.wfprocforms_idwfprocforms=".$fet_formdata['wfprocforms_idwfprocforms']."
                            AND wfprocassets.wfprocdtype_idwfprocdtype=".$fet_formdata['wfprocdtype_idwfprocdtype']."
                            AND wfassetsdata.wfprocassets_idwfprocassets=".$fet_formdata['idwfprocassets']."
                            AND wftaskstrac_idwftaskstrac=".$_SESSION['wftaskstrac']."
                            LIMIT 1";
                            
                            $res_data=mysql_query($sql_data);
                            $num_data=mysql_num_rows($res_data);
                            $fet_data=mysql_fetch_array($res_data);
//echo $sql_data."<br>";
                            //determine logic if update or delete depending on whether there is value in the db
                            if ($num_data > 0)
                                {
                                $transaction="UPDATE";
                                } else {
                                $transaction="INSERT";
                                }
    
                            //check for the primary key if record exists
                            if ( (isset($fet_data['idwfassetsdata'])) && ($fet_data['idwfassetsdata']>0) )
                                {
                                $idassetdata=$fet_data['idwfassetsdata'];
                                } else {
                                $idassetdata=0;
                                }	
                        //	echo $fet_data['idwfassetsdata'];
                        
                         //this is a text box
                         if ($fet_formdata['wfprocdtype_idwfprocdtype']==1) 
                            {
                            //check permissions
                        //	echo $fet_formdata['perm_write'];
                            if (
								($fet_formdata['perm_write']==0) 
								|| ($fet_task['wftskstatustypes_idwftskstatustypes']==1) 
								||  ($fet_task['wftskstatustypes_idwftskstatustypes']==4) 
								||  (($fet_task['usrac_idusrac']!=$_SESSION['WkAtToMrPa_idacname']) && ($num_co==0) && ($num_delegated==0) )
								||  (($fet_task['usrrole_idusrrole']!=$_SESSION['WkAtToMrPa_iduserrole']) && ($num_co==0) && ($num_delegated==0) )
								)
                                {	
                                $readonly="readonly=\"readonly\" style=\"background-color:#f4f4f4\" ";
                                } else {
                                $readonly="";
                                }
                            
                            echo "<input type=\"text\" ".$readonly." ";
                            //check if it is a post
                                if (isset($_POST['item_'.$fet_formdata['idwfprocassetsaccess'].'']))
                                    {
                                    echo " value=\"".$_POST['item_'.$fet_formdata['idwfprocassetsaccess'].'']."\" ";
                                    } else {
                                    echo " value=\"".$fet_data['value_choice']."\" ";
                                    }
                                //highlight document show if error
                            //	if (isset($error."_".$fet_formdata['idwfprocassetsaccess']))
                                if ( (isset($_POST['required_'.$fet_formdata['idwfprocassetsaccess'].''])) && ($_POST['required_'.$fet_formdata['idwfprocassetsaccess'].'']==1) &&  ($_POST['item_'.$fet_formdata['idwfprocassetsaccess'].'']=="") )
                                    {
                                    echo " style=\"border:1px solid #ff0000\" ";
                                    }
                                                        
                            echo " name=\"item_".$fet_formdata['idwfprocassetsaccess']."\" maxlength=\"50\">";
                            
                            echo "<input type=\"hidden\" name=\"required_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$fet_formdata['perm_required']."\">\r\n
                            <input type=\"hidden\" name=\"transtype_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$transaction."\" > \r\n
                            <input type=\"hidden\" name=\"itemtype_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$fet_formdata['wfprocdtype_idwfprocdtype']."\">
                            <input type=\"hidden\" name=\"itempk_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$idassetdata."\">
                            ";
                            
							 //audit trail check for this value
							$res_auditcheck=mysql_query('SELECT idwfassetsdata FROM audit_wfassetsdata WHERE idwfassetsdata='.$idassetdata.' LIMIT 1');
							$num_auditcheck=mysql_num_rows($res_auditcheck);
							if ($num_auditcheck>0)
								{
								echo "<a title=\"Audit Trail\" href=\"pop_audit_xforms.php?fvid=".$idassetdata."&amp;&amp;tabview=1&amp;tabview=1&keepThis=true&TB_iframe=true&height=300&width=600&inlineId=hiddenModalContent&modal=false\" class=\"thickbox\" href=\"pop_audit_xforms.php\"><img src=\"../../assets/btns/btn_at.jpg\" border=\"0\" align=\"absmiddle\"></a>";
								}
							}
                         
						   
						   
							
                        //select menu	
                        if ($fet_formdata['wfprocdtype_idwfprocdtype']==2) 
                            {
							
							if (
								($fet_formdata['perm_write']==0) 
								|| ($fet_task['wftskstatustypes_idwftskstatustypes']==1) 
								||  ($fet_task['wftskstatustypes_idwftskstatustypes']==4) 
								||  (($fet_task['usrac_idusrac']!=$_SESSION['WkAtToMrPa_idacname']) && ($num_co==0) && ($num_delegated==0) )
								||  (($fet_task['usrrole_idusrrole']!=$_SESSION['WkAtToMrPa_iduserrole']) && ($num_co==0) && ($num_delegated==0) )
								)
                                {	
                                $readonly="disabled=\"disabled\" style=\"background-color:#f4f4f4\" ";
                                } else {
                                $readonly="";
                                }
                                                    
                            $sql_choices="SELECT idwfprocassetschoice,assetchoice FROM wfprocassetschoice WHERE wfprocassets_idwfprocassets=".$fet_formdata['idwfprocassets']."";
                            $res_choices=mysql_query($sql_choices);
                            $fet_choices=mysql_fetch_array($res_choices);
                        //echo $sql_choices;
						//echo $fet_formdata['idwfprocassets'];	
                            echo "<select name=\"item_".$fet_formdata['idwfprocassetsaccess']."\" ";
                                
                                if ( (isset($_POST['required_'.$fet_formdata['idwfprocassetsaccess'].''])) && ($_POST['required_'.$fet_formdata['idwfprocassetsaccess'].'']==1) &&  ($_POST['item_'.$fet_formdata['idwfprocassetsaccess'].'']=="") )
                                    {
                                    echo " style=\"border:1px solid #ff0000\" ";
                                    }
                            
                            echo "  >";
                            //get the select options data
                            echo "<option value=\"\" ".$readonly.">---</option>";
                                do {
                                    
                                    echo "<option ";
                                        //select the default if there is a value
                                        if ( (isset($fet_data['idwfassetsdata'])) && ($fet_data['wfprocassetschoice_idwfprocassetschoice']==$fet_choices['idwfprocassetschoice']) )
                                            {
                                            echo " selected=\"selected\" ";
                                            } else {
                                            echo  $readonly;
                                            }
                                    echo " value=\"".$fet_choices['idwfprocassetschoice']."\">".$fet_choices['assetchoice']."</option>";
                                } while ($fet_choices=mysql_fetch_array($res_choices));
                            echo "</select>";						
                            
                            //if readonly is set, then place a hidden value
                            if (strlen($readonly)>3)
                                {
                                echo "<input type=\"hidden\" name=\"item_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$fet_choices['idwfprocassetschoice']."\">";
                                }
                                
                                echo "<input type=\"hidden\" name=\"required_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$fet_formdata['perm_required']."\">\r\n
                                <input type=\"hidden\" name=\"transtype_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$transaction."\" > \r\n
                                <input type=\"hidden\" name=\"itemtype_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$fet_formdata['wfprocdtype_idwfprocdtype']."\">
                                <input type=\"hidden\" name=\"itempk_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$idassetdata."\">
                                ";
                            
							 //audit trail check for this value
							$res_auditcheck=mysql_query('SELECT idwfassetsdata FROM audit_wfassetsdata WHERE idwfassetsdata='.$idassetdata.' LIMIT 1');
							$num_auditcheck=mysql_num_rows($res_auditcheck);
							if ($num_auditcheck>0)
								{
								echo "<a title=\"Audit Trail\" href=\"pop_audit_xforms.php?fvid=".$idassetdata."&amp;&amp;tabview=1&amp;tabview=1&keepThis=true&TB_iframe=true&height=300&width=600&inlineId=hiddenModalContent&modal=false\" class=\"thickbox\" href=\"pop_audit_xforms.php\"><img src=\"../../assets/btns/btn_at.jpg\" border=\"0\" align=\"absmiddle\"></a>";
								}
								
							}
							
							
                        
                        //file upload
                        if ($fet_formdata['wfprocdtype_idwfprocdtype']==3) 
                            {
                            //check permissions
                            if ($fet_formdata['perm_write']==0) 
                                {	
                                $readonly="disabled=\"disabled\"  title=\"You cannot replace this file\" ";
                                } else {
                                $readonly="";
                                }
							
							//display the file if it exists and if read permissions exist
							 if (($fet_formdata['perm_read']==1)  && (strlen($fet_data['value_path'])>4) )
                                {
								//get the file name explode by _ and get the first array 0
								//$file_path=explode('/',$fet_data['value_path']);
								$count_tkt=(strlen($_SESSION['tktin_idtktin'])+1);
								//$file_actual=explode('_',$fet_data['value_path']);
								echo "<div style=\"padding:5px 0px;\"><a class=\"thickbox\" href=\"download_file.php?f=".$idassetdata."&amp;i=".$fet_data['asset_date']."&amp;keepThis=true&TB_iframe=true&height=100&width=780&inlineId=hiddenModalContent&modal=false\"><img align=\"absmiddle\" border=\"0\" src=\"../../be_assets/btns/btn_download_small.jpg\" title=\"".$fet_data['value_path']."\">&nbsp;&nbsp;".substr($fet_data['value_path'],$count_tkt,50)."</a></div>";
								}
								
							//if there is a file, then warn user that he could replace this file
							if ( (strlen($fet_data['value_path'])>1) && ($fet_formdata['perm_write']==1) )
								{
								echo "Replace this Document ? &raquo;&nbsp;";
								}		
                            //show the following only if you have write permissions otherwise disable	
                            echo "<input type=\"file\" ".$readonly."  ";
                            
                            if ( (isset($_POST['required_'.$fet_formdata['idwfprocassetsaccess'].''])) && ($_POST['required_'.$fet_formdata['idwfprocassetsaccess'].'']==1) &&  ($_POST['item_'.$fet_formdata['idwfprocassetsaccess'].'']=="") )
                                    {
                                    echo " style=\"border:1px solid #ff0000\" ";
                                    }
                            
                            echo " name=\"item_".$fet_formdata['idwfprocassetsaccess']."\" size=\"10\"  >";
                            echo "<span style=\"cursor:pointer;color:red;\" onclick=\"document.task.item_".$fet_formdata['idwfprocassetsaccess'].".value=''\">unselect</span>";
                            
                            echo "<input type=\"hidden\" name=\"required_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$fet_formdata['perm_required']."\">\r\n
                                <input type=\"hidden\" name=\"transtype_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$transaction."\" > \r\n
                                <input type=\"hidden\" name=\"itemtype_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$fet_formdata['wfprocdtype_idwfprocdtype']."\">
                                <input type=\"hidden\" name=\"itempk_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$idassetdata."\">
                                ";
                            
                             //audit trail check for this value
							$res_auditcheck=mysql_query('SELECT idwfassetsdata FROM audit_wfassetsdata WHERE idwfassetsdata='.$idassetdata.' LIMIT 1');
							$num_auditcheck=mysql_num_rows($res_auditcheck);
							if ($num_auditcheck>0)
								{
								echo "<a title=\"Audit Trail\" href=\"pop_audit_xforms.php?fvid=".$idassetdata."&amp;&amp;tabview=1&amp;tabview=1&keepThis=true&TB_iframe=true&height=300&width=600&inlineId=hiddenModalContent&modal=false\" class=\"thickbox\" href=\"pop_audit_xforms.php\"><img src=\"../../assets/btns/btn_at.jpg\" border=\"0\" align=\"absmiddle\"></a>";
								}
                            
                            }
                            
                        //checkbox
                        if ($fet_formdata['wfprocdtype_idwfprocdtype']==4) 
                            {
                            
							if (
								($fet_formdata['perm_write']==0) 
								|| ($fet_task['wftskstatustypes_idwftskstatustypes']==1) 
								||  ($fet_task['wftskstatustypes_idwftskstatustypes']==4) 
								||  (($fet_task['usrac_idusrac']!=$_SESSION['WkAtToMrPa_idacname']) && ($num_co==0) && ($num_delegated==0) )
								||  (($fet_task['usrrole_idusrrole']!=$_SESSION['WkAtToMrPa_iduserrole']) && ($num_co==0) && ($num_delegated==0) )
								)
                                {	
                                $readonly=1;
                                } else {
                                $readonly=0;
                                }
							
							if ((isset($fet_data['wfprocassetschoice_idwfprocassetschoice'])) && ($fet_data['value_choice']=="1"))
                                    {
                                    $value_chkbox=1;
                                    } else {
									$value_chkbox=0;
									}
							//echo $readonly;		
							echo "<label for=\"".$fet_formdata['idwfprocassetsaccess']."\">";
							
							//check if the persion has permission to edit the checkbox to know what to show 
							if ($readonly==1)
								{
								
								 echo "<input type=\"hidden\" value=\"".$value_chkbox."\" name=\"item_".$fet_formdata['idwfprocassetsaccess']."\" id=\"".$fet_formdata['idwfprocassetsaccess']."\" value=\"1\">";
                            	
									if ($value_chkbox==1)
										{
										echo "<img border=\"0\" title=\"Edit Disabled\" align=\"absmiddle\" src=\"../../assets/icons/icon_fchkbox_on.png\">";
										} else {
										echo "<img border=\"0\"  title=\"Edit Disabled\"  align=\"absmiddle\" src=\"../../assets/icons/icon_fchkbox_off.png\">";
										}
									
								} else {
                           		 
								 echo "<input type=\"checkbox\" ";
                                	if ((isset($fet_data['wfprocassetschoice_idwfprocassetschoice'])) && ($fet_data['value_choice']=="1"))
                                    	{
	                                    echo " checked=\"checked\" ";
    	                                }
        			                    echo " name=\"item_".$fet_formdata['idwfprocassetsaccess']."\" id=\"".$fet_formdata['idwfprocassetsaccess']."\" value=\"1\"> <small>( click to select )</small>";
                            
								}
							
                            
                            echo "<input type=\"hidden\" name=\"required_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$fet_formdata['perm_required']."\">\r\n
                                <input type=\"hidden\" name=\"transtype_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$transaction."\" > \r\n
                                <input type=\"hidden\" name=\"itemtype_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$fet_formdata['wfprocdtype_idwfprocdtype']."\">
                                <input type=\"hidden\" name=\"itempk_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$idassetdata."\">
                                ";
                            echo "</label>";
							
							 //audit trail check for this value
							$res_auditcheck=mysql_query('SELECT idwfassetsdata FROM audit_wfassetsdata WHERE idwfassetsdata='.$idassetdata.' LIMIT 1');
							$num_auditcheck=mysql_num_rows($res_auditcheck);
							if ($num_auditcheck>0)
								{
								echo "<a title=\"Audit Trail\" href=\"pop_audit_xforms.php?fvid=".$idassetdata."&amp;&amp;tabview=1&amp;tabview=1&keepThis=true&TB_iframe=true&height=300&width=600&inlineId=hiddenModalContent&modal=false\" class=\"thickbox\" href=\"pop_audit_xforms.php\"><img src=\"../../assets/btns/btn_at.jpg\" border=\"0\" align=\"absmiddle\"></a>";
								}
							
                            }
                        
                        
                        //yes no questions
                        if ($fet_formdata['wfprocdtype_idwfprocdtype']==5) 
                            {	
    						
							if (
								($fet_formdata['perm_write']==0) 
								|| ($fet_task['wftskstatustypes_idwftskstatustypes']==1) 
								||  ($fet_task['wftskstatustypes_idwftskstatustypes']==4) 
								||  (($fet_task['usrac_idusrac']!=$_SESSION['WkAtToMrPa_idacname']) && ($num_co==0) && ($num_delegated==0) )
								||  (($fet_task['usrrole_idusrrole']!=$_SESSION['WkAtToMrPa_iduserrole']) && ($num_co==0) && ($num_delegated==0) )
								)
                                {	
                                $readonly=1;
                                } else {
                                $readonly=0;
                                }
							
							if ($readonly==0)
								{
                            	echo "<label for=\"radio_1\"><input id=\"radio_1\" ";
                            	if ((isset($fet_data['value_choice'])) && ($fet_data['value_choice']=="YES"))
                             	   {
                              	 	 echo " checked=\"checked\" ";
                               	 	}
                           		 echo " type=\"radio\" value=\"YES\" name=\"item_".$fet_formdata['idwfprocassetsaccess']."\"><strong> YES </strong></label>";
                           		 echo "<span style=\"padding:0px 15px 0px 15px\"></span>";
								                           
							
								echo "<label for=\"radio_2\"><input id=\"radio_2\" type=\"radio\" ";
    	                            if ((isset($fet_data['value_choice'])) && ($fet_data['value_choice']=="NO"))
        	                        {
            	                    echo " checked=\"checked\" ";
                	                }
                    	        echo " value=\"NO\" name=\"item_".$fet_formdata['idwfprocassetsaccess']."\"><strong> NO </strong></label>";
								
								} else {
								
								 if ($fet_data['value_choice']=="YES")
								 	{
									 echo "<img src=\"../../assets/icons/icon_radio_on.png\" border=\"0\" align=\"absmiddle\" > <strong>YES</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
									 echo "<img src=\"../../assets/icons/icon_radio_off.png\" border=\"0\" align=\"absmiddle\" > <strong>NO</strong>";
									 } else if ($fet_data['value_choice']=="NO") {
									 echo "<img src=\"../../assets/icons/icon_radio_off.png\" border=\"0\" align=\"absmiddle\" > <strong>YES</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
									 echo "<img src=\"../../assets/icons/icon_radio_on.png\" border=\"0\" align=\"absmiddle\" > <strong>NO</strong>";
									 }
								
								
								}
    
                                echo "<input type=\"hidden\" name=\"required_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$fet_formdata['perm_required']."\">\r\n
                                <input type=\"hidden\" name=\"transtype_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$transaction."\" > \r\n
                                <input type=\"hidden\" name=\"itemtype_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$fet_formdata['wfprocdtype_idwfprocdtype']."\">
                                <input type=\"hidden\" name=\"itempk_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$idassetdata."\">
                                ";
                            
							 //audit trail check for this value
							$res_auditcheck=mysql_query('SELECT idwfassetsdata FROM audit_wfassetsdata WHERE idwfassetsdata='.$idassetdata.' LIMIT 1');
							$num_auditcheck=mysql_num_rows($res_auditcheck);
							if ($num_auditcheck>0)
								{
								echo "<a title=\"Audit Trail\" href=\"pop_audit_xforms.php?fvid=".$idassetdata."&amp;&amp;tabview=1&amp;tabview=1&keepThis=true&TB_iframe=true&height=300&width=600&inlineId=hiddenModalContent&modal=false\" class=\"thickbox\" href=\"pop_audit_xforms.php\"><img src=\"../../assets/btns/btn_at.jpg\" border=\"0\" align=\"absmiddle\"></a>";
								}
							
                            }
                        
                        //date only
                        if ($fet_formdata['wfprocdtype_idwfprocdtype']==6) 
                            {
							
							if (
								($fet_formdata['perm_write']==0) 
								|| ($fet_task['wftskstatustypes_idwftskstatustypes']==1) 
								||  ($fet_task['wftskstatustypes_idwftskstatustypes']==4) 
								||  (($fet_task['usrac_idusrac']!=$_SESSION['WkAtToMrPa_idacname']) && ($num_co==0) && ($num_delegated==0) )
								||  (($fet_task['usrrole_idusrrole']!=$_SESSION['WkAtToMrPa_iduserrole']) && ($num_co==0) && ($num_delegated==0) )
								)
                                {	
                                $readonly_off="";
								$readonly_click="";
								$readonly_style=" style=\"background-color:#f4f4f4\" ";
                                } else {
                                $readonly_off="<script language=\"javascript\">
                                $('#item_".$fet_formdata['idwfprocassetsaccess']."').datepicker({
                                    controlType: 'select',
                                    dateFormat: 'dd/mm/yy'
                                });
                                </script>";
								$readonly_click=" onClick=\"datetimepicker('item_".$fet_formdata['idwfprocassetsaccess']."');\" ";
								$readonly_style=" ";
                                }
							
							
                            echo "<input size=\"10\" ";
                                if (isset($fet_data['value_choice']))
                                {
                                echo " value=\"".$fet_data['value_choice']."\" ";
                                } else {
                                echo " value=\"\" ";
                                }
                            
                            //display if value missing
                            if ( (isset($_POST['required_'.$fet_formdata['idwfprocassetsaccess'].''])) && ($_POST['required_'.$fet_formdata['idwfprocassetsaccess'].'']==1) &&  ($_POST['item_'.$fet_formdata['idwfprocassetsaccess'].'']=="") )
                                    {
                                    echo " style=\"border:1px solid #ff0000\" ";
                                    }
                            
                            echo  $readonly_click." ".$readonly_style."  name=\"item_".$fet_formdata['idwfprocassetsaccess']."\" type=\"text\" id=\"item_".$fet_formdata['idwfprocassetsaccess']."\"  readonly=\"readonly\" >" ;					
                            echo $readonly_off;
                                
                            echo "<input type=\"hidden\" name=\"required_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$fet_formdata['perm_required']."\">\r\n
                                <input type=\"hidden\" name=\"transtype_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$transaction."\" > \r\n
                                <input type=\"hidden\" name=\"itemtype_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$fet_formdata['wfprocdtype_idwfprocdtype']."\">
                                <input type=\"hidden\" name=\"itempk_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$idassetdata."\">
                                ";	
                             //audit trail check for this value
							$res_auditcheck=mysql_query('SELECT idwfassetsdata FROM audit_wfassetsdata WHERE idwfassetsdata='.$idassetdata.' LIMIT 1');
							$num_auditcheck=mysql_num_rows($res_auditcheck);
							if ($num_auditcheck>0)
								{
								echo "<a title=\"Audit Trail\" href=\"pop_audit_xforms.php?fvid=".$idassetdata."&amp;&amp;tabview=1&amp;tabview=1&keepThis=true&TB_iframe=true&height=300&width=600&inlineId=hiddenModalContent&modal=false\" class=\"thickbox\" href=\"pop_audit_xforms.php\"><img src=\"../../assets/btns/btn_at.jpg\" border=\"0\" align=\"absmiddle\"></a>";
								}    
								
                            }
                        
                        
                        //date & time 
                        if ($fet_formdata['wfprocdtype_idwfprocdtype']==7) 
                            {
							
							if (
								($fet_formdata['perm_write']==0) 
								|| ($fet_task['wftskstatustypes_idwftskstatustypes']==1) 
								||  ($fet_task['wftskstatustypes_idwftskstatustypes']==4) 
								||  (($fet_task['usrac_idusrac']!=$_SESSION['WkAtToMrPa_idacname']) && ($num_co==0) && ($num_delegated==0) )
								||  (($fet_task['usrrole_idusrrole']!=$_SESSION['WkAtToMrPa_iduserrole']) && ($num_co==0) && ($num_delegated==0) )
								)
                                {	
                                $readonly_off="";
								$readonly_click="";
								$readonly_style=" style=\"background-color:#f4f4f4\" ";
                                } else {
                                $readonly_off="<script language=\"javascript\">
                                $('#item_".$fet_formdata['idwfprocassetsaccess']."').datetimepicker({
                                        controlType: 'select',
                                        timeFormat: 'hh:mm tt',
                                        dateFormat: 'dd/mm/yy'
                                });
                                </script>";	
								$readonly_style="";
								$readonly_click=" onClick=\"datetimepicker('item_".$fet_formdata['idwfprocassetsaccess']."');\"";
                                }
							
                            echo "<input size=\"25\" ";
                            
                            if (isset($fet_data['value_choice']))
                                {
                                echo " value=\"".$fet_data['value_choice']."\" ";
                                } else {
                                echo " value=\"\" ";
                                }
                            
                            //show if required value missing
                            if ( (isset($_POST['required_'.$fet_formdata['idwfprocassetsaccess'].''])) && ($_POST['required_'.$fet_formdata['idwfprocassetsaccess'].'']==1) &&  ($_POST['item_'.$fet_formdata['idwfprocassetsaccess'].'']=="") )
                                    {
                                    echo " style=\"border:1px solid #ff0000\" ";
                                    }
                                    
                            echo  $readonly_click." ".$readonly_style." name=\"item_".$fet_formdata['idwfprocassetsaccess']."\" type=\"text\" id=\"item_".$fet_formdata['idwfprocassetsaccess']."\" readonly=\"readonly\" >" ;
                                
                                echo $readonly_off;	

                            echo "<input type=\"hidden\" name=\"required_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$fet_formdata['perm_required']."\">\r\n
                                <input type=\"hidden\" name=\"transtype_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$transaction."\" > \r\n
                                <input type=\"hidden\" name=\"itemtype_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$fet_formdata['wfprocdtype_idwfprocdtype']."\">
                                <input type=\"hidden\" name=\"itempk_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$idassetdata."\">
                                ";	
                              //audit trail check for this value
							$res_auditcheck=mysql_query('SELECT idwfassetsdata FROM audit_wfassetsdata WHERE idwfassetsdata='.$idassetdata.' LIMIT 1');
							$num_auditcheck=mysql_num_rows($res_auditcheck);
							if ($num_auditcheck>0)
								{
								echo "<a title=\"Audit Trail\" href=\"pop_audit_xforms.php?fvid=".$idassetdata."&amp;&amp;tabview=1&amp;tabview=1&keepThis=true&TB_iframe=true&height=300&width=600&inlineId=hiddenModalContent&modal=false\" class=\"thickbox\" href=\"pop_audit_xforms.php\"><img src=\"../../assets/btns/btn_at.jpg\" border=\"0\" align=\"absmiddle\"></a>";
								} 
							            
                            }
                        
                        
                         //this is a text box (numbers only)
                         if ($fet_formdata['wfprocdtype_idwfprocdtype']==8) 
                            {
                            //check permissions
                        //	echo $fet_formdata['perm_write'];
                           if (
						   		($fet_formdata['perm_write']==0) 
								|| ($fet_task['wftskstatustypes_idwftskstatustypes']==1) 
								||  ($fet_task['wftskstatustypes_idwftskstatustypes']==4) 
								||  (($fet_task['usrac_idusrac']!=$_SESSION['WkAtToMrPa_idacname']) && ($num_co==0) && ($num_delegated==0) )
								||  (($fet_task['usrrole_idusrrole']!=$_SESSION['WkAtToMrPa_iduserrole']) && ($num_co==0) && ($num_delegated==0) )
								) 
                                {	
                                $readonly="readonly=\"readonly\" style=\"background-color:#f4f4f4\" ";
                                } else {
                                $readonly="";
                                }											
                            
							if ((isset($fet_calc['result_id'])) && ($fet_calc['result_id']>0) )
								{
								$function_calc="calculate_".$fet_calc['result_id']."();";
								} else {
								$function_calc="";
								}
							
                            echo "<input onKeyUp=\"res(this,numb);".$function_calc."\" type=\"text\" ".$readonly." ";
                            
                            if (isset($_POST['item_'.$fet_formdata['idwfprocassetsaccess'].'']))
                                {
                                echo " value=\"".$_POST['item_'.$fet_formdata['idwfprocassetsaccess'].'']."\" ";
                                } else {
                                echo " value=\"".$fet_data['value_choice']."\" ";
                                }
                                
                            //show if value is missing
                            if ( (isset($_POST['required_'.$fet_formdata['idwfprocassetsaccess'].''])) && ($_POST['required_'.$fet_formdata['idwfprocassetsaccess'].'']==1) &&  ($_POST['item_'.$fet_formdata['idwfprocassetsaccess'].'']=="") )
                                    {
                                    echo " style=\"border:1px solid #ff0000\" ";
                                    }	
                                
                            echo " name=\"item_".$fet_formdata['idwfprocassetsaccess']."\" maxlength=\"50\">";
                            echo "<input type=\"hidden\" name=\"required_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$fet_formdata['perm_required']."\">\r\n
                            <input type=\"hidden\" name=\"transtype_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$transaction."\" > \r\n
                            <input type=\"hidden\" name=\"itemtype_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$fet_formdata['wfprocdtype_idwfprocdtype']."\">
                            <input type=\"hidden\" name=\"itempk_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$idassetdata."\">
                            ";
							
							 //audit trail check for this value
							$res_auditcheck=mysql_query('SELECT idwfassetsdata FROM audit_wfassetsdata WHERE idwfassetsdata='.$idassetdata.' LIMIT 1');
							$num_auditcheck=mysql_num_rows($res_auditcheck);
							if ($num_auditcheck>0)
								{
								echo "<a title=\"Audit Trail\" href=\"pop_audit_xforms.php?fvid=".$idassetdata."&amp;&amp;tabview=1&amp;tabview=1&keepThis=true&TB_iframe=true&height=300&width=600&inlineId=hiddenModalContent&modal=false\" class=\"thickbox\" href=\"pop_audit_xforms.php\"><img src=\"../../assets/btns/btn_at.jpg\" border=\"0\" align=\"absmiddle\"></a>";
								}
								
                            }
                            
                            
                            //this are approvals values
                      if ($fet_formdata['wfprocdtype_idwfprocdtype']==9) 
					 	{
						//check permissions
					//	echo $fet_formdata['perm_write'];
						
						
						$sql_choicesapprovals="SELECT idwfprocdtype_approvals,wfprocdtype_approvalslbl FROM wfprocdtype_approvals ";
						$res_choicesapprovals=mysql_query($sql_choicesapprovals);
						$fet_choicesapprovals=mysql_fetch_array($res_choicesapprovals);
						//echo $sql_choices;	
						
						echo "<select name=\"item_".$fet_formdata['idwfprocassetsaccess']."\" ";
						
						//show if required value is missing
						if ( (isset($_POST['required_'.$fet_formdata['idwfprocassetsaccess'].''])) && ($_POST['required_'.$fet_formdata['idwfprocassetsaccess'].'']==1) &&  ($_POST['item_'.$fet_formdata['idwfprocassetsaccess'].'']=="") )
								{
								echo " style=\"border:1px solid #ff0000\" ";
								}
						
						echo " >";
						//get the select options data
						if (!isset($fet_data['value_choice']))
							{
							echo "<option value=\"\">---</option>";
							}
							
							do {
								
								echo "<option ";
									//select the default if there is a value
									if ( (isset($fet_data['idwfassetsdata'])) && ( ($fet_data['value_choice']==$fet_choicesapprovals['idwfprocdtype_approvals']) ) || ( (isset($_POST['item_'.$fet_formdata['idwfprocassetsaccess'].''])) && ($_POST['item_'.$fet_formdata['idwfprocassetsaccess'].'']==$fet_choicesapprovals['idwfprocdtype_approvals']) ) )
										{
										echo " selected=\"selected\" ";
									//	} else {
									//	echo " disabled=\"disabled\" ";
										}	
										
									if (($fet_data['value_choice']!=$fet_choicesapprovals['idwfprocdtype_approvals']) && ($fet_formdata['perm_write']==0) || ($fet_ticket['tktstatus_idtktstatus']>3)   ) //|| ($lock_values==1)
										{	
										echo "disabled=\"disabled\" ";
										} 
																		
								echo " value=\"".$fet_choicesapprovals['idwfprocdtype_approvals']."\">".$fet_choicesapprovals['wfprocdtype_approvalslbl']."</option>";
							} while ($fet_choicesapprovals=mysql_fetch_array($res_choicesapprovals));
						echo "</select>";						
						
						//if readonly is set, then place a hidden value
						/*if (strlen($readonly)>3)
							{
							echo "<input type=\"hidden\" name=\"item_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$fet_choicesapprovals['idwfprocassetschoice']."\">";
							}*/
						
						echo "<input type=\"hidden\" name=\"required_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$fet_formdata['perm_required']."\">\r\n
						<input type=\"hidden\" name=\"transtype_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$transaction."\" > \r\n
						<input type=\"hidden\" name=\"itemtype_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$fet_formdata['wfprocdtype_idwfprocdtype']."\">
						<input type=\"hidden\" name=\"itempk_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$idassetdata."\">
						";
						
						 //audit trail check for this value
							$res_auditcheck=mysql_query('SELECT idwfassetsdata FROM audit_wfassetsdata WHERE idwfassetsdata='.$idassetdata.' LIMIT 1');
							$num_auditcheck=mysql_num_rows($res_auditcheck);
							if ($num_auditcheck>0)
								{
								echo "<a title=\"Audit Trail\" href=\"pop_audit_xforms.php?fvid=".$idassetdata."&amp;&amp;tabview=1&amp;tabview=1&keepThis=true&TB_iframe=true&height=300&width=600&inlineId=hiddenModalContent&modal=false\" class=\"thickbox\" href=\"pop_audit_xforms.php\"><img src=\"../../assets/btns/btn_at.jpg\" border=\"0\" align=\"absmiddle\"></a>";
								}
						
						}
                        
						
						
						 //this is a text area
                         if ($fet_formdata['wfprocdtype_idwfprocdtype']==10) 
                            {
                            //check permissions
                        //	echo $fet_formdata['perm_write'];
                            if (
								($fet_formdata['perm_write']==0) 
								|| ($fet_task['wftskstatustypes_idwftskstatustypes']==1) 
								||  ($fet_task['wftskstatustypes_idwftskstatustypes']==4) 
								||  (($fet_task['usrac_idusrac']!=$_SESSION['WkAtToMrPa_idacname']) && ($num_co==0) && ($num_delegated==0) )
								||  (($fet_task['usrrole_idusrrole']!=$_SESSION['WkAtToMrPa_iduserrole']) && ($num_co==0) && ($num_delegated==0) )
								)
                                {	
                                $readonly="readonly=\"readonly\" style=\"background-color:#f4f4f4\" ";
                                } else {
                                $readonly="";
                                }
                            
                            echo "<textarea ".$readonly."  name=\"item_".$fet_formdata['idwfprocassetsaccess']."\" maxlength=\"450\" ";
                                //highlight document show if error
                            //	if (isset($error."_".$fet_formdata['idwfprocassetsaccess']))
                                if ( (isset($_POST['required_'.$fet_formdata['idwfprocassetsaccess'].''])) && ($_POST['required_'.$fet_formdata['idwfprocassetsaccess'].'']==1) &&  ($_POST['item_'.$fet_formdata['idwfprocassetsaccess'].'']=="") )
                                    {
                                    echo " style=\"border:1px solid #ff0000\" ";
                                    }
							echo ">";
							if (isset($_POST['item_'.$fet_formdata['idwfprocassetsaccess'].'']))
                                    {
                                    echo $_POST['item_'.$fet_formdata['idwfprocassetsaccess'].''];
                                    } else {
                                    echo $fet_data['value_choice'];
                                    }                   
                            echo "</textarea>";
                            
                            echo "<input type=\"hidden\" name=\"required_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$fet_formdata['perm_required']."\">\r\n
                            <input type=\"hidden\" name=\"transtype_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$transaction."\" > \r\n
                            <input type=\"hidden\" name=\"itemtype_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$fet_formdata['wfprocdtype_idwfprocdtype']."\">
                            <input type=\"hidden\" name=\"itempk_".$fet_formdata['idwfprocassetsaccess']."\" value=\"".$idassetdata."\">
                            ";
                            
							 //audit trail check for this value
							$res_auditcheck=mysql_query('SELECT idwfassetsdata FROM audit_wfassetsdata WHERE idwfassetsdata='.$idassetdata.' LIMIT 1');
							$num_auditcheck=mysql_num_rows($res_auditcheck);
							if ($num_auditcheck>0)
								{
								echo "<a href=\"pop_audit_xforms.php?fvid=".$idassetdata."&amp;&amp;tabview=1&amp;tabview=1&keepThis=true&TB_iframe=true&height=300&width=600&inlineId=hiddenModalContent&modal=false\" class=\"thickbox\" href=\"pop_audit_xforms.php\"><img src=\"../../assets/btns/btn_at.jpg\" border=\"0\" align=\"absmiddle\"></a>";
								}
							
							}
						
						
						
                        ?>
                      </td>
                      </tr>
                      </table>
                
             		<?php			
				 	} while ($fet_formdata=mysql_fetch_array($res_formdata));
				} //[001] close if num > 0
			//close after checking
				?>
                </td>
            </tr>
            <?php
if ( (isset($_SESSION['delegated'])) && ($_SESSION['delegated']==0)) 
	{ ?>
    <tr>
        <td width="100%">
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#E4E4E4">
                <tr>
                    <td width="100%" class="tbl_data" colspan="2">
                    <div><span style="font-size:11px; font-weight:bold;color:#990000; background-color:#E4E4E4">Appropriate Action</span></div>
                    <div style="width:100%; font-size:12px">
                    <?php			
                        if (($fet_task['wftskstatustypes_idwftskstatustypes']==1) || ($fet_task['wftskstatustypes_idwftskstatustypes']==4)) //if task is closed then halt
                            {
                            echo "<strong>".$lbl_closedtask."</strong>";
                            } else {
                            
                            //ensure first that this task it not an exception before running the query checks below
                            //if  ( ($fet_task['sender_idusrrole']!=2) && ($fet_task['wftskflow_idwftskflow']>0) ) //if it is not sent by a customer, then
                            if ( ($fet_task['sender_idusrrole']!=2) && ($fet_task['wftskflow_idwftskflow']!=0) ) //if $fet_task['sender_idusrrole']!=2
                                    {
                                    
                                    //creation of the menu if there are valid permissions at THIS STEP
                                    $sql_listactions="SELECT wftskstatustype,idwftskstatustypes,wftskstatustypedesc,idwftskstatus FROM wftskstatustypes
                                    INNER JOIN wftskstatus ON wftskstatustypes.idwftskstatustypes=wftskstatus.wftskstatustypes_idwftskstatustypes 
                                    WHERE wftskflow_idwftskflow=".$fet_task['wftskflow_idwftskflow']." AND idwftskstatustypes!=8 AND is_visible=1 ORDER BY wftskstatustypes.listorder ASC";
                                    $res_listactions=mysql_query($sql_listactions);
                                    $fet_listactions=mysql_fetch_array($res_listactions);
                                    $num_listactions=mysql_num_rows($res_listactions);
                                    //echo "<span style=\"color:#ffffff\">1</span>";
        //							echo $sql_listactions;
                                    //check the last place this task is and which role has that task
                                    //check the previous task - if it was a Request For Transfer RFT
                                    $sql_rft="SELECT wftskstatustypes_idwftskstatustypes FROM wftasks WHERE idwftasks=".$fet_task['wftasks_idwftasks']." AND wftskstatustypes_idwftskstatustypes=5 LIMIT 1";
                                    $res_rft=mysql_query($sql_rft);
                                    $num_rft=mysql_num_rows($res_rft);
                                    
                                    if ($num_rft > 0 )	//if RFT is 5, then go ahead and create menu for Transfer 
                                        {
                                        $sql_transmenu="SELECT idwftskstatustypes,wftskstatustype FROM wftskstatustypes WHERE idwftskstatustypes=3";
                                        $res_transmenu=mysql_query($sql_transmenu);
                                        $fet_transmenu=mysql_fetch_array($res_transmenu);
                                        
                                        $transfer_menu="<option value=".$fet_transmenu['idwftskstatustypes'].">".$fet_transmenu['wftskstatustype']."</option>";
                                        
                                        } else {
                                        $transfer_menu="";
                                        }
                                        
                                    if (isset($fet_task['listorder']))
                                        {
                                        //transfer back to sender checker
                                        //check if this task should be allowed
                                        $sql_returnto="SELECT wftasks.usrrole_idusrrole,wftasks.wftskflow_idwftskflow,wftskflow.listorder,wftasks.sender_idusrrole,utitle,usrrole.usrrolename,fname,lname FROM wftasks 
                                        INNER JOIN wftskflow ON wftasks.wftskflow_idwftskflow=wftskflow.idwftskflow
                                        INNER JOIN usrrole ON wftasks.usrrole_idusrrole=usrrole.idusrrole
                                        INNER JOIN usrac ON usrrole.idusrrole=usrac.usrrole_idusrrole
                                        WHERE wftskflow.listorder < '".$fet_task['listorder']."'
                                        AND wftskflow.wfproc_idwfproc=".$fet_task['wfproc_idwfproc']."  
                                        AND wftasks.wftaskstrac_idwftaskstrac=".$fet_task['wftaskstrac_idwftaskstrac']." 
                                        AND ((wftasks.wftskstatustypes_idwftskstatustypes=0) OR (wftasks.wftskstatustypes_idwftskstatustypes=2)) 
                                        ORDER BY wftskflow.listorder DESC LIMIT 1";
                                        $res_returnto=mysql_query($sql_returnto);
                                        $fet_returnto=mysql_fetch_array($res_returnto);
                                        $num_returnto=mysql_num_rows($res_returnto);
                                        }
                                        
                            
                                    } else { //else if it is an exception, then just create some default menus from the system
                                
                                    $sql_listactions="SELECT wftskstatustype,idwftskstatustypes,wftskstatustypedesc FROM wftskstatustypes
                                    WHERE (idwftskstatustypes=1 OR idwftskstatustypes=2 OR idwftskstatustypes=4 OR idwftskstatustypes=6 OR idwftskstatustypes=9) AND is_visible=1 ORDER BY wftskstatustypes.listorder ASC";
                                    $res_listactions=mysql_query($sql_listactions);
                                    $fet_listactions=mysql_fetch_array($res_listactions);
                                    $num_listactions=mysql_num_rows($res_listactions);
                                    //echo "<span style=\"color:#ffffff\">2</span>";
                                    //where is the task at	
                                    /*$sql_taskat="SELECT usrrole_idusrrole,wftaskstrac_idwftaskstrac FROM wftasks WHERE wftaskstrac_idwftaskstrac=".$fet_task['wftaskstrac_idwftaskstrac']." ORDER BY idwftasks DESC LIMIT 1";
                                    $res_taskat=mysql_query($sql_taskat);
                                    $fet_taskat=mysql_fetch_array($res_taskat);*/
                                    //echo $sql_taskat."<br>";
                                    }
        
                                //construct the ACTION DROP DOWN MENU condition		
                                /*if ( $num_delegated > 0 )
                                    {
                                    $qry_recepient = " ( (".intval($fet_task['usrrole_idusrrole']==$_SESSION['WkAtToMrPa_iduserrole']).") || (".intval($fet_task['usrrole_idusrrole']==$fet_delegated['idusrrole_from']).") ) ";
                                    $delegated_to_me = 1;
                                    } else {
                                    $qry_recepient = " (".$fet_task['usrrole_idusrrole']==$_SESSION['WkAtToMrPa_iduserrole'].") ";
                                    $delegated_to_me = 0;
                                    }	
                                */	
                                //if (($num_listactions > 0) && $qry_recepient) //$qry_recepient has been constructed at the top of this document
                                //echo "<span style=\"color:#ffffff\">if (".$num_listactions.">0) && (".$fet_taskat['usrrole_idusrrole']."==".$_SESSION['WkAtToMrPa_iduserrole'].") || (".$fet_task['usrrole_idusrrole']."==".$fet_delegated['idusrrole_from'].")</span><br>";
                                
            /*					echo "if (
                                    (".$num_listactions." > 0) && 
                                    ((".$fet_taskat['usrrole_idusrrole']."==".$_SESSION['WkAtToMrPa_iduserrole'].") || 
                                    (".$fet_task['usrrole_idusrrole']."==".$fet_delegated['idusrrole_from'].") || 
                                    ((isset(".$is_group_task.")) && (".$is_group_task." > 0) ) ) 
                                    )";
            */
                            
                            if (
                                ($num_listactions > 0) && 
                                (  ($fet_taskat['usrrole_idusrrole']==$_SESSION['WkAtToMrPa_iduserrole']) 
                                || ($fet_task['usrrole_idusrrole']==$fet_delegated['idusrrole_from']) 
                                || ((isset($is_group_task)) && ($is_group_task > 0))  ) 
                                || ( ($num_co>0 ) && ($fet_task['usrrole_idusrrole']==$fet_co['idusrrole']) )
                                )											
                                {	
                                ?>
                                <select name="action_to" style="width:90%" class="switchaction" id="action_msg">
                                <option value="0">---</option>
                            <?php do { ?>
                                <option <?php if ( ($fet_listactions['idwftskstatustypes']==8) || ($fet_listactions['idwftskstatustypes']==9) ) { echo "disabled=\"disabled\""; } // disable Assign(8) and RTS(9) ?> value="<?php echo $fet_listactions['idwftskstatustypes'];?>"><?php echo $fet_listactions['wftskstatustype'];?></option>
                            <?php } while ($fet_listactions=mysql_fetch_array($res_listactions)) ;?>
                            
                            <?php if (isset($transfer_menu)) { echo $transfer_menu; } //show the extra Transfer menu for RFT tickets if set
                                    if ( (isset($num_returnto)) && ($num_returnto> 0)  )
                                        {
                                ?>
                                <option <?php //if sender is customer, then u cannot return to customer
                                /* if ( ($fet_returnto['usrrole_idusrrole']==$_SESSION['WkAtToMrPa_iduserrole']) || ($fet_returnto['usrrole_idusrrole']==2)  ) { echo "disabled=\"disabled\""; }*/ echo "disabled=\"disabled\"";  ?> value="9">** Return to Sender! ** </option>
                                    <?php
                                        }
                                    ?>
                            </select>
                                <?php } else {
                                $sql_itswith="SELECT usrrolename,utitle,lname FROM usrrole 
                                INNER JOIN usrac ON usrrole.idusrrole=usrac.usrrole_idusrrole
                                WHERE usrrole.idusrrole=".$fet_taskat['usrrole_idusrrole']." ";
                                //echo $sql_itswith;
                                $res_itswith=mysql_query($sql_itswith);
                                $fet_itswith=mysql_fetch_array($res_itswith);
                                //echo $sql_itswith;
                                echo "<small>Currently assigned to:</small><br><strong> ".$fet_itswith['usrrolename']." (".$fet_itswith['utitle']." ".$fet_itswith['lname'].")</strong>";
                                 } 
                                  
                            }  //close if task is closed
                            
                        /*	echo 
                            "(".$num_listactions." > 0) && 
                                (  (".$fet_taskat['usrrole_idusrrole']."==".$_SESSION['WkAtToMrPa_iduserrole'].") 
                                || (".$fet_task['usrrole_idusrrole']."==".$fet_delegated['idusrrole_from'].") 
                                || ((isset(".$is_group_task.")) && (".$is_group_task." > 0))  ) 
                                )";
                                echo "<br>".$fet_task['usrrole_idusrrole']."---".$fet_task['usrac_idusrac'];
                        */	
                        ?>
                   		</div>     
                  </td>
                </tr>
      		</table>
  		</td>
	</tr> 
<?php
} else { //else if delegated out, give this message
?>
<tr>
	<td>
	<div class="msg_warning_small">
	You cannot action on this tasks because you have delegated all your Tasks out to <?php echo $_SESSION['delegated_to'];?></div>
	</td>
</tr>
<?php
} //close delegated OUT
?>
<tr>
    <td>    
    <!-- start the options-->
	
    <div class="scroll-content">
	<div class="actionlist 1" style="margin:0; padding:0">
    <table border="0" width="100%" cellpadding="0" cellspacing="0" >
    	<tr>
            <td width="100%" valign="top" class="tbl_data" colspan="2" >
                <div><span style="font-size:11px; font-weight:bold;color:#990000; background-color:#FFFFFF"><?php echo $lbl_youraction_msg; ?></span></div>
                <div style="width:100%; font-size:12px">
	            	<!--<textarea cols="25" rows="4" name="task_msg_1"><?php //if (isset($tkttskmsg1)) { echo $tkttskmsg1; }?></textarea>-->
                    <input style="width:90%" type="text" name="task_msg_1" id="task_msg_1" value="<?php if (isset($tkttskmsg1)) { echo $tkttskmsg1; }?>" />
                </div>    
            </td>
		</tr>
        <tr>
            <td class="tbl_data" colspan="2">
            <div><label for="1"><input type="hidden" value="1" name="close_1" id="1" /> <?php echo $lbl_confirmtktclose;?></label></div>
            </td>
        </tr>
        <tr>
            <td height="50" style="padding:0px 50px" colspan="2">
            <input type="hidden" value="process_task" name="formaction" />
    		<!--<a href="#" class="button_small" onClick="document.forms['task'].submit()" >Close Task &amp; Ticket</a>-->
            <a onclick="document.forms['task'].submit()" href="#"><input type="submit" class="button_small" id="button_passiton" value="Close Task &amp; Ticket" /></a>
            </td>
        </tr>
	</table>
	</div>
	
	<div class="actionlist 2" style="margin:0; padding:0">
    <table border="0" width="100%" cellpadding="0" cellspacing="0" >
    	<tr>
            <td width="100%" valign="top" class="tbl_data" colspan="2" >
                <div><span style="font-size:11px; font-weight:bold;color:#990000; background-color:#FFFFFF"><?php echo $lbl_youraction_msg; ?></span></div>
                <div style="width:100%; font-size:12px">
	            	<!--<textarea cols="35" rows="2" class="tbox" name="task_msg_2" style="width:30%"><?php //if (isset($tkttskmsg2)) { echo $tkttskmsg2; }?></textarea>-->
                    <input style="width:90%" type="text" name="task_msg_2" id="task_msg_2" value="<?php if (isset($tkttskmsg2)) { echo $tkttskmsg2; }?>" />
                </div>    
            </td>
		</tr>
        <?php
		//if the current task is an exception, then show the milestones option avaiable on this workflow
		if ($fet_task['wftskflow_idwftskflow']==0)
			{
			//get the milestones in this workflow from the system
			$sql_ms="SELECT idwftskflow,wftskflowname FROM wftskflow WHERE wfproc_idwfproc=".$fet_wfproc['wfproc_idwfproc']." AND is_milestone=1 ORDER BY wftskflowname ASC";
			$res_ms=mysql_query($sql_ms);
			$num_ms=mysql_num_rows($res_ms);
			$fet_ms=mysql_fetch_array($res_ms);
				
			//is next step variable
			$is_nextstep=1;
			?>
		<tr>
        	<td class="tbl_data" colspan="2">
            	<div><span style="font-size:11px; font-weight:bold;color:#990000; background-color:#FFFFFF"><?php echo $lbl_asterik;?><strong>Next STEP</strong></span></div>
                <div style="width:100%; font-size:12px">
                    <select name="next_milestone" onChange="nextstep(this.value)" id="next_milestone" style="width:90%">
                        <option value="">---</option>
                        <option value="other_exception" style="background-color:#FFFFCC;color:#CC0000;font-weight:bold">*** Not Listed / I Don't Know ***</option>
                        <?php
                        if ($num_ms > 0)
                            {
                            $milestone_avail=1;
                            do {
                            echo "<option value=\"".$fet_ms['idwftskflow']."\">".$fet_ms['wftskflowname']."</option>";
                            } while ($fet_ms=mysql_fetch_array($res_ms));
                        } //close if is_milestone=1
                        ?>
                    </select>            
             	</div>  		     
           	</td>
        </tr>
		<?php	
			} //close if ==0
		?>
        <tr>
        	<td></td>
            <td style="margin:0px; padding:5px 4px;"><div id="nextstepdiv"></div></td>
        </tr>
        <?php	
		if ($fet_task['wftskflow_idwftskflow']!=0)//if it is not an exception, then show the list be [0887]
			{
		?>
        <tr>
        	<td class="tbl_data" width="100%" colspan="2">
                <div><span style="font-size:11px; font-weight:bold;color:#990000; background-color:#FFFFFF"><?php echo "Send To";?></span></div>
                <div style="width:100%; font-size:12px">
<?php
	//check the next VALID taskflow after this one
	/*
	$sql_nextwf="SELECT idwftskflow,wfsymbol_idwfsymbol,wfproc_idwfproc,limit_to_zone,limit_to_dpt 
	FROM wftskflow 
	WHERE 
	wfproc_idwfproc=".$fet_task['wfproc_idwfproc']." AND 
	listorder>".$fet_task['listorder']." 
	ORDER BY listorder ASC LIMIT 1";
					
	//first, check the related role or group so that we can filter by the roles Regions or Department if it applies
	*/					
	
	//WE NEED TO CHECK FOR A VALID NEXT WORKFLOW 
						
			//CHECK BASED ON ROLE
			$sql_checkrole="SELECT idwftskflow,wfsymbol_idwfsymbol,wfproc_idwfproc,limit_to_zone,limit_to_dpt 
			FROM wftskflow 
			INNER JOIN wfactors ON wftskflow.idwftskflow=wfactors.wftskflow_idwftskflow
			INNER JOIN usrrole ON wfactors.usrrole_idusrrole=usrrole.idusrrole
			INNER JOIN usrac ON usrrole.idusrrole=usrac.usrrole_idusrrole
			WHERE ( 
			(wftskflow.limit_to_zone=0 AND wftskflow.limit_to_dpt=0) OR 
			(wftskflow.limit_to_zone=1 AND usrrole.usrteamzone_idusrteamzone=".$_SESSION['WkAtToMrPa_userteamzoneid']." AND wftskflow.limit_to_dpt=0) OR 
			(wftskflow.limit_to_zone=0 AND wftskflow.limit_to_dpt=".$_SESSION['WkAtToMrPa_usrdpts']." AND wftskflow.limit_to_dpt=0) OR 
			(wftskflow.limit_to_zone=".$_SESSION['WkAtToMrPa_userteamzoneid']." AND wftskflow.limit_to_dpt=".$_SESSION['WkAtToMrPa_usrdpts'].") 
			)
			AND 
			wfactors.usrrole_idusrrole>0 AND
			wftskflow.wfproc_idwfproc=".$fet_task['wfproc_idwfproc']." AND 
			wftskflow.listorder>".$fet_task['listorder']." AND
			usrac.acstatus=1
			ORDER BY wftskflow.listorder ASC LIMIT 1";
			$res_checkrole=mysql_query($sql_checkrole);	
			$fet_checkrole=mysql_fetch_array($res_checkrole);
			
	//		echo $sql_checkrole."<br>";
			
			//CHECK BASED ON GROUP
			$sql_checkroleg="SELECT idwftskflow,wfsymbol_idwfsymbol,wfproc_idwfproc,limit_to_zone,limit_to_dpt 
			FROM wftskflow 
			INNER JOIN wfactors ON wftskflow.idwftskflow=wfactors.wftskflow_idwftskflow
			INNER JOIN link_userrole_usergroup ON wfactors.usrgroup_idusrgroup=link_userrole_usergroup.usrgroup_idusrgroup 	
			INNER JOIN usrrole ON link_userrole_usergroup.usrrole_idusrrole=usrrole.idusrrole
			INNER JOIN usrac ON usrrole.idusrrole=usrac.usrrole_idusrrole
			WHERE ( 
			(wftskflow.limit_to_zone=0 AND wftskflow.limit_to_dpt=0) OR 
			(wftskflow.limit_to_zone=1 AND usrrole.usrteamzone_idusrteamzone=".$_SESSION['WkAtToMrPa_userteamzoneid']." AND wftskflow.limit_to_dpt=0) OR 
			(wftskflow.limit_to_zone=0 AND wftskflow.limit_to_dpt=".$_SESSION['WkAtToMrPa_usrdpts']." AND wftskflow.limit_to_dpt=0) OR 
			(wftskflow.limit_to_zone=".$_SESSION['WkAtToMrPa_userteamzoneid']." AND wftskflow.limit_to_dpt=".$_SESSION['WkAtToMrPa_usrdpts'].") 
			)
			AND
			wfactors.usrgroup_idusrgroup>0 AND
			wftskflow.wfproc_idwfproc=".$fet_task['wfproc_idwfproc']." AND 
			wftskflow.listorder>".$fet_task['listorder']." AND
			usrac.acstatus=1
			ORDER BY wftskflow.listorder ASC LIMIT 1";
			$res_checkroleg=mysql_query($sql_checkroleg);	
			$fet_checkroleg=mysql_fetch_array($res_checkroleg);
			
				//		echo $sql_checkroleg;
				
				//WHAT IF ITS IS THE END OF THE WORKFLOW AND HENCE THE RESULTS ABOVE RETURN A NIL RESULT -
				//THEN IN THAT CASE, MAKE IT AN EXCEPTION AND SHOW THE MENU FOR OTHER :)
				
			if (($fet_checkrole['idwftskflow'] < 1) && ($fet_checkroleg['idwftskflow'] < 1)  ) //if condition MAINEMAINE
				{
				
				echo "<input type=\"hidden\" name=\"wftaskflow_id\" value=\"0\">"; //by default, the next wkflow id is zero because wer are moving out of the workflow again.
				echo "<select style=\"width:90%\" name=\"assign_to_2\" id=\"assign_to_2\" onchange=\"showstuff(this.value);\">";
				echo "<option value=\"\">---Select Here---</option>";
				echo "<option disabled=\"disabled\">-----------------</option>";	
				echo "<option style=\"color:#ff0000;font-weight:bold;background-color:#ffffcc\" title=\"If Recepient is not Listed, then make this an Exception\" value=\"other_exception\">[ Not Listed Above ]</option>";
				echo "</select>";	
				
				} else {			
				
						
						//take the least - else if the same, take whichever idtskwkflow
						if ($fet_checkrole['idwftskflow'] > 0)
							{
							$tskflowid_role = $fet_checkrole['idwftskflow'];							
							}
							
						if ($fet_checkroleg['idwftskflow'] > 0)
							{							
							$tskflowid_grp = $fet_checkroleg['idwftskflow'];
							}
							
						//pick the lower number
						if ( (isset($tskflowid_role)) && (isset($tskflowid_grp)) )
							{
								if ($tskflowid_role < $tskflowid_grp)
									{
									$next_tskflowid=$tskflowid_role;
									} else if ($tskflowid_role > $tskflowid_grp) {
									$next_tskflowid=$tskflowid_grp;
									} else {
									$next_tskflowid=$tskflowid_role;
									}
									
							} else if ( (isset($tskflowid_role)) && (!isset($tskflowid_grp)) ) {
								
								$next_tskflowid=$tskflowid_role;	
														
							} else if ( (!isset($tskflowid_role)) && (isset($tskflowid_grp)) ) {
								
								$next_tskflowid=$tskflowid_grp;	
								
							} else {
								
								$next_tskflowid=0;	
								
							}
							
						
							$sql_nextwf="SELECT idwftskflow,wfsymbol_idwfsymbol,wfproc_idwfproc,limit_to_zone,limit_to_dpt 
							FROM wftskflow 
							WHERE idwftskflow=".$next_tskflowid." ";
							$res_nextwf=mysql_query($sql_nextwf);
							$fet_nextwf=mysql_fetch_array($res_nextwf);
							$num_nextwf=mysql_num_rows($res_nextwf);
							//echo $sql_nextwf."<br>";
							if ($num_nextwf > 0)//if there is a record
								{ 
								
								//check if it is limit region
								if ($fet_nextwf['limit_to_zone']==1)
									{
									//limit this users region
									$limit_to_zone_qry1=" AND usrrole.usrteamzone_idusrteamzone=".$_SESSION['WkAtToMrPa_userteamzoneid']." ";
									} else {
									$limit_to_zone_qry1="";
									}
									
								//check if it is limit region
								if ($fet_nextwf['limit_to_dpt']==1)
									{
									//limit this user to their department
									$limit_to_dpt_qry1=" AND usrrole.usrdpts_idusrdpts=".$_SESSION['WkAtToMrPa_usrdpts']." ";
									} else {
									$limit_to_dpt_qry1="";
									}
								
								if ($fet_nextwf['wfsymbol_idwfsymbol']==10) //if it is the end of the process
									{
									
									$next_step="last_step";
									
									} else { //else if not end of the process, continue
									
										//confirm whether the actors are a group or individual role
										$sql_actors="SELECT usrrole_idusrrole,usrgroup_idusrgroup FROM wfactors WHERE wftskflow_idwftskflow=".$fet_nextwf['idwftskflow']." LIMIT 1 ";
										$res_actors=mysql_query($sql_actors);
										$fet_actors=mysql_fetch_array($res_actors);
										$num_actors=mysql_num_rows($res_actors);
										//echo $sql_actors;
										if ($fet_actors['usrrole_idusrrole'] >0 ) //if more than 0, then it is a allocated to a role
											{
											//find out the actual account assigned this role
											$sql_userac="SELECT idusrac,usrrolename,idusrrole,usrac.utitle,usrac.lname,usrac.fname,usrteamzone.region_pref FROM wfactors
											INNER JOIN usrrole ON wfactors.usrrole_idusrrole=usrrole.idusrrole
											INNER JOIN usrac ON usrrole.idusrrole=usrac.usrrole_idusrrole
											INNER JOIN usrteamzone ON usrrole.usrteamzone_idusrteamzone=usrteamzone.idusrteamzone
											WHERE wfactors.wftskflow_idwftskflow=".$fet_nextwf['idwftskflow']." AND usrac.usrteam_idusrteam=".$_SESSION['WkAtToMrPa_idacteam']." ".$limit_to_zone_qry1." ".$limit_to_dpt_qry1." AND acstatus=1 ORDER BY usrteamzone.idusrteamzone";
											$res_userac=mysql_query($sql_userac);
											$fet_userac=mysql_fetch_array($res_userac);
											$num_userac=mysql_num_rows($res_userac);
										//	echo "<span style=color:#ffffff>".$sql_userac."</spa<br>";
	
											if ($num_userac > 0)
												{
												
												$menu_item="";
												$menu_exvalues="";
													do {
														if ($fet_userac['idusrrole']!=$_SESSION['WkAtToMrPa_iduserrole']) //list only if not current user
															{
															$menu_item.="<option title=\"".$fet_userac['utitle']." ".$fet_userac['fname']." ".$fet_userac['lname']."\" value=\"".$fet_userac['idusrrole']."\">[".$fet_userac['region_pref']."] ".$fet_userac['usrrolename']." ( ".$fet_userac['utitle']." ".$fet_userac['fname']." ".$fet_userac['lname'].")</option>";
															} else {
															$menu_item.="<option title=\"".$fet_userac['utitle']." ".$fet_userac['fname']." ".$fet_userac['lname']."\" value=\"".$fet_userac['idusrrole']."\">*** [ To My TasksIN ]</option>";
															} //end //list only if not current user
														
														//create the exvalues
														$menu_exvalues.= "AND idusrrole!=".$fet_userac['idusrrole']." ";
														
														} while ($fet_userac=mysql_fetch_array($res_userac));
										
												} else {
												
												echo "<div class=\"msg_warning_small\">--No Active User--<br><small>(Please Contact Admin)</small></div>";
												
												} //user exists
											} //close usrrole
									
										if ($fet_actors['usrgroup_idusrgroup'] > 0 ) //if allocated to a group, then do the following
											{ 
											//if group, check only those roles that do actually have users (active status) mapped to them
											//check who has had most work in the last 7 days (one week) in terms of hours
											//last 7 days
											//$timenow = ; //capture current time. You can adjust based on server settings
											$sevendaysago = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s",time())) - (7*86400)); //7 days ago
											
											//echo $sevendaysago."<br>-----";
												
											$sql_workdistr="SELECT SUM(TIMESTAMPDIFF(MINUTE,timetatstart,timedeadline)) AS minutes, usrac.idusrac, usrac.usrrole_idusrrole,usrac.utitle,usrac.lname,usrrole.usrrolename,usrteamzone.region_pref FROM wftasks 
											INNER JOIN usrrole ON wftasks.usrrole_idusrrole=usrrole.idusrrole 
											INNER JOIN link_userrole_usergroup ON usrrole.idusrrole = link_userrole_usergroup.usrrole_idusrrole
											INNER JOIN usrac ON link_userrole_usergroup.usrrole_idusrrole = usrac.usrrole_idusrrole
											INNER JOIN usrteamzone ON usrrole.usrteamzone_idusrteamzone=usrteamzone.idusrteamzone
											WHERE wftasks.createdon>='".$sevendaysago."' AND wftasks.createdon<='".$timenowis."' ".$limit_to_zone_qry1." ".$limit_to_dpt_qry1." AND usrac.usrteam_idusrteam=".$_SESSION['WkAtToMrPa_idacteam']." AND link_userrole_usergroup.usrgroup_idusrgroup=".$fet_actors['usrgroup_idusrgroup']." AND acstatus=1 GROUP BY wftasks.usrac_idusrac ORDER BY minutes ASC";
											//echo "test";
											//echo $sql_workdistr."<br>";
											$res_workdistr=mysql_query($sql_workdistr);
											$num_workdistr=mysql_num_rows($res_workdistr);
											$fet_workdistr=mysql_fetch_array($res_workdistr);
												
												
											//check in case the group has not received anything in the last 7 days
											$sql_workdistolder7="SELECT SUM(TIMESTAMPDIFF(MINUTE,timetatstart,timedeadline)) AS minutes, usrac.idusrac, usrac.usrrole_idusrrole,usrac.utitle,usrac.lname,usrac.fname,usrrole.usrrolename,usrteamzone.region_pref FROM wftasks 
											INNER JOIN usrrole ON wftasks.usrrole_idusrrole=usrrole.idusrrole 
											INNER JOIN link_userrole_usergroup ON usrrole.idusrrole = link_userrole_usergroup.usrrole_idusrrole
											INNER JOIN usrac ON link_userrole_usergroup.usrrole_idusrrole = usrac.usrrole_idusrrole
											INNER JOIN usrteamzone ON usrrole.usrteamzone_idusrteamzone=usrteamzone.idusrteamzone
											WHERE wftasks.createdon<='".$timenowis."' AND link_userrole_usergroup.usrgroup_idusrgroup=".$fet_actors['usrgroup_idusrgroup']." AND usrac.usrteam_idusrteam=".$_SESSION['WkAtToMrPa_idacteam']." ".$limit_to_zone_qry1." ".$limit_to_dpt_qry1." AND acstatus=1 GROUP BY wftasks.usrac_idusrac ORDER BY minutes ASC";
											//echo "test";
											//echo $sql_workdistolder7."<br>";
											$res_workdistolder7=mysql_query($sql_workdistolder7);
											$num_workdistolder7=mysql_num_rows($res_workdistolder7);
											$fet_workdistolder7=mysql_fetch_array($res_workdistolder7);	
											
												
											//check also for any new user who perhaps has never received a task - new user
											$sql_newuser="SELECT idusrac, usrac.usrrole_idusrrole, usrrole.usrrolename,usrac.utitle,usrac.lname,usrac.fname,usrteamzone.region_pref
											FROM link_userrole_usergroup
											INNER JOIN usrrole ON link_userrole_usergroup.usrrole_idusrrole = usrrole.idusrrole
											INNER JOIN usrac ON usrrole.idusrrole=usrac.usrrole_idusrrole
											INNER JOIN usrteamzone ON usrrole.usrteamzone_idusrteamzone=usrteamzone.idusrteamzone
											WHERE link_userrole_usergroup.usrrole_idusrrole NOT
											IN (
											
											SELECT usrrole_idusrrole
											FROM wftasks
											)
											AND link_userrole_usergroup.usrgroup_idusrgroup=".$fet_actors['usrgroup_idusrgroup']." 
											".$limit_to_zone_qry1." ".$limit_to_dpt_qry1."
											AND acstatus=1";
											$res_newuser=mysql_query($sql_newuser);
											$num_newuser=mysql_num_rows($res_newuser);
											$fet_newuser=mysql_fetch_array($res_newuser);
									
								//	echo $sql_newuser;
											//if record exists, then pick 
											
											if ($num_newuser>0) //if there is a new user and user exists in the workflow
												{
												
												$menu_item3="";
												$menu_exvalues3="";
												
													do {
														if ($fet_newuser['usrrole_idusrrole']!=$_SESSION['WkAtToMrPa_iduserrole']) //list only if not current user
															{
															$menu_item3.="<option title=\"".$fet_newuser['utitle']."  ".$fet_newuser['fname']." ".$fet_newuser['lname']."\" value=\"".$fet_newuser['usrrole_idusrrole']."\">[".$fet_newuser['region_pref']."] ".$fet_newuser['usrrolename']." (".$fet_newuser['utitle']."  ".$fet_newuser['fname']." ".$fet_newuser['lname'].")</option>";
															} //end //list only if not current user
														
												//create the exvalues
												$menu_exvalues3.=" AND idusrrole!=".$fet_newuser['usrrole_idusrrole']." ";
														
														} while ($fet_newuser=mysql_fetch_array($res_newuser));	
												//$menu_item="<option selected=\"selected\" title=\"".$fet_newuser['utitle']." ".$fet_newuser['lname']."\" value=\"".$fet_newuser['usrrole_idusrrole']."\">".$fet_newuser['usrrolename']."[2]</option>";
													
													
												} else if ($num_newuser==0) { //else if no one is new 
													
														if ($num_workdistr > 0 ) //if there are already users in the tasks
															{
															$menu_item2="";
															$menu_exvalues2="";
																
																do {
																//don't list the current logged in user on the menu
																if ($fet_workdistr['usrrole_idusrrole']!=$_SESSION['WkAtToMrPa_iduserrole'])
																		{
																		$menu_item2.="<option title=\"".$fet_workdistr['utitle']." ".$fet_workdistr['fname']." ".$fet_workdistr['lname']."\"  value=\"".$fet_workdistr['usrrole_idusrrole']."\">[".$fet_workdistr['region_pref']."] ".$fet_workdistr['usrrolename']." (".$fet_workdistr['utitle']." ".$fet_workdistr['fname']." ".$fet_workdistr['lname'].")</option>";
																		}
																		
																//exvalue here	
																$menu_exvalues2.=" AND idusrrole!=".$fet_workdistr['usrrole_idusrrole']." ";	
																	
																	} while ($fet_workdistr=mysql_fetch_array($res_workdistr));
															
															} else { //else create a task for the admin
															
																//check if older than 7 days
																if ($num_workdistolder7 > 0)
																	{
																	$menu_item2="";
																	$menu_exvalues2="";
																	
																	do {
																		//don't list the current logged in user on the menu
																		if ($fet_workdistolder7['usrrole_idusrrole']!=$_SESSION['WkAtToMrPa_iduserrole'])
																				{
																				$menu_item2.="<option title=\"".$fet_workdistolder7['utitle']." ".$fet_workdistolder7['fname']." ".$fet_workdistolder7['lname']."\"  value=\"".$fet_workdistolder7['usrrole_idusrrole']."\">[".$fet_workdistolder7['region_pref']." ] ".$fet_workdistolder7['usrrolename']." (".$fet_workdistolder7['utitle']." ".$fet_workdistolder7['fname']." ".$fet_workdistolder7['lname'].")</option>";
																				}
																				
																			//exvalue here	
																			$menu_exvalues2.=" AND idusrrole!=".$fet_workdistolder7['usrrole_idusrrole']." ";	
																			
																			} while ($fet_workdistolder7=mysql_fetch_array($res_workdistolder7));
																	
																	} else {
																	//create new task for the admin
																	echo "<div class=\"msg_warning_small\">--No Active User--<br><small>(Please Contact Admin)</small></div>";
																	}
												
															} //user exists
													
													} //close new user
	
												} //close user group
	
									} //not last step
								
								} else { //if no record
								
								$next_step="end_of_road";
								} //close if there is a record

							
						if ( (isset($menu_item)) || (isset($menu_item2)) || (isset($menu_item3)) )
							{
							
						//let's create the exception list to carry over to the ajaxfile on the other end
						if (isset($menu_exvalues))
							{
							$menu_exvalues_vals=$menu_exvalues;
							} else {
							$menu_exvalues_vals="";
							}
						if (isset($menu_exvalues2))
							{
							$menu_exvalues2_vals=$menu_exvalues2;
							} else {
							$menu_exvalues2_vals="";
							}
						if (isset($menu_exvalues3))
							{
							$menu_exvalues3_vals=$menu_exvalues3;
							} else {
							$menu_exvalues3_vals="";
							}	
						
						
						$_SESSION['next_tskflowid']=$next_tskflowid; //store in a session and see if it appears the other file
						
						$_SESSION['exempt']=$menu_exvalues_vals.$menu_exvalues2_vals.$menu_exvalues3_vals; //excempt this ids from the excemption list
										
						
						echo "<input type=\"hidden\" name=\"wftaskflow_id\" value=\"".$next_tskflowid."\">";				
						echo "<select style=\"width:90%\" name=\"assign_to_2\" id=\"assign_to_2\" onchange=\"showstuff(this.value);\">";
						echo "<option value=\"\">---Select Here---</option>";
						
								if(isset($menu_item)) { echo $menu_item; }
								if(isset($menu_item2)) { echo $menu_item2; }
								if(isset($menu_item3)) { echo $menu_item3; }

								//find out if there is a group for this
								$sql_groupname="SELECT idwfactorsgroupname,groupname FROM wfactorsgroupname WHERE wftskflow_idwftskflow=".$fet_nextwf['idwftskflow']." LIMIT 1";
								$res_groupname=mysql_query($sql_groupname);
								$fet_groupname=mysql_fetch_array($res_groupname);						
										
							if ($fet_groupname['idwfactorsgroupname'] > 0)
								{	
								echo "<option title=\"Send to a Group but only one will Action\" value=\"GRP".$fet_groupname['idwfactorsgroupname']."\">[Group] ".$fet_groupname['groupname']."</option>";
								}
							echo "<option disabled=\"disabled\">-----------------</option>";	
							echo "<option style=\"color:#ff0000;font-weight:bold;background-color:#ffffcc\" title=\"If Recepient is not Listed, then make this an Exception\" value=\"other_exception\">[ Not Listed Above ]</option>";
							echo "</select>";	
								
							} //select menu
					
						} //close if condition MAINEMAINE
					
						?>            
                 	</div>       
              </td>
        </tr>
        <?php
			} //list this only if this is not an exeception [0887]
		?>
        <tr>
        	<td colspan="2">
            <?php
			//echo $is_nextstep."-";
			//echo $fet_task['sender_idusrrole']."-".$fet_task['wftskflow_idwftskflow'];
				//change the display from none to block if it is an exception
				if  ( ($fet_task['sender_idusrrole']!=2) && ($fet_task['wftskflow_idwftskflow']==0) && (!isset($is_nextstep))  ) //if exception
					 { 
					 $lbl_sendto="<strong>".$lbl_asterik.$lbl_sendto."</strong>";
					 $display="block";
					 
					 } else {
					 	
					 	$lbl_sendto="";
					 	$display="none";
					 }
				echo $lbl_sendto;
				?>           
        	</td>
      	</tr>      

           	<?php
			if ($fet_task['wftskflow_idwftskflow']!=0) { //if not an exception ?>
            <tr>    
            <td class="tbl_data" width="100%" colspan="2">
                <div id="other_exception" style="display:<?php echo $display;?>;">
                    <div><span style="font-size:11px; font-weight:bold;color:#CC0000; background-color:#FFFFCC">Type Name or Role to Send To :</span></div>
                    <div style="width:100%; font-size:12px">
                        <input type="text" name="recepient_alt" id="recepient_alt" value="" style="width:90%"/>
                    </div>      
                </div>                
            </td>
            </tr>
			<?php
			}
			?>            
        
        <tr>
            <td height="50" style="padding:0px 50px" colspan="2">
            <input type="hidden" value="process_task" name="formaction" />
    		<!--<a href="#" class="button_small" onClick="document.forms['task'].submit()" >Done / Pass it On</a>-->
           <a onclick="document.forms['task'].submit()" href="#"><input type="submit" class="button_small" id="button_passiton" value="Done / Pass it On" /></a>
            </td>
        </tr>
	</table>
	</div>
        
    
	<div class="actionlist 3" style="margin:0; padding:0">
    <table border="0" width="100%" cellpadding="0" cellspacing="0" >
    	<tr>
            <td width="100%" valign="top" class="tbl_data" colspan="2" >
                <div><span style="font-size:11px; font-weight:bold;color:#990000; background-color:#FFFFFF"><?php echo $lbl_youraction_msg; ?></span></div>
                <div style="width:100%; font-size:12px">
	            	<!--<textarea cols="25" rows="4" name="task_msg_3"><?php if (isset($tkttskmsg3)) { echo $tkttskmsg3; }?></textarea>-->
                    <input style="width:90%" type="text" name="task_msg_3" id="task_msg_3" value="<?php if (isset($tkttskmsg3)) { echo $tkttskmsg3; }?>" />
                </div>    
			</td>	
        </tr>
        <tr>
        	<td class="tbl_data">
            <strong><?php echo $lbl_asterik;?><?php echo $lbl_transto;?></strong>            </td>
            <td class="tbl_data">
            <?php
						//confirm what the next workflow id is for this task
						$sql_role="SELECT idusrrole,idusrac,usrrolename,utitle,lname FROM usrrole
						INNER JOIN usrac ON usrrole.idusrrole=usrac.usrrole_idusrrole
						INNER JOIN usrteamzone ON usrrole.usrteamzone_idusrteamzone=usrteamzone.idusrteamzone
						WHERE (usrteamzone.idusrteamzone=".$_SESSION['WkAtToMrPa_userteamzoneid']." OR usrteamzone.usrteam_idusrteam=".$_SESSION['WkAtToMrPa_idacteam'].") AND idusrrole!=".$_SESSION['WkAtToMrPa_iduserrole']."  ORDER BY usrrolename ASC";
						$res_role=mysql_query($sql_role);
						$fet_role=mysql_fetch_array($res_role);
						$num_role=mysql_num_rows($res_role);

								
						echo "<select name=\"assign_to_3\" >";
						echo "<option value=\"\">---</option>";
						do {
						echo "<option value=\"".$fet_role['idusrrole']."\">".$fet_role['usrrolename']." (".$fet_role['utitle']." ".$fet_role['lname'].")</option>";
						} while ($fet_role=mysql_fetch_array($res_role));
						echo "</select>";	
						?>            </td>
        </tr>
         <tr>
        	<td class="tbl_data">
            <strong><?php echo $lbl_asterik;?><?php echo $lbl_newdeadline;?></strong>            </td>
            <td class="tbl_data">
            <input type="text" name="newdeadline" value="<?php if (isset($tktnewdeadline)) { echo $tktnewdeadline;}?>" readonly="readonly" onClick="javascript:show_calendar('document.task.newdeadline', document.task.newdeadline.value);" />
            <a href="javascript:show_calendar('document.task.newdeadline', document.task.newdeadline.value);">
            <img src="../../assets/btns/cal.gif" width="30" align="absmiddle" height="30" border="0" alt="Click Here to Pick up the timestamp"></a>            </td>
        <tr>
            <td height="50" style="padding:0px 50px" colspan="2">
            <input type="hidden" value="process_task" name="formaction" />
    		<!--<a href="#" class="button_small" onClick="document.forms['task'].submit()" >Submit</a>            -->
            <a onclick="document.forms['task'].submit()" href="#"><input type="submit" class="button_small" id="button_passiton" value="Submit" /></a>
            </td>
        </tr>
	</table>
	</div>
    
    <div class="actionlist 4" style="margin:0; padding:0">
        <table border="0" width="100%" cellpadding="0" cellspacing="0" >
 			<tr>
            	<td width="100%" class="tbl_data" colspan="2">
                    <div><span style="font-size:11px; font-weight:bold;color:#990000; background-color:#FFFFFF"><?php echo $lbl_reasoninvalid;?></span></div>
                    <div style="width:100%; font-size:12px">
            		<?php
						//confirm what the next workflow id is for this task
						$sql_role="SELECT idwftskinvalidlist,wfttaskinvalidlistlbl FROM wftskinvalidlist ORDER BY wfttaskinvalidlistlbl ASC";
						$res_role=mysql_query($sql_role);
						$fet_role=mysql_fetch_array($res_role);
						$num_role=mysql_num_rows($res_role);
						
					//	echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>";		
						echo "<select style=\"width:90%\" name=\"invalid_id\" id=\"invalid_id\">";
						echo "<option value=\"-1\" selected>----</option>";
						do {
						echo "<option value=\"".$fet_role['idwftskinvalidlist']."\">".$fet_role['wfttaskinvalidlistlbl']."</option>";
						} while ($fet_role=mysql_fetch_array($res_role));
						echo "<option value=\"0\">".$lbl_notlistedadd."</option>";
						echo "</select>";	
						
					//	echo "</td><td>";
						echo "<div id=\"0\" class=\"invalid_new\"  style=\"display:none; padding:10px 0px 10px 0px\">".$lbl_newreason." : <input type=\"text\" name=\"add_reason\"></div>";
					//	echo "</td></tr></table>";
						?>            
            	</div>                
        	</td>
        </tr>
    	<tr>
           	<td width="100%" valign="top" class="tbl_data" colspan="2" >
                <div><span style="font-size:11px; font-weight:bold;color:#990000; background-color:#FFFFFF"><?php echo $lbl_youraction_msg; ?></span></div>
                <div style="width:100%; font-size:12px">
	            	<!--<textarea cols="25" rows="4" name="task_msg_4"><?php //if (isset($tkttskmsg4)) { echo $tkttskmsg4; }?></textarea>-->
                    <input style="width:90%" type="text" name="task_msg_4" id="task_msg_4" value="<?php if (isset($tkttskmsg4)) { echo $tkttskmsg4; }?>" />
                </div>    
            </td>
		</tr>
       
        <tr>
			<td height="50" style="padding:0px 50px" colspan="2">
            <input type="hidden" value="process_task" name="formaction" />
    		<!--<a href="#" class="button_small" onClick="document.forms['task'].submit()" >Invalidate Ticket</a>-->
            <a onclick="document.forms['task'].submit()" href="#"><input type="submit" class="button_small" id="button_passiton" value="Invalidate Ticket" /></a>
            </td>
        </tr>
	</table>
    </div>
    
    <div class="actionlist 5" style="margin:0; padding:0">
    <table border="0" width="100%" cellpadding="0" cellspacing="0" >
    	<tr>
          	<td width="100%" valign="top" class="tbl_data" colspan="2" >
                <div><span style="font-size:11px; font-weight:bold;color:#990000; background-color:#FFFFFF"><?php echo $lbl_youraction_msg; ?></span></div>
                <div style="width:100%; font-size:12px">
	            	<!--<textarea cols="25" rows="4" name="task_msg_5"><?php //if (isset($tkttskmsg5)) { echo $tkttskmsg5; }?></textarea>-->
                    <input style="width:90%" type="text" name="task_msg_5" id="task_msg_5" value="<?php if (isset($tkttskmsg5)) { echo $tkttskmsg5; }?>" />
                </div>    
            </td>
        </tr>
        <tr>
        	<td class="tbl_data">
            <strong><?php echo $lbl_asterik;?><?php echo $lbl_sendto;?></strong>            </td>
            <td class="tbl_data">
            <?php
						//confirm what the next workflow id is for this task
						$sql_role="SELECT idusrrole,idusrac,usrrolename,utitle,lname FROM usrrole
						INNER JOIN usrac ON usrrole.idusrrole=usrac.usrrole_idusrrole
						WHERE idusrrole=".$_SESSION['WkAtToMrPa_reportingto']."  ORDER BY usrrolename ASC";
						$res_role=mysql_query($sql_role);
						$fet_role=mysql_fetch_array($res_role);
						$num_role=mysql_num_rows($res_role);

								
						echo "<select name=\"assign_to_5\" >";
						do {
						echo "<option value=\"\">---</option>";
						echo "<option value=\"".$fet_role['idusrrole']."\">".$fet_role['usrrolename']." (".$fet_role['utitle']." ".$fet_role['lname'].")</option>";
						} while ($fet_role=mysql_fetch_array($res_role));
						echo "</select>";	
						?>            </td>
        </tr>
        <tr>
			<td height="50" style="padding:0px 50px" colspan="2">
            <input type="hidden" value="process_task" name="formaction" />
    		<!--<a href="#" class="button_small" onClick="document.forms['task'].submit()" >Submit</a></td>-->
            <a onclick="document.forms['task'].submit()" href="#"><input type="submit" class="button_small" id="button_passiton" value="Submit" /></a>
        </tr>
	</table>    
	</div>
    
     <div class="actionlist 6" style="margin:0; padding:0">
    <table border="0" width="100%" cellpadding="0" cellspacing="0" >
    	<tr>
          	<td width="100%" class="tbl_data" colspan="2">
            	<div><span style="font-size:11px; font-weight:bold;color:#990000; background-color:#FFFFFF"><?php echo $lbl_update_prog;?></span></div>
                <div style="width:100%; font-size:12px">
					<?php
                    $sql_progup="SELECT idwftskupdates_class,wftskupdates_classlbl,wftskupdates_classdesc FROM wftskupdates_class";
                    $res_progup=mysql_query($sql_progup);
                    $num_progup=mysql_num_rows($res_progup);
                    $fet_progup=mysql_fetch_array($res_progup);
        //			echo $sql_progup;
                    echo "<select style=\"width:90%\" name=\"progress_update_status\">";
                    do {
                    echo "<option value=\"".$fet_progup['idwftskupdates_class']."\">".$fet_progup['wftskupdates_classlbl']."</option>";
                    } while ($fet_progup=mysql_fetch_array($res_progup));
                    echo "</select>";			
                    ?>            
            	</div>
            </td>
       </tr>
<?php /*?>       <tr>
            <td width="25%" valign="top" class="tbl_data" align="left" >
            <strong><?php echo $lbl_nemail_notify;?></strong>
			</td>
            <td class="tbl_data">
            <input type="text" name="progress_update_emails" size="30" maxlength="150" />            </td>
		</tr>
<?php */?>    	<tr>
           	<td width="100%" valign="top" class="tbl_data" colspan="2" >
                <div><span style="font-size:11px; font-weight:bold;color:#990000; background-color:#FFFFFF"><?php echo $lbl_update_msg; ?></span></div>
                <div style="width:100%; font-size:12px">
	            	<!--<textarea cols="25" rows="4" name="task_msg_6"><?php //if (isset($tkttskmsg6)) { echo $tkttskmsg6; }?></textarea>-->
                    <input style="width:90%" type="text" name="task_msg_6" id="task_msg_6" value="<?php if (isset($tkttskmsg6)) { echo $tkttskmsg6; }?>" />
                </div>    
            </td>
		</tr>
       
        <tr>
        	<td height="50" style="padding:0px 50px" colspan="2">
            <input type="hidden" value="process_task" name="formaction" />
    		<!--<a href="#" class="button_small" onClick="document.forms['task'].submit()" >Progress Update</a>-->
            <a onclick="document.forms['task'].submit()" href="#"><input type="submit" class="button_small" id="button_passiton" value="Progress Update" /></a>
            </td>
        </tr>
	</table>
	</div>
    
	
    <div class="actionlist 9" style="margin:0; padding:0">
     <table border="0" width="100%" cellpadding="0" cellspacing="0" >
    	<tr>
            <td colspan="2" valign="top" class="tbl_data" align="left" >
            <div style="color:#FFFFFF; font-weight:bold; background-color:#FF0000; padding:2px"><img src="../../assets/icons/warning.gif" border="0" align="absmiddle"> This means that you are not able to work on this task!            </div>            </td>
        </tr>
       	<tr>
        	<td width="100%" valign="top" class="tbl_data" colspan="2" >
                <div><span style="font-size:11px; font-weight:bold;color:#990000; background-color:#FFFFFF">Your Message</span></div>
                <div style="width:100%; font-size:12px">
	            	<!--<textarea cols="25" rows="4" name="task_msg_9"><?php //if (isset($tkttskmsg9)) { echo $tkttskmsg9; }?></textarea>-->
                    <input style="width:90%" type="text" name="task_msg_9" id="task_msg_6" value="<?php if (isset($tkttskmsg9)) { echo $tkttskmsg9; }?>" />
                </div>    
            </td>                    
		</tr>
        <tr>
        	<td class="tbl_data">
            <strong><?php echo $lbl_sendto;?></strong>            </td>
            <td class="tbl_data">
            <?php	
				echo "<select name=\"assign_to_9\">";
				echo "<option value=\"".$fet_sender['idusrrole']."\" >".$fet_sender['usrrolename']." (".$fet_sender['utitle']." ".$fet_sender['fname']." ".$fet_sender['lname'].")"."</option>";
				echo "</select>";	
			?>            
            </td>
        </tr>
        <tr>
        	<td></td>
            <td height="50">
            <input type="hidden" value="process_task" name="formaction" />
    		<a href="#" id="button_return" onClick="document.forms['task'].submit()" ></a>
            </td>
        </tr>
	</table>
    </div>    
</div>

	<!-- end the options-->
                </td>
            </tr>
            <tr>
           	  <td class="body_font">&nbsp;</td>
            </tr>
            
	</table>
</form>   
          </div>
            
          <div class="menuo"><a href="index.php" style="text-decoration:none">
   	      <div><img src="../m_assets/icon_search_on.gif" width="16" height="19" border="0" align="absmiddle" /> Find Ticket</div>
       	  </a></div> 
          <div class="menuo"><a href="logout.php" style="text-decoration:none">
   	      <div><img src="../m_assets/icon_login_n.gif" width="16" height="19" border="0" align="absmiddle" /> Log Out</div>
       	  </a></div>
            
    </div>

</body>
</html>
