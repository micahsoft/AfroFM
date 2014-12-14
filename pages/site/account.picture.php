<?php
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

$user = $session->userinfo;
$tpl->assign('user', $user);


if(isset($_POST["subpicture"]) || isset($_POST["rempicture"])) {

    if(isset($_SESSION['changepicture'])){
       /* Picture change was successful */
       if($_SESSION['changepicture']){
          $func->redirect('account.picture', "uploadSuccess=true");
       }

       unset($_SESSION['changepicture']);

    }

    if(isset($_SESSION['rempicture'])){
       /* Picture removal was successful */
       if($_SESSION['rempicture']){
          $func->redirect('account.picture', "removeSuccess=true");
       }

       unset($_SESSION['rempicture']);

    }
}

?>
