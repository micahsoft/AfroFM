<?
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////


if(isset($_GET["id"])) {
	$itunes = newClass('Itunes');
	
	$albumId = $_GET["id"];
	$data = $itunes->lookupArtist($albumId, 'song');
	$album = $data["results"][0];
	$album["collectionPrice"] = $func->country_currency($itunes->country, $album["collectionPrice"]);
	$songs = array_slice($data["results"], 1, sizeof($data["results"]) - 1);
	$totalSongs = sizeof($songs);
	
	$currentSong = null;
	if(isset($_GET["tid"]) && $_GET["tid"] != "") {
		foreach($songs as $song) {
			if($song["trackId"] == $_GET["tid"]) {
				$currentSong = $song;
				break;
			}
		}
	}

	$data = $itunes->lookupArtist($album["artistId"], 'album', 15);
	$topAlbums = array_slice($data["results"], 1, sizeof($data["results"]) - 1);

	$breadcrumbs = array(
		array(
			'title'=>$func->lang('Artists'),
			'link'=>$func->link('artists'),
		),
		array(
			'title'=>$album["artistName"], 
			'link'=>$func->link('artist', 'id='.$album["artistId"].'&'.$func->seoTitle($album["artistName"]).''),
		),
		array(
			'title'=>$album["collectionName"], 
		)
	);
	if($currentSong) {
		$page_title = $currentSong['trackName'].' - '.$album['artistName'];
		$page_description = $currentSong['trackName'].' - '.$album['collectionName'].' by '.$album['artistName'].', '.$func->lang('Album released on').' '.date('Y-m-d', strtotime($album['releaseDate'])).', Genre: '.$album['primaryGenreName'];
		$page_keywords = strtolower($currentSong['trackName'].','.$album['artistName'].','.$album['primaryGenreName'].','.$func->lang('Music Album'));
		$playSongJs = "<script>setTimeout(function() { $('#track_row_".$currentSong['trackId']." .play').trigger('click'); },100);</script>";
	}else{
		$page_title = $album['collectionName'].' - '.$album['artistName'];
		$page_description = $album['collectionName'].' by '.$album['artistName'].', '.$func->lang('Album released on').' '.date('Y-m-d', strtotime($album['releaseDate'])).', Genre: '.$album['primaryGenreName'];
		$page_keywords = strtolower($album['collectionName'].','.$album['artistName'].','.$album['primaryGenreName'].','.$func->lang('Music Album'));
	}
	$page_image = isset($album['artworkUrl100']) ? $album['artworkUrl100'] : '';
	
	$tpl->assign('album', $album);
	$tpl->assign('totalSongs', $totalSongs);
	$tpl->assign('songs', $songs);
	$tpl->assign('topAlbums', $topAlbums);
	$tpl->assign('playSongJs', $playSongJs);
		
}else{
	$func->redirect('home');
}
?>