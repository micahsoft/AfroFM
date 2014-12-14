<?
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////


$PL = newClass('Playlists');
$playlists = $PL->getPlaylists(false, false);
	
$breadcrumbs = array(
	array(
		'title'=>$func->lang('Playlists'),
	)
);

$page_title = $func->lang('Playlists');
$page_keywords = $page_description = $page_title;
	
		
$tpl->assign('playlists', $playlists["items"]);	
$tpl->assign('summary', $playlists["summary"]);	
$tpl->assign('pages', $playlists["pages"]);

?>