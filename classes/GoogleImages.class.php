<?php

class GoogleImages
{

    public function findPictures($query, $limit=1){
    	global $func;
        //creating array with urls
        $url = 'http://ajax.googleapis.com/ajax/services/search/images?v=1.0&rsz=7&q='.urlencode($query);
		$curl = newClass('Curl');
		$curl->referer = "http://www.google.com";
		$data = json_decode($curl->get($url), true);	
        $data = array_slice($data["responseData"]["results"], 0, $limit);

        $i = 0;
        foreach($data as $image) {
        	$images[$i]["url"] = $image["url"];
        	$images[$i]["title"] = $image["title"];
        	$i++;
        }

        //outputting results
        return $images;
    }
}
?>