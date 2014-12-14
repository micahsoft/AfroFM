<?
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////


if(isset($_GET["id"])) {
	$itunes = newClass('Itunes');
	
	$albumId = $_GET["id"];
	$data = $itunes->lookupArtist($albumId, 'song');
	$album = $data["results"][0];
	$songs = array_slice($data["results"], 1, sizeof($data["results"]) - 1);
	$totalSongs = sizeof($songs);

	$tpl->assign('album', $album);
	$tpl->assign('totalSongs', $totalSongs);
	$tpl->assign('songs', $songs);
		
}
?>