<?
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

$PL = newClass('Playlists');
if(isset($_GET["id"])) {
	$pid = $_GET["id"];
	$playlist = $PL->getPlaylist($pid);

	if($playlist) {
		$songs = $PL->getTracks($pid, array('field'=>'position', 'direction'=>'asc'));
		$playlists = $PL->getUserPlaylists($playlist["uid"]);

		
		$breadcrumbs = array(	
			array(
				'title'=>$func->lang('Playlists'),
				'link'=>$func->link('playlists'),
			),		
			array(
				'title'=>$playlist["name"], 
				'link'=>$func->link('playlist', 'id='.$playlist["id"].'&'.$func->seoTitle($playlist["uid"]).''),
			),
		);
		
		$page_title = ucwords($playlist["name"]) ." ". $func->lang('Playlist');
		$page_keywords = $page_description = $page_title;

		$tpl->assign('playlist', $playlist);
		$tpl->assign('playlists', $playlists);
		$tpl->assign('songs', $songs);
		$tpl->assign('totalSongs', sizeof($songs));
	}else{
		$func->redirect('home');
	}	
}else{
	$func->redirect('home');
}
?>