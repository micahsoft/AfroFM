<?
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

$breadcrumbs = array(
	array(
		'title'=>$func->lang('Users'),
		'link'=>$func->link('users'),
	),
	array(
		'title'=>$func->lang('Add User'),
	)
);


if(isset($_POST["user"])) {

	$data = $_POST["user"];

	$User = newClass('Users');
	$status = $User->createNew($data);
	$success = false;
	if($status == 0) {
		$success = true;
		$data = array();
		$uid = $dbase->lastInsertedId(TBL_USERS);
	}
	$tpl->assign('success', $success);
	$tpl->assign('uid', $uid);
	$tpl->assign('data', $data);
}

?>