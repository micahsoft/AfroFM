<?
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

$itunes = newClass('Itunes');
$genres = $itunes->loadAllGenres();

$col1_seperate_at = 12;
$col2_seperate_at = 30;

$page_title = $func->lang('Genres');
$page_keywords = $page_description = $page_title;
		
$tpl->assign('seperator1', $col1_seperate_at);
$tpl->assign('seperator2', $col2_seperate_at);
$tpl->assign('genres', $genres["parent"]);
$tpl->assign('subgenres', $genres["sub"]);


?>