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
	$allAlbums = array_slice($data["results"], 1, sizeof($data["results"]) - 1);
	$totalAlbums = sizeof($albums);
	
	$albums = array();
	$i=0;
	foreach($allAlbums as $album) {
		if($i == 4)
			break;
			
		$albums[] = $album;
		$i++;
	}
	
	$picture = $itunes->findPictures('"'.$artist["artistName"].' singer"', 3);
	
	$tpl->assign('artistId', $artistId);
	$tpl->assign('bio', $bio);
	$tpl->assign('artist', $artist);
	$tpl->assign('picture', $picture);
	$tpl->assign('totalAlbums', $totalAlbums);
	$tpl->assign('albums', $albums);

}
?>