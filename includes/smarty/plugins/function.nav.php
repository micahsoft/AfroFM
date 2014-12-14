<?php
function smarty_function_navigation($params, &$smarty)
{
    $params['query'] = array(); }
    if (isset($params['query_fields']))
    {
        $params['query']['fields'] = $params['query_fields'];
    }
    if (isset($params['query_values']))
    {
        $params['query']['values'] = $params['query_values'];
    $children = $nav->getChildren($params['query'], array('no_nav_hide' => true));
    if (!$children) return false;

    $smarty->assign($params['var'], $children);
}
?>
