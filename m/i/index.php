<?php
require_once('../required/config.php');
require_once('../../Connections/connSystem.php'); 
require_once('../required/check_user_mobile.php');
mysql_select_db($database_connSystem, $connSystem); //connect to DB

//check if this user has some tasks
$sql_myundone="SELECT DISTINCT count(tktin_idtktin) as undone FROM wftasks 
WHERE wftasks.usrrole_idusrrole=".$_SESSION['WkAtToMrPa_iduserrole']." 
AND wftasks.usrac_idusrac=".$_SESSION['WkAtToMrPa_idacname']." AND wftasks.tktin_idtktin>0 
AND ((wftskstatustypes_idwftskstatustypes=0 AND wftskstatusglobal_idwftskstatusglobal=1) 
OR (wftskstatustypes_idwftskstatustypes=6 AND wftskstatusglobal_idwftskstatusglobal=2))";
$res_myundone=mysql_query($sql_myundone);
$fet_myundone=mysql_fetch_array($res_myundone);

unset($_SESSION['pview']);
unset($_SESSION['param']); 

$nohomebtn=1;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8"> 
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<title><?php echo $app_title;?></title>
<link rel="stylesheet" href="../m_assets/main.css" />
<script type="text/javascript">	
//JavaScript for creating a ceiling list	
function hidetxt()
	{
		if (document.searchbox.stp.value=="Search by Ticket, Account or Mobile Number")
			{
			document.searchbox.stp.value='';
			}
	return true;
	}

function showtxt()
	{
		if (document.searchbox.stp.value=='')
			{
			document.searchbox.stp.value='Search by Ticket, Account or Mobile Number';
			}
	return true;
	}
</script>	
</head>
<body>
	<div>
    	<div>
        <?php require_once('../m_header_2.php');?>
        </div>

       	  <div>
          <a name="bottom"></a>
			<form method="get" style="margin:0px" action="search.php" name="searchbox">
            	<div class="section" style="background-color:#FFFFFF">
                    <div class="header_font"><img src="../m_assets/icon_search_on.gif" border="0" align="absmiddle" />  Find Ticket</div>
<!--                    <div class="body_font">
                    Ticket Number <strong>[or]</strong> Account Number <strong>[or]</strong> Location
                    </div>
-->               	<div class="fldblk" align="center" style="padding:20px 10px 10px 10px; height:45px;">
                    	<input type="hidden" name="tcat" value="-1" />
                    	<input type="text" class="tbox" onclick="hidetxt()" onblur="showtxt()" name="stp" id="stp" maxlength="50" size="90%" style="width:80%" value="Search by Ticket, Account or Mobile Number" />
                  	</div>
                    <div class="fldblk" align="center" style="padding:0px 10px 10px 10px; height:45px;">
                    <a href="#" onclick="document.forms['searchbox'].submit()" class="button" style="width:90%">Find Ticket</a>
                    </div>
                </div>
               </form>
        </div>
               
        <div>            
            <div class="menu">
            	<a href="mytasks.php" style="color:#FFFFFF; text-decoration:none">
            		<div><img src="../m_assets/menu_arrow_n.png" width="16" height="19" border="0" align="absmiddle" /> My Tasks 
					<?php
                    if ($fet_myundone['undone'] > 0)
                        {
                        echo "<span class=\"box_count\">".$fet_myundone['undone']."</span>";
                        }
                    ?>
                    </div></a>
          </div>
       	  
          <div class="menu">
          	<a href="gethelp.php" style="color:#FFFFFF; text-decoration:none">
   	      		<div><img src="../m_assets/menu_arrow_n.png" width="16" height="19" border="0" align="absmiddle" /> Contact Support</div>
       	  	</a>
          </div>
                          
          <div class="menuo">
          	<a href="logout.php" style="text-decoration:none">
   	      		<div><img src="../m_assets/icon_login_n.gif" width="16" height="19" border="0" align="absmiddle" /> Log Out</div>
       	 	</a>
       	  </div>  
    	</div>
</div>
</body>
</html>
