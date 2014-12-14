<?php
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

$currentYear = date("Y");
$countries = $dbase->getCountries();
$tpl->assign('countries', $countries);
$tpl->assign('currentYear', $currentYear);

if(isset($_POST["subjoin"])) {

    /**
     * The user has submitted the registration form and the
     * results have been processed.
     */
    if(isset($_SESSION['regsuccess'])){
       /* Registration was successful */
       if($_SESSION['regsuccess']){
          $func->redirect('register.success');
       }
 
       unset($_SESSION['regsuccess']);
       unset($_SESSION['reguname']);
    }

}else if(isset($_POST["sublogin"])) {
     /**
     * The user has submitted the login form and the
     * results have been processed.
     */
    if(isset($_SESSION['logsuccess'])){

       $refererLoc = $_POST["refererLoc"];
       $refererParams = $_POST["refererParams"];

       /* Registration was successful */
       if($_SESSION['logsuccess']){
           if($refererLoc != "")
                $func->redirect($refererLoc, $refererParams);
           else
                $func->redirect('profile');
       }

       unset($_SESSION['logsuccess']);

    }
}

?>