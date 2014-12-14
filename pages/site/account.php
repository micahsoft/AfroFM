<?php
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

$user = $session->userinfo;
$countries = $dbase->getCountries();
$currentYear = date("Y");

$tpl->assign('user', $user);
$tpl->assign('countries', $countries);
$tpl->assign('currentYear', $currentYear);


if(isset($_POST["subsettings"])) {

    if(isset($_SESSION['settings_changed'])){
       /* Avatar change was successful */
       if($_SESSION['settings_changed']){
          $func->redirect('account', "success=true");
       }

       unset($_SESSION['settings_changed']);

    }

}

?>
