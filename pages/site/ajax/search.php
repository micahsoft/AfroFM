<?
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

$itunes = newClass('Itunes');
if(isset($_GET["term"])) {
	$query = $_GET["term"];
	$entity = isset($_GET["entity"]) ? $_GET["entity"] : 'musicArtist';
	
	$results = $itunes->search(urlencode($query), $entity, 'music', 20, true);
	echo json_encode($results);
}
?>