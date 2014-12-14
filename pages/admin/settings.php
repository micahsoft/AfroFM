<?
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

$settings = newClass("Settings");
$configs = $settings->getSettings();
$groups = $settings->getGroups();
	
$breadcrumbs = array(
	array(
		'title'=>$func->lang('Settings'),
	)
);
$tpl->assign('groups', $groups);
$tpl->assign('data', $configs["attr"]);	
$tpl->assign('settings', $configs["items"]);	
$tpl->assign('summary', $configs["summary"]);
$tpl->assign('pages', $configs["pages"]);

?>