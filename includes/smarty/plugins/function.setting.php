<?php
function smarty_function_setting($params, &$smarty) {
	global $func;
	
	if(isset($params['key'])) {
		$key = $params['key'];
	}else{
		$key = "";
	}
	
	echo $func->setting($key);
}
?>
