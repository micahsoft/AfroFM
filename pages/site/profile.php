<?php
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

if(isset($_GET['u']) && $_GET['u'] != "") {
	$username = $_GET['u'];
	$user = $dbase->getUserInfo($username);
	
}else if($session->logged_in) {

	$user = $session->userinfo;
}else{
    $func->redirect('home');
    exit();
}

if($user){
    $PL = newClass('Playlists');
    $playlists = $PL->getPlaylists($user["id"]);
    $songs = $PL->getTopUserTracks($user["id"], 20, array('field'=>'timestamp', 'direction'=>'DESC'));
    
    $page_title = ucwords($user["username"]);
	$page_keywords = $page_description = $page_title;
	
	
    $tpl->assign('user', $user);
	$tpl->assign('playlists', $playlists["items"]);		
	$tpl->assign('pages', $playlists["pages"]);
	$tpl->assign('songs', $songs);
}else{
    $func->redirect('home');
}

?>
