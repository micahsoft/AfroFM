<?php
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

if(isset($_POST['action']) && $_POST['action'] != "") {   

	$PL = newClass("Playlists");
	if($_POST['action'] == "rmtrack") {
	
		if($_POST['pid'] != "") {
			$pid = $_POST['pid'];
			$trackid = $_POST['trackId'];
    		$PL->removeTrack($pid, $trackid);
    		$msg = "removed track";
    	}
    	
    }else if($_POST['action'] == "rmplaylist") {
    
    	if($_POST['pid'] != "") {
    		$pid = $_POST['pid'];
    		$PL->removePlaylist($pid);
    		$msg = "removed playlist";
    	}
    	
    }else if($_POST['action'] == "update") {
    
    	if($_POST['pid'] != "" && $_POST['pname'] != "") {
    		$pid = $_POST['pid'];
    		$pname = $_POST['pname'];
    		$PL->updatePlaylist($pid, $pname);
    		$msg = "updated_".$pid;
    	}
    		
    }else if($_POST['action'] == "new") {
    	if($_POST['pname'] != "") {
    		$pData["uid"] = $session->id;
    		$pData["name"] = $_POST['pname'];
    		$pid = $PL->createNew($pData);
    		$msg = "created_".$pid;
    	}
    }else if($_POST['action'] == "reorder") {
    	if($_POST['pid'] != "" && isset($_POST["playlist"])) {
    		$pid = $_POST['pid'];
    		$trackIds = $_POST["playlist"]; 
    		$PL->reorderPlaylist($pid, $trackIds);
    		$msg = "reordered_".$pid;
    	}
    }
	echo $msg;
}