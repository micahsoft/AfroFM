<?
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

$itunes = newClass('Itunes');

$currentId = $func->setting('DEFAULT_GENRE_ID', '', 'Homepage Settings');

if($currentId == '')
	$currentId = 20;
	
if(isset($_GET["id"]) && $_GET["id"] != "") {
	$currentId = $_GET["id"];
}


$genresData = $itunes->loadAllGenres($currentId);
$genres = $genresData["parent"];
$subgenres = $genresData["sub"];

$currentGenre = $genres[$currentId];

$currentSid = "";
$currentSubGenre = "";
if(isset($_GET["sid"]) && $_GET["sid"] != "") {
	$currentSid = $_GET["sid"];
	$currentSubGenre = $subgenres[$currentId][$currentSid];
}


$letter = isset($_GET["letter"]) ? $_GET["letter"] : "";
$page = isset($_GET["page"]) ? $_GET["page"] : "";
$alphas = $itunes->loadAlphas();
$results = $itunes->loadArtists($currentId, $currentSid, $letter, $page);
$totalArtists = sizeof($results["artists"]);
	 
$breadcrumbs[] = array(
	'title'=>$func->lang('Genres'), 
	'link'=>$func->link('genres'),
);
	           
$breadcrumbs[] = array(
	'title'=>$currentGenre, 
	'link'=>$func->link('artists', 'id='.$currentId.'&'.$func->seoTitle($currentGenre).''),
);
$title = $currentGenre;
$keywords = $currentGenre;
if($currentSid) {
	$breadcrumbs[] = array(
		'title'=>$currentSubGenre, 
		'link'=>$func->link('artists', 'id='.$currentId.'&sid='.$currentSid.'&'.$func->seoTitle($currentSubGenre).''),
	);
	$title .= ' - '.$currentSubGenre;
	$keywords .= ','.$currentSubGenre; 
}
if($letter) {
	$breadcrumbs[] = array(
		'title'=>$func->lang('Letter')." ".$letter, 
		'link'=>$func->link('artists', 'id='.$currentId.'&sid='.$currentSid.'&letter='.$letter.'&'.$func->seoTitle($currentSubGenre).''),
	);
}	

$page_title = $title;
$page_description = $func->lang('Browse').' '.$title.' '.$func->lang('music artists');
$page_keywords = strtolower(str_replace(" ", ",", $page_description));
			
$tpl->assign('totalArtists', $totalArtists);
$tpl->assign('currentId', $currentId);
$tpl->assign('currentSid', $currentSid);
$tpl->assign('currentLetter', $letter);	
$tpl->assign('currentPage', $page);	
$tpl->assign('currentGenre', $currentGenre);
$tpl->assign('currentSubGenre', $currentSubGenre);	
$tpl->assign('genres', $genres);
$tpl->assign('subgenres', $subgenres);
$tpl->assign('alphas', $alphas);
$tpl->assign('pages', $results["pages"]);
$tpl->assign('artists', $results["artists"]);


?>