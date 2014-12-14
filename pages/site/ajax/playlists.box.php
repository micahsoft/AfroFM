<?php
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

if($session->logged_in) {
	
	$PL = newClass('Playlists');
	
	if(isset($_POST["formData"])) {
		$formData = unserialize(base64_decode($_POST["formData"]));
	}else{
		foreach($_POST as $data) {
			$formData[] = $data;
		}
	}
	if(isset($_GET["createNew"]) && isset($_GET["playlist_name"]) && $_GET["playlist_name"] != "") {
		$pData["uid"] = $session->id;
		$pData["name"] = $_GET["playlist_name"];
		$pid = $PL->createNew($pData);
		
		$pData = $formData;
		$exists = 0;
		foreach($pData as $data) {
			$data["pid"] = $pid;
			if(!$PL->addToPlaylist($data)) {
				$exists++;
			}
		}
		if($exists > 0)
			$tpl->assign('trackExists', true);
			
		$tpl->assign('playlist_id', $data["pid"]);
		$tpl->assign('playlist_name', $_GET["playlist_name"]);
		
	}else if(isset($_GET["addTo"]) && isset($_GET["playlist_id"]) && $_GET["playlist_id"] != "") {
		$pData = $formData;
		$exists = 0;
		foreach($pData as $data) {
			$data["pid"] = $_GET["playlist_id"];
			if(!$PL->addToPlaylist($data)) {
				$exists++;
			}
		}
		if($exists > 0)
			$tpl->assign('trackExists', true);
			
		$tpl->assign('playlist_id', $_GET["playlist_id"]);	
		$tpl->assign('playlist_name', $_GET["playlist_name"]);
	}else{
		$playlists = $PL->getPlaylists($session->id);
		$tpl->assign('formData', base64_encode(serialize($formData)));
		$tpl->assign('playlists', $playlists["items"]);
		$tpl->assign('pages', $playlists["pages"]);
	}
	
}
?>