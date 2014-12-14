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
    	
    }
	echo $msg;
}