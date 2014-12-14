<?
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////


$PL = newClass('Playlists');
$playlists = $PL->getPlaylists();
	
$breadcrumbs = array(
	array(
		'title'=>$func->lang('Playlists'),
	)
);
$tpl->assign('playlists', $playlists["items"]);	
$tpl->assign('summary', $playlists["summary"]);
$tpl->assign('pages', $playlists["pages"]);

?>