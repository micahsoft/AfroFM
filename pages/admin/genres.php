<?
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

$itunes = newClass('Itunes');
$genres = $itunes->manageAllGenres();
		
$tpl->assign('genres', $genres["parent"]);
$tpl->assign('subgenres', $genres["sub"]);


?>