<?php
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

$latestVersion = implode('', file(APP_VERSION_URL)); 

if(APP_VERSION < $latestVersion)
	echo '<div class="error">'.APP_NAME.' version: '.$latestVersion.' has been launched<br>Visit <a href="'.APP_URL.'">'.APP_URL.'</a> for more info.</div>';

else if (APP_VERSION == $latestVersion)
	echo '<div class="success">You are using '.APP_NAME.' Version: '.APP_VERSION.' It is the latest release.</div>';

else
	echo '<div class="error">Update Check Failed</div>'. 

?>