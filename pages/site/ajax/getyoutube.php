<?php
///////////////////////////////////////////////////////////////////////
if (!defined('IS_IN_SCRIPT')) die('Cannot access this file directly.');
///////////////////////////////////////////////////////////////////////

if(isset($_POST['artist']) && $_POST['artist'] != "" && isset($_POST['song']) && $_POST['song'] != "") {
    $query = '';
	$artist = $_POST['artist'];
	$song = $_POST['song'];
	$isVideo = (isset($_POST['isVideo']) && $_POST['isVideo'] == 'true') ? true : false;
   	$yt = newClass("Youtube");
   	$extraQuery = '';
   	if($isVideo)
   		$extraQuery .= '+official video';

    $query = '"'.$artist.'"+"'.$song.'"'.$extraQuery;
	$results = $yt->linkSearch($query, 1);
	$player = array();
	$link = "na";
	if(empty($results)) {
		$query = ''.$artist.'+'.$song.''.$extraQuery;
		$results = $yt->linkSearch($query , 1);
		if(empty($results)) {
			$query = ''.$artist.'+'.preg_replace('/(\(.+?\))/', '', $song).''.$extraQuery;
		    $results = $yt->linkSearch($query, 1);
		}else{
		    $link = $results[0];
		}
	}else{
		$link = $results[0];
	} 
	echo json_encode(array(
		'ytlink'=>$link,
		'ytid'=>$yt->parseURL($link)
	));
}