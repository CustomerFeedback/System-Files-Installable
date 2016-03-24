<?php
session_start(); //initialize session
$app_title="Citizen Feedback Mobile";

$server_pref_m=substr($_SERVER["SERVER_NAME"],0,3);

 if ($server_pref_m==139) //then thats local
 	{
	$url_m="http://139.162.212.172/kiwasco/mobile"; //local
	} else {
	$url_m="http://localhost/imum_kiwasco/mobile"; //remote
	}

function strtotitle($title)
// Converts $title to Title Case, and returns the result.
{
// Our array of 'small words' which shouldn't be capitalised if
// they aren't the first word. Add your own words to taste.
$smallwordsarray = array(
'of','a','the','and','an','or','nor','but','is','if','then','else','when',
'at','from','by','on','off','for','in','out','over','to','into','with'
);

// Split the string into separate words
$words = explode(' ', $title);

foreach ($words as $key => $word)
{
// If this word is the first, or it's not one of our small words, capitalise it
// with ucwords().
if ($key == 0 or !in_array($word, $smallwordsarray))
$words[$key] = ucwords($word);
}

// Join the words back into a string
$newtitle = implode(' ', $words);

return $newtitle;
} 

$spaces_y=array(",", ":", "-");
$spaces_n=array(", ", " : ", " - ");

$server_time = date("Y-m-d H:i:s",time());
$timenowis =date("Y-m-d H:i:s",strtotime($server_time) + (60*60*9)); //8-9 hours

$thirty_days_ago=date("Y-m-d H:i:s",strtotime($timenowis) - (30*86400)); //30 days ago
$seven_days_ago=date("Y-m-d H:i:s",strtotime($timenowis) - (7*86400)); //7 days ago
$one_day_ago=date("Y-m-d H:i:s",strtotime($timenowis) - (1*86400)); //1 days ago
$three_mins_ago=date("Y-m-d H:i:s",strtotime($timenowis) - (60*3)); //3 minutes ago
$day_sec = 86400;
$fifteen_months_ago=date("Y-m-d H:i:s",strtotime($timenowis) - (30*86400*15)); //30 days ago
$today = date("Y-m-d",time());
$timevar = date("YmdHis",time());
$thirty_days_togo=date("Y-m-d H:i:s",strtotime($timenowis) + (30*86400)); //30 days to go
$twelve_hrs_togo=date("Y-m-d H:i:s",strtotime($timenowis) + (60*60*12)); //6 hrs to go
// technocurve arc 3 php mv block1/3 start
$mocolor1 = "#FFFFFF";
$mocolor2 = "#FCFCFC";
$mocolor3 = "#F2FFF2";
$mocolor = $mocolor1;

			
function loggerIP()
			{ 
			if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
				{
				$theIP=$_SERVER['HTTP_X_FORWARDED_FOR'];
				} else {
				 $theIP=$_SERVER['REMOTE_ADDR'];
				}
			return trim($theIP);
			}
			
$userIP = loggerIP();
$userBrowser=$_SERVER['HTTP_USER_AGENT'];

//Current URL
function curPageURL() 
	{
	$pageURL = 'http';
	if ( (isset($_SERVER["HTTPS"])) && ($_SERVER["HTTPS"] == "on") ) {$pageURL .= "s";}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
 	return $pageURL;
 //just echo curPageURL();
	}
//dictionary
$lbl_asterik="<span style=\"color:#ff0000;font-size:20px;font-weight:bold\">*&nbsp;</span>";
$lbl_youraction_msg = "Task Message";
$lbl_update_prog="Subject <small>or</small> Challenge";
$lbl_action_msg = "Message";
$lbl_nemail_notify = "Email <br><small>(who to notify)</small>";
$lbl_update_msg = "Your Update";
$lbl_confirmtktclose = "I Confirm that this matter has been fully resolved<br><span style=\"color:#ff0000;font-size:11px\">Please Note :<br>1. This means that you are the <em>Last Person</em> to act on this matter and you don't need to <em>pass it on / forward </em> to anyone else in the company.<br>2. Customer(s) will receive SMS alerting him/her that the matter is fully resolved<br>3. This action will reflect on record</span>";
$lbl_reasoninvalid = "Reason to Invalidate";
$lbl_notlistedadd = ">> Not Listed <<";
$lbl_newreason = "Add New Reason";
$msg_warning_loginrequired="Sorry! You need to log in to your account to access this section.<br>Your session seems to have expired.<br><a href=\"../a/logout.php\">Back to System</a>";
$msg_warning_prom = "You have been logged out because you or someone else has just logged onto the same account from another computer or device.<br><a href=\"../a/logout.php\">Back to System</a>";
?>