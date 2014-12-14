<?php
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

if(isset($_POST['ytlink']) && $_POST['ytlink'] != "") {   
	$downloadEnabled = $func->setting('ENABLE_TRACK_DOWNLOAD', '1', 'Playlist Settings', 'bool');
	$downloadEnabledMembersOnly = $func->setting('ENABLE_DOWNLOAD_FOR_MEMBERS_ONLY', '1', 'Playlist Settings', 'bool');
	if($downloadEnabled) {
		if($downloadEnabledMembersOnly && !$session->logged_in) {
			$iframe = '<div class="message error"><center>'.$func->lang("You need to be logged in to use this feature").' <br><br> <a href="'.$func->link("login").'">'.$func->lang("Login Here").'</a></center></div>';
		}else{
			$download_frame = WEBSITE_URL . 'vidfetch/?url='.$_POST['ytlink'];
			$iframe = '<iframe src="'.$download_frame.'" style="overflow-y:auto" width="445" height="300" frameborder="0" scrolling="no"></iframe>';
		}
	}else{
		$iframe = '<div class="message error"><center>'.$func->lang("This feature is disabled").'</center></div>';
	}
	echo $iframe;
	exit;
}