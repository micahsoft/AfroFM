<?
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////


$U = newClass('Users');

$users = $U->getUsers();
$countries = $dbase->getCountries();

$breadcrumbs = array(
	array(
		'title'=>$func->lang('Users'),
	)
);

$page_title = $func->lang('Users');
$page_keywords = $page_description = $page_title;
			
$tpl->assign('users', $users["items"]);
$tpl->assign('summary', $users["summary"]);
$tpl->assign('pages', $users["pages"]);
$tpl->assign('countries', $countries);			

?>