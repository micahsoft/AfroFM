<?php
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

$user = $session->userinfo;
$tpl->assign('user', $user);

$PL = newClass('Playlists');
$playlists = $PL->getUserPlaylists($session->id);

$pid = false;
if(isset($_GET["id"])) {
    $pid = $_GET["id"];
}else if(!empty($playlists)){
    $pid = $playlists[0]["id"];
}

if($pid) {
	$playlist = $PL->getPlaylist($pid);
	$songs = $PL->getTracks($pid, array('field'=>'position', 'direction'=>'asc'));
	$tpl->assign('playlist', $playlist);
	$tpl->assign('songs', $songs);
	$tpl->assign('playlists', $playlists);
}else{
	$tpl->assign('playlist', false);
}
	


?>
