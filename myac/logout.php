<?php
session_start();
//then destroy the session
session_destroy();

/*unset($_SESSION['MVGitHub_logstatus']);
unset($_SESSION['MVGitHub_acname']);
unset($_SESSION['MVGitHub_usrtitle']);
unset($_SESSION['MVGitHub_usrfname']);
unset($_SESSION['MVGitHub_usrlname']);
unset($_SESSION['MVGitHub_idacname']);
unset($_SESSION['MVGitHub_usremail']);
unset($_SESSION['MVGitHub_acteam']);
unset($_SESSION['MVGitHub_acteamshrtname']);
unset($_SESSION['MVGitHub_logo']);
unset($_SESSION['MVGitHub_userrole']);
unset($_SESSION['MVGitHub_reportingto']);
unset($_SESSION['MVGitHub_iduserrole']);
unset($_SESSION['MVGitHub_idacteam']);
unset($_SESSION['MVGitHub_iduserprofile']);
unset($_SESSION['MVGitHub_userteamzone']);
unset($_SESSION['MVGitHub_userteamzoneid']);
unset($_SESSION['MVGitHub_userteamshortname']);  
unset($_SESSION['CFCMS_page']);
unset($_SESSION['CFCMS_page']);
unset($_SESSION['sec_view']);
unset($_SESSION['sec_uction']);
unset($_SESSION['sec_mod']);
unset($_SESSION['sec_submod']);
unset($_SESSION['parenttabview']);
unset($_SESSION['parenttabview']);*/
header('location:../user_login/a/');
exit;
?>