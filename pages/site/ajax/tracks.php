<?php
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

if(isset($_POST['action']) && isset($_POST['tid']) && $_POST['action'] != "" && $_POST['tid'] != "") {   

	$PL = newClass("Playlists");
	$tid = $_POST['tid'];
	
	if($_POST['action'] == "like") {
	
    	$PL->likeTrack($tid);
    	$msg = "liked track";	
    	
    }else if($_POST['action'] == "dislike") {
    
    	$PL->dislikeTrack($tid);
    	$msg = "disliked track";
    	
    }else if($_POST['action'] == "played") {
    
    	$PL->playedTrack($tid);
    	$msg = "played track";
    		
    }
	echo $msg;
}