<?php
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

if(isset($_POST['action']) && $_POST['action'] != "") {   

	$User = newClass("Users");
	if($_POST['action'] == "rmuser") {
    
    	if($_POST['uid'] != "") {
    		$uid = $_POST['uid'];
    		$User->removeUser($uid);
    		$msg = "removed user";
    	}else{
    		$msg = "error removing user";
    	}
    	
    }else if($_POST['action'] == "rmuserpic") {
    
    	if($_POST['uid'] != "") {
    		$uid = $_POST['uid'];
    		$default_picture = $User->removeUserPicture($uid);
    		$msg = $default_picture;
    	}else{
    		$msg = "error";
    	}
    	
    }else if($_POST['action'] == "uploadpic") {
    
    	if($_POST['uid'] != "") {
    		$uid = $_POST['uid'];
    		$picture = $_FILES['picture'];
    		$user = $User->getUser($uid);
	  		$retval = $session->changePicture($picture, $user);

      		if($retval){
      			$picture = $dbase->getUserField($uid, "picture");
         		$msg = $picture;
      		}else{
      			$msg = "error";
      		}
    	}else{
    		$msg = "error";
    	}
    	
    }
	echo $msg;
}