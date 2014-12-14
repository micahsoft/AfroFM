<?php
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

// Get a key from http://recaptcha.net/api/getkey
$publickey = "6Ldf9wsAAAAAALZFx7-0adTHcdehCBRe3mRdguyW";
echo recaptcha_get_html($publickey);

?>
