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
	
$tpl->assign('users', $users["items"]);
$tpl->assign('pages', $users["pages"]);
$tpl->assign('countries', $countries);	

?>