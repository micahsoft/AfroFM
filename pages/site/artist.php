<?
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////


if(isset($_GET["id"])) {

	$itunes = newClass('Itunes');


	$artistId = $_GET["id"];

	$bio = $itunes->getBio($artistId);

	$data = $itunes->lookupArtist($artistId, 'album');
	$artist = $data["results"][0];
	$albums = array_slice($data["results"], 1, sizeof($data["results"]) - 1);
	$totalAlbums = sizeof($albums);
	
	$data = $itunes->lookupArtist($artistId, 'song');
	$songs = array_slice($data["results"], 1, sizeof($data["results"]) - 1);
	
	$pictures = $itunes->findPictures('"'.$artist["artistName"].' singer"', 9);

	$breadcrumbs = array(
		array(
			'title'=>$func->lang('Artists'),
			'link'=>$func->link('artists'),
		),
		array(
			'title'=>$artist["artistName"], 
		)
	);
	
	$page_title = $artist['artistName'];
	$page_description = $bio['brief'];
	$page_keywords = $artist['artistName'].','.$artist['primaryGenreName'].','.$func->lang('Music');
	$page_image = isset($pictures[0]["url"]) ? $pictures[0]["url"] : '';
	
	$tpl->assign('artistId', $artistId);
	$tpl->assign('bio', $bio);
	$tpl->assign('artist', $artist);
	$tpl->assign('pictures', $pictures);
	$tpl->assign('totalAlbums', $totalAlbums);
	$tpl->assign('albums', $albums);
	$tpl->assign('songs', $songs);

}else{
	$func->redirect('home');
}
?>