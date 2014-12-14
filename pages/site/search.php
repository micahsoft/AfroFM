<?
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

if(isset($_GET["term"])) {

	$itunes = newClass('Itunes');
	$query = str_replace("+", " ", $_GET["term"]);
	$entity = isset($_GET["entity"]) ? $_GET["entity"] : 'musicArtist';
	
	if($entity == "musicArtist")
		$type = "artists";
	else if($entity == "album")
		$type = "albums";
	else if($entity == "song")
		$type = "songs";
	else if($entity == "musicVideo")
		$type = "videos";
	else{
		$func->redirect('home');
		exit();
	}
	$results = $itunes->search(urlencode($query), $entity);
	$genres = $itunes->loadParentGenres();

	$breadcrumbs = array(
		array(
			'title'=>$func->lang('Search for').": ".$query." ".$func->lang($type),
		)
	);
	
	$page_title = ucwords($query);
	$page_keywords = $page_description = $page_title;

	$tpl->assign($type, $results["results"]);	
	$tpl->assign('genres', $genres);
	$tpl->assign('type', $type);
	$tpl->assign('query', $query);

}else{
	$func->redirect('home');
}
?>