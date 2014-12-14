<?php
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

if(isset($_POST['action']) && $_POST['action'] != "") {   

	$ST = newClass("Settings");
	if($_POST['action'] == "rmsetting") {
    
    	if($_POST['sid'] != "") {
    		$sid = $_POST['sid'];
    		$ST->removeSetting($sid);
    		$msg = "removed setting";
    	}
    	
    }else if($_POST['action'] == "completegroups") {
    
    	if($_POST['group'] != "") {
    		$group = $_POST['group'];
    		$groups = $ST->getGroups($sid);
    		echo json_encode($groups);
    		exit;
    	}
    	
    }
	echo $msg;
}