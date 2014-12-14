<?
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

$source = 'en';
$target = 'fr';
$key = 'AIzaSyCZNIq3ND05Nl86mImkFI9BqxqNPhgGibs';

$curl = newClass('Curl');
$curl->referer = 'http://lemontunes.com';

$google_url = 'https://www.googleapis.com/language/translate/v2?key='.$key.'&source='.$source.'&target='.$target.'';
$constants = get_defined_constants(true);
$constants = $constants["user"];	
$languages = array();
foreach ($constants as $key=>$value) {

	$pos = strpos($key,'[LANG]');
	if($pos !== false) {
		echo str_replace("\\", "", $value)."<br>";
		//echo 'define("'.addslashes($key).'","'."<br>";
		//echo '");<br>';
	/*
		$query = substr($value, $pos);
		$url = $google_url."&q=".urlencode($query)."&t=".time().rand(1000,10000000);
		
		$json = $curl->get($url);
		$json = json_decode($json, true);
		print_r($json);
		echo($url);
		$translated = $json["data"]["translations"][0]["translatedText"];
		$define = 'define("'.addslashes($key).'","'.addslashes($translated).'");'."\r\n";
		echo $define."<br>";exit();
		$languageFile = 'includes/languages/'.$target.'.php';
        $func->writeToFile($languageFile, $define, 'a');
        chmod($languageFile, 0777);
    */
	}
}

$tpl->assign('languages', $languages);		


?>