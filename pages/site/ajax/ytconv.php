<?php
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

if(isset($_POST['type']) && $_POST['type'] != "") {
   	$ytconv = newClass("Ytconv");
	$type = $_POST['type'];
	
	if($type == "check") {
	
		$url = $_POST["url"];
		$hq = $_POST["hq"];
		echo $ytconv->check($url, $hq);
		
	}else if($type == "grab"){
	
		$info = $_POST["info"];
		echo $ytconv->grab($info);
		
	}else if($type == "convert") {
		$info = $_POST["info"];
		echo $ytconv->convert($info);
		
	}else if($type == "checksize") {
		$fname = $_POST["fname"];
		$fsize = $_POST["fsize"];
		$hq = $_POST["hq"];
		echo $ytconv->result($fname, $fsize, $hq);
		
	}else{
	
		die("Error: Invalid YTCONV Type!");
	}
}else{
	die("Error: YTCONV Type cannot be empty!");
}