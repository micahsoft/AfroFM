<?
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

$username = (isset($_GET['id']) && $_GET['id'] != "") ? $_GET['id'] : "";

if($username == "" && $session->logged_in) {
	$username = $session->username;
}

if($username != "") {
	$User = newClass('Users');
	if(isset($_POST["user"])) {
		$success = $User->updateUser($username, $_POST["user"]);
		$tpl->assign('success', $success);
	}

    $user = $User->getUser($username);
    if($user){
    
        $breadcrumbs = array(
       	 	array(
				'title'=>$func->lang('Users'),
				'link'=>$func->link('users'),
			),
			array(
				'title'=>$func->lang('Edit User'),
			)
		);

        $tpl->assign('user', $user);
    }else{
        $func->redirect('users');
    } 
}else{
    $func->redirect('users');
}
	

?>