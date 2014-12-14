<?
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

$breadcrumbs = array(
	array(
		'title'=>$func->lang('Settings'),
		'link'=>$func->link('settings'),
	),
	array(
		'title'=>$func->lang('Add Setting'),
	)
);


if(isset($_POST["setting"])) {

	$data = $_POST["setting"];
	$settings = newClass("Settings");
	$sid = $settings->addSetting($data);
	$success = false;
	if($sid != -1) {
		$success = true;
		$func->redirect('setting.edit', 'id='.$sid);
	}

}

?>