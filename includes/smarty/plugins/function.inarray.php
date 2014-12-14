<?php
function smarty_function_inarray($params, &$smarty) {

    $result = in_array($params["letter"], $params["letters"]);
    $smarty->assign('inArrayResult', $result);
}

?>
