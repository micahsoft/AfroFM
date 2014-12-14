<?php
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

if(isset($_POST['url']) && $_POST['url'] != "") {

	$itunes = newClass("Itunes");
	$url = $_POST['url'];
	$button = $itunes->generateButton($url, $func->lang('Buy Now'));

	echo $button;
}