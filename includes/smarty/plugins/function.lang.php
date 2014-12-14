<?php
function smarty_function_lang($params, &$smarty) {

	global $func;
    	
    if ($params["output"] == "")
        $params["output"] = "echo";

    $string = $params["string"];
    
	$func->lang($string, $params["output"]);
}
?>
