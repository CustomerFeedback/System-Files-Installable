<div class="header" style="height:45px">
<table border="0" width="100%" cellpadding="2" cellspacing="0">
	<tr>
    	<!--
        <td width="0%">
        <img src="<?php //echo $url_m;?>/m_assets/company_logo_small.png" border="0" style="margin:0px;padding:0px" align="absmiddle"/>
        </td>
        -->
        <?php
        if ( (isset($_SESSION['WkAtToMrPa_logstatus'])) &&  ($_SESSION['WkAtToMrPa_logstatus']=="IS_LOGGED_IN") ) { ?>

        <td width="70%" style="padding:10px 5px; float:left; padding-right:5px;">
        <div class="txtsmall"><?php echo $_SESSION['WkAtToMrPa_usrtitle']." ".$_SESSION['WkAtToMrPa_usrfname']." ".$_SESSION['WkAtToMrPa_usrlname'] ."";?></div>
        <div class="txtsmall"><?php echo $_SESSION['WkAtToMrPa_userteamzone'];?></div>
      	</td>
        <td width="30%">
        	<?php if(!isset($nohomebtn)) { ?>
        	<div style="float:right"><a href="index.php" class="button_small">Home</a></div>
            <?php } ?>
        </td>
        <?php
		} else {
		$sql_teamname="SELECT usrteamname FROM usrteam LIMIT 1";
		$res_teamname=mysql_query($sql_teamname);
		$fet_teamname=mysql_fetch_array($res_teamname);
		$num_teamname=mysql_num_rows($res_teamname);		
		?>
       	<td width="90%" style="font-weight:bold; font-size:18px; color:#FFFFFF" >
		<div style="padding:10px 0px 0px 0px">
			<?php
			if($num_teamname>0);
				{
				echo $fet_teamname['usrteamname'];
				}
			?>        	
        </div>
        </td>
        <?php 
		}
		?>
	</tr>
</table>
</div>
