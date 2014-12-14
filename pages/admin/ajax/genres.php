<?php
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

if(isset($_POST['action']) && $_POST['action'] != "") {   

	if($_POST['action'] == "hidegenre") {
    
    	if($_POST['id'] != "") {
    		$id = $_POST['id'];
    		$dbase->updateValue(TBL_GENRES, 'hidden', 1, 'id', $id);
    		$msg = "genre is hidden";
    	}
    	
    }else if($_POST['action'] == "showgenre") {
    
    	if($_POST['id'] != "") {
    		$id = $_POST['id'];
    		$dbase->updateValue(TBL_GENRES, 'hidden', 0, 'id', $id);
    		$msg = "genre is visible";
    	}
    	
    }else if($_POST['action'] == "featgenre") {
    
    	if($_POST['id'] != "") {
    		$id = $_POST['id'];
    		$dbase->updateValue(TBL_GENRES, 'featured', 1, 'id', $id);
    		$msg = "genre is featured";
    	}
    	
    }else if($_POST['action'] == "unfeatgenre") {
    
    	if($_POST['id'] != "") {
    		$id = $_POST['id'];
    		$dbase->updateValue(TBL_GENRES, 'featured', 0, 'id', $id);
    		$msg = "genre is not featured";
    	}
    	
    }
	echo $msg;
}