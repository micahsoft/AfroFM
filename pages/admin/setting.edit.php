<?
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

$sid = (isset($_GET['id']) && $_GET['id'] != "") ? $_GET['id'] : "";
$settings = newClass("Settings");

if($sid != "") {
	if(isset($_POST["setting"])) {
		$success = $settings->updateSetting($sid, $_POST["setting"]);
		$tpl->assign('success', $success);
	}

    $setting = $settings->getSetting($sid);
    if($setting){
    
        $breadcrumbs = array(
       	 	array(
				'title'=>$func->lang('Settings'),
				'link'=>$func->link('settings'),
			),
			array(
				'title'=>$func->lang('Edit Setting'),
			)
		);

        $tpl->assign('setting', $setting);
    }else{
        $func->redirect('settings');
    } 
}else{
    $func->redirect('settings');
}
	

?>