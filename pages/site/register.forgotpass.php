<?php
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

if(isset($_POST["subforgot"])) {
     /**
     * The user has submitted the login form and the
     * results have been processed.
     */
    if(isset($_SESSION['forgotpassword'])){
    	if($_SESSION['forgotpassword']){
       		$tpl->assign("success", true);
   		}else{
    	   	$tpl->assign("success", false);
    	}
	}
    unset($_SESSION['forgotpassword']);

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