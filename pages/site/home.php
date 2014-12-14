<?
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

$itunes = newClass('Itunes');

$default_genre = $func->setting('DEFAULT_GENRE_ID', '', 'Homepage Settings');

$genre_id = null;
if($default_genre != '')
	$genre_id = $default_genre;
	
if(isset($_GET["gid"])) {
	$genre_id = $_GET["gid"];
	if($genre_id == "random") {
		$genre = $itunes->getRandomFeaturedGenre();
		$genre_id = $genre["id"];
	}else if($genre_id == "all") {
		$genre_id = null;
	}
}
$featuredGenres = $itunes->loadParentGenres(true);
$genresDropdown = $itunes->getGenresDropdown($featuredGenres, $genre_id);
   		
$feeds = array(); 
$maxFeatured = $func->setting('MAX_FEATURED_ALBUMS', 6, 'Homepage Settings');
$maxNew = $func->setting('MAX_NEW_ALBUMS', 18, 'Homepage Settings');
$maxTop = $func->setting('MAX_TOP_ALBUMS', 18, 'Homepage Settings');
$maxAdded = $func->setting('MAX_JUST_ADDED_ALBUMS', 18, 'Homepage Settings');
$maxSongs = $func->setting('MAX_TOP_SONGS', 30, 'Homepage Settings');

$feeds['featured'] = $itunes->getFeed('featured', $genre_id, $maxFeatured);
$feeds['new_releases'] = $itunes->getFeed('new_releases', $genre_id, $maxNew);
$feeds['top_albums'] = $itunes->getFeed('top_albums', $genre_id, $maxTop);
$feeds['just_added'] = $itunes->getFeed('just_added', $genre_id, $maxAdded);
$feeds['top_songs'] = $itunes->getFeed('top_songs', $genre_id, $maxSongs);

$tpl->assign('feeds', $feeds);

$show_genres_dropdown = $func->setting('SHOW_GENRES_DROPDOWN', 1, 'Homepage Settings', 'bool');

if($show_genres_dropdown)
	$tpl->assign('genresDropdown', $genresDropdown);

?>