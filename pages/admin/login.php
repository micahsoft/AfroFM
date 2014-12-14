<?php
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

$TPL_HEADER = 'login_header.html';
$TPL_FOOTER = 'login_footer.html';

if(isset($_POST["sublogin"])) {
     /**
     * The user has submitted the login form and the
     * results have been processed.
     */
    if(isset($_SESSION['logsuccess'])){

       $refererLoc = $_POST["refererLoc"];

       /* Registration was successful */
       if($_SESSION['logsuccess']){
           if($refererLoc != "")
                $func->redirect($refererLoc);
           else
                $func->redirect('home');
       }

       unset($_SESSION['logsuccess']);

    }
}

?>