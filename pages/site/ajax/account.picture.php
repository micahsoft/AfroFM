<?php
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

if(isset($_POST['action']) && $_POST['action'] != "") {   

	if($_POST['action'] == "uploadpic") {
    	$picture = $_FILES['picture'];
		$retval = $session->changePicture($picture);
	
	    if($retval){
	      	$picture = $dbase->getUserField($session->id, "picture");
	        $msg = $picture;
	    }else{
	      	$msg = "error";
	    }
	}
	echo $msg;
}