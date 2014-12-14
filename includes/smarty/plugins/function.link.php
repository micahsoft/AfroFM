<?php
function smarty_function_link($params, &$smarty) {
        global $func;

		$trail = "";
		if(isset($params['t'])) {
			$trail = $func->seoTitle($params['t']);
		}
		$fullLink = false;
		if(isset($params['f']) && $params['f'] == 'true') {
			$fullLink = true;
		}
		
		$absolute = false;
		if(isset($params['abs']) && $params['abs'] == 'true') {
			$absolute = true;
		}
		
		$mode = "";
		if(isset($params['mode'])) {
			$mode = $params['mode'];
		}
		$link = $func->link($params['l'], $params['q'], $trail, $fullLink, $mode);
		if($absolute) {
			$link = "http://" . $_SERVER['HTTP_HOST'].$link;
		}
		echo $link;
		
}
?>
