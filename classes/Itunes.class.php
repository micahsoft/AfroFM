<?php

class Itunes{

	var $country;
	var $country_id;
   	var $partner_id;
   	var $tracking_url;
   	var $rssFeeds = array(
   		'top_songs' => 'https://itunes.apple.com/{country}/rss/topsongs/limit={limit}/{genre}explicit=true/xml',
   		'top_albums' => 'https://itunes.apple.com/{country}/rss/topalbums/limit={limit}/{genre}explicit=true/xml',  
   		'top_videos' => 'https://itunes.apple.com/{country}/rss/topmusicvideos/{limit}/{genre}explicit=true/xml', 	
   		'new_releases' => 'https://itunes.apple.com/WebObjects/MZStore.woa/wpa/MRSS/newreleases/sf={country_id}/limit={limit}/{genre}explicit=true/rss.xml',
   		'featured' => 'https://itunes.apple.com/WebObjects/MZStore.woa/wpa/MRSS/featuredalbums/sf={country_id}/limit={limit}/{genre}explicit=true/rss.xml',
   		'just_added' => 'https://itunes.apple.com/WebObjects/MZStore.woa/wpa/MRSS/justadded/sf={country_id}/limit={limit}/{genre}explicit=true/rss.xml'
   	);
   	
   	function Itunes() {
   	
   		global $func;
   		
   		$this->country = strtolower($func->setting('ITUNES_COUNTRY', 'us', 'Itunes Affiliate'));
   		$country_id = "";
   		if(!defined('ITUNES_COUNTRY_ID') && $this->country != '') {
	   		$country_id = $this->getCountryId();
   		}

   		$this->country_id = $func->setting('ITUNES_COUNTRY_ID', $country_id, 'Itunes Affiliate');
   		$this->partner_id = $func->setting('LINK_SYNERGY_PARTNER_ID', '30', 'Itunes Affiliate');
   		$this->tracking_url = $func->setting('LINK_SYNERGY_TRACKING_URL', 'http://click.linksynergy.com/fs-bin/stat?id=qIvlhymHWA0&offerid=146261&type=3&subid=0&tmpid=1826&RD_PARM1=', 'Itunes Affiliate');

   	}
   	
   	function getCountryId() {
	   	
	   	global $func;
	   	
	   	$countries_url = 'http://www.apple.com/itunes/affiliates/resources/documentation/linking-to-the-itunes-music-store.html';
	   	$page = $func->getUrlContents($countries_url);
		$pattern = '/\<td\>'.strtoupper($this->country).'\<\/td\>.+?\<td.align\=\"right\"\>(.+?)\<\/td\>/s';
		preg_match ($pattern, $page, $matches);

		if(isset($matches[1])) {
			return $matches[1];
		}
	   	return '';
   	}

    //Search
    function grabGenres() {
        global $func, $dbase;

   		$url = 'https://itunes.apple.com/'.$this->country.'/genre';
		$page = $func->getUrlContents($url);

    	$page = @explode('<div id="genre-nav"', $page);
		$page = @explode('</div>', $page[1]);
		$page = $page[0];
		$pattern = '/id([0-9]+)" class\="top-level-genre".title=".+?"\>(.+?)\<\/a\>/';

		if(preg_match_all($pattern, $page, $matches, PREG_SET_ORDER)) {

			$genres = array();
			$subgenres = array();
			foreach($matches as $match) {
				$id = $match[1];
				$name = $match[2];
        		
        		$data = array(
        			"id" => $id,
        			"name" => $name
        		);
        		$dbase->insertArray(TBL_GENRES, $data);
						
        		$url = 'https://itunes.apple.com/'.$this->country.'/genre/id'.$id;
				$page = $func->getUrlContents($url);
				$page = $func->subPage($page, '<ul class="list top-level-subgenres">', '</ul>');
        		$pattern = '/id([0-9]+)".title=".+?"\>(.+?)\<\/a\>/';

				if(preg_match_all($pattern, $page, $matches, PREG_SET_ORDER)) {
					foreach($matches as $match) {
						$sid = $match[1];
						$sname = $match[2];
						
						$sdata = array(
        					"id" => $sid,
        					"parent_id" => $id,
        					"name" => $sname
        				);
        				$dbase->insertArray(TBL_GENRES, $sdata);
						
					}

				}

      		}
    	}		
    }
    
    function loadArtists($genreId, $sGenreId="", $l="", $p="") {
    	global $func;
    	
    	$results = array();
    	if(!empty($sGenreId))
    		$genreId = $sGenreId;
    		
    	$url = 'https://itunes.apple.com/'.$this->country.'/genre/id'.$genreId;
    	if(!empty($l))
    		$url .= "?letter=".$l;
    	if(!empty($p))
    		$url .= "&page=".$p;
    		    		
		$page = $func->getUrlContents($url);
		$subPage = $func->subPage($page, '<ul class="list paginate">', '</ul>');

		$pattern = '/\<a.href\=\"https\:\/\/itunes\.apple\.com\/'.$this->country.'\/genre\/.+?\/id.+?\?letter\=.+?\&amp;page\=([0-9]+?)\#(page\"|page\".class\=\"selected\"|page\".class\=\"paginate-more\"|page\".class\=\"paginate-previous\")\>(.+?)\<\/a\>/';
		if(preg_match_all($pattern, $subPage, $matches, PREG_SET_ORDER)) {
			$i = 0;
			foreach($matches as $match) {
				$results["pages"][$i]["page"] = $match[1];
				$results["pages"][$i]["caption"] = $match[3];
				$i++;
			}
		}
		
		
		$pattern = '/\<a.href\=\"https\:\/\/itunes\.apple\.com\/'.$this->country.'\/artist\/.+?\/id([0-9]+?)\"\>(.+?)\<\/a\>/';

		if(preg_match_all($pattern, $page, $matches, PREG_SET_ORDER)) {

			foreach($matches as $match) {
				$aid = $match[1];
				$aname = $match[2];
				$results["artists"][$aid] = $aname;
			}
			
		}	
		return $results;	    
    }
    
    function loadAlphas() {
    	return array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','#');
    }

    function getRandomFeaturedGenre() {
    	global $func, $dbase;

		$query = "SELECT id, name FROM ".TBL_GENRES." WHERE parent_id IS NULL AND featured = 1 AND hidden = 0 ORDER BY rand() LIMIT 1";
    	$result = $dbase->query($query);
   		$genre = $dbase->fetch($result);

      	return $genre;
    }
    
    function loadParentGenres($featuredOnly = false) {
    	global $func, $dbase;

		$whereFeatured = "";
		if($featuredOnly)
			$whereFeatured = "AND featured = 1";
			
		$query = "SELECT id, name FROM ".TBL_GENRES." WHERE parent_id IS NULL ".$whereFeatured." AND hidden = 0 ORDER BY name ASC";
    	$result = $dbase->query($query);
    	
    	if(!$result || ($result->rowCount() < 1)){
         	$this->grabGenres();
			$result = $dbase->query($query);
      	}
      	
      	$genres = array();
      	
       	while($row = $dbase->fetch($result)) {
     		$genres[$row["id"]] = $row["name"]; 	
      	}
      	return $genres;
    }
    
    function getGenresDropdown(&$genres, $selected_id = null) {
    	global $func;
    	$dropdown = '<select id="genres" name="genres">';
    	$dropdown .= '<option value="all"> '.$func->lang(" -- All Genres -- ").' </option>';
    	$dropdown .= '<option value="random"> '.$func->lang(" -- Random Genre -- ").' </option>';
    	foreach($genres as $id=>$genre) {
    		$selected = "";
    		if($selected_id && $selected_id == $id) {
    			$selected = " selected ";
    		}
    		$dropdown .= '<option '.$selected.' value="'.$id.'">'.$genre.'</option>';
    	}
    	$dropdown .= '</select>';
    	return $dropdown;
    }
     	
    function loadAllGenres($parent_id = null) {
    	global $func, $dbase;

		$query = "SELECT id, name FROM ".TBL_GENRES." WHERE parent_id IS NULL AND hidden = 0 ORDER BY name ASC";
    	$result = $dbase->query($query);
    	
    	if(!$result || ($result->rowCount() < 1)){
         	$this->grabGenres();
			$result = $dbase->query($query);
      	}
      
      	$pgenres = array();
      	$subgenres = array();
      	
       	while($row = $dbase->fetch($result)) {
     		$pgenres[$row["id"]] = $row["name"]; 	
      	}

		$filter = "";
		if($parent_id) {
			$filter = " AND sg.parent_id = $parent_id ";
		}
      	$query = "SELECT pg.id as pid, sg.id as id, sg.name as name FROM ".TBL_GENRES." as pg RIGHT JOIN ".TBL_GENRES." as sg ON pg.id = sg.parent_id WHERE pg.id IS NOT NULL AND sg.hidden = 0 $filter ORDER BY pg.name, name ASC";
		$result = $dbase->query($query);

    	while($row = $dbase->fetch($result)) {
			$pid = $row["pid"];
     		$subgenres[$pid][$row["id"]] = $row["name"]; 
      	}

		$genres = array();
		$genres["parent"] = $pgenres;
		$genres["sub"] = $subgenres;
		
       	return $genres;
    }


    function manageAllGenres($parent_id = null) {
    	global $func, $dbase;

		$query = "SELECT * FROM ".TBL_GENRES." WHERE parent_id IS NULL ORDER BY name ASC";
    	$result = $dbase->query($query);
    	
    	if(!$result || ($result->rowCount() < 1)){
         	$this->grabGenres();
			$result = $dbase->query($query);
      	}
      
      	$pgenres = array();
      	$subgenres = array();
      	
       	while($row = $dbase->fetch($result)) {
     		$pgenres[] = $row; 	
      	}

		$filter = "";
		if($parent_id) {
			$filter = " AND sg.parent_id = $parent_id ";
		}
      	$query = "SELECT pg.id as pid, sg.id as id, sg.name as name, sg.featured as featured, sg.hidden as hidden FROM ".TBL_GENRES." as pg RIGHT JOIN ".TBL_GENRES." as sg ON pg.id = sg.parent_id WHERE pg.id IS NOT NULL $filter ORDER BY pg.name, name ASC";
		$result = $dbase->query($query);

    	while($row = $dbase->fetch($result)) {
			$pid = $row["pid"];
     		$subgenres[$pid][] = $row; 
      	}

		$genres = array();
		$genres["parent"] = $pgenres;
		$genres["sub"] = $subgenres;
		
       	return $genres;
    }



    function lookupArtist($artistId, $entity, $limit = 100) {
    	global $func;
    	$cacheFile = "artist_".$this->country."_".$artistId."_".$entity."_".$limit.".txt";
    	if(!$func->cacheExists($cacheFile)) {

        	$url = 'https://itunes.apple.com/lookup?country='.$this->country.'&id='.$artistId.'&entity='.$entity.'&limit='.$limit;
			$artist = $func->getUrlContents($url);
			$func->writeCache($cacheFile, $artist);

        }else{
            $artist = $func->readCache($cacheFile);
        }

        $artist = json_decode($artist, true);   
        return $artist;
    }

    function getBio($artistId) {
    	global $func;
    	$cacheVersion = 5;
        $bio = array();
        $cacheFile = "artistbio_".$this->country."_".$artistId."_v".$cacheVersion.".txt";
        if(!$func->cacheExists($cacheFile)) {

			$url = 'https://itunes.apple.com/'.$this->country.'/artist/id'.$artistId.'?showBio=1';
	    	$page = $func->getUrlContents($url);

			$pattern = '/\<h5\>(Birth.Name|Born|Formed):\<\/h5\>\<p\>(.+?)\<\/p\>/';
			
			if(preg_match_all($pattern, $page, $matches, PREG_SET_ORDER)) {
				foreach($matches as $match) {
					$bio["info"][$match[1]] = $match[2];
				}
	    	}

			$start = '<div metrics-loc="Titledbox_Influencers">';
			$end = '</div>';
			$subPage = $func->subPage($page, $start, $end);
			$pattern = '/\<li\>\<a.href\="https\:\/\/itunes\.apple\.com\/'.$this->country.'\/artist\/.+?\/id(.+?)\"\>(.+?)\<\/a\>\<\/li\>/';
			
			if(preg_match_all($pattern, $subPage, $matches, PREG_SET_ORDER)) {
				$i = 0;
				foreach($matches as $match) {
					$bio["influencers"][$i]["id"] = $match[1];
					$bio["influencers"][$i]["name"] = $match[2];
					$i++;
				}
	    	}

			$start = '<div metrics-loc="Titledbox_Followers">';
			$end = '</div>';
			$subPage = $func->subPage($page, $start, $end);
			$pattern = '/\<li\>\<a.href\="https\:\/\/itunes\.apple\.com\/'.$this->country.'\/artist\/.+?\/id(.+?)\"\>(.+?)\<\/a\>\<\/li\>/';
			
			if(preg_match_all($pattern, $subPage, $matches, PREG_SET_ORDER)) {
				$i = 0;
				foreach($matches as $match) {
					$bio["followers"][$i]["id"] = $match[1];
					$bio["followers"][$i]["name"] = $match[2];
					$i++;
				}
	    	}
	    	
	    	$start = '<div metrics-loc="Titledbox_Contemporaries">';
			$end = '</div>';
			$subPage = $func->subPage($page, $start, $end);
			$pattern = '/\<li\>\<a.href\="https\:\/\/itunes\.apple\.com\/'.$this->country.'\/artist\/.+?\/id(.+?)\"\>(.+?)\<\/a\>\<\/li\>/';
			
			if(preg_match_all($pattern, $subPage, $matches, PREG_SET_ORDER)) {
				$i = 0;
				foreach($matches as $match) {
					$bio["contemporaries"][$i]["id"] = $match[1];
					$bio["contemporaries"][$i]["name"] = $match[2];
					$i++;
				}
	    	}
	    		    		    	

			$pattern = '/\<p.class\=\"extra\"\>(.+?)\<\/p\>/';
	    	if(preg_match_all($pattern, $page, $matches, PREG_SET_ORDER)) {
	    		$i = 0;
				foreach($matches as $match) {
					if($i == 0) {
						$bio["brief"] = $match[1];
					}else{
						$bio["extra"][] = $match[1];
					}
					$i++;
				}
	    	}
			$func->writeCache($cacheFile, serialize($bio));
	
		}else{
            $bio = unserialize($func->readCache($cacheFile));
        }

        return $bio;

    } 


    function search($term, $entity = 'musicArtist', $media='music', $limit=80, $nocache=false) {
    	global $func;
    	$cacheFile = "search_".$this->country."_".md5($term)."_".$media."_".$entity."_".$limit.".txt";
    	if(!$func->cacheExists($cacheFile) || $nocache) {

        	$url = 'https://itunes.apple.com/search?country='.$this->country.'&term='.$term.'&media='.$media.'&entity='.$entity.'&limit='.$limit;
			$results = $func->getUrlContents($url);
			$func->writeCache($cacheFile, $results);

        }else{
            $results = $func->readCache($cacheFile);
        }

        $results = json_decode($results, true); 
        if(empty($results))
        	$results = null;
        		
        return $results;
    }
    
    function findPictures($query, $limit=1, $nocache=false) {
    	global $func;
    	$cacheFile = "search_pic_".md5($query)."_".$limit.".txt";
    	if(!$func->cacheExists($cacheFile) || $nocache) {
			$googleImg = newClass('GoogleImages');
        	$results = $googleImg->findPictures($query, $limit);
			$func->writeCache($cacheFile, serialize($results));

        }else{
            $results = $func->readCache($cacheFile);
            $results = unserialize($results); 
        }
        
        return $results;
        
    }  

   
    function generateButton($collectionViewUrl, $alt='Buy Now', $type='sbutton') {

  	 	$button = '';
   		$param = urlencode($collectionViewUrl);
   			
   		if($type == "sbutton") {
   			$content = '<img src="http://ax.phobos.apple.com.edgesuite.net/images/web/linkmaker/badge_itunes-sm.gif" alt="'.$alt.'" style="border: 0;"/>';
   		}else if($type == "lbutton"){
   			$content = '<img src="http://ax.phobos.apple.com.edgesuite.net/images/web/linkmaker/badge_itunes-lrg.gif" alt="'.$alt.'" style="border: 0;"/>';
   		}else{
   			$content = $alt;
   		}
   		$button = '<a title="'.$alt.'" href="'.$this->tracking_url.''.$param.'%2526partnerId%253D'.$this->partner_id.'" target="itunes_store">'.$content.'</a>';
   		
   		return $button;
   } 
   
   
   function getFeed($feed, $genre_id = null, $limit = 20, $visible = 12) {
   		global $func;
   			
   		$url = $this->rssFeeds[$feed];
   		$url = str_replace(
   			array(
   				"{country}",
   				"{country_id}",
   				"{limit}",
   				"{genre}"
   			),
   			array(
   				$this->country,
   				$this->country_id,
   				$limit,
   				($genre_id && $genre_id != 'all') ? "genre=".$genre_id."/" : "/"
   			),
   			$url
   		);

   		$cacheFile = "feed_".$feed."_".md5($url).".txt";
        if(!$func->cacheExists($cacheFile, true, 1)) {
        
	        	
	   		$xml = $func->getUrlContents($url);
	   		$xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $xml);
	   		
	   		$rss = simplexml_load_string($xml);
	
	   		$patterns["album_link"] = '/https\:\/\/itunes\.apple.com\/.*?album\/.+?\/id([0-9]+)/';
	   		$patterns["genre_link"] = '/https\:\/\/itunes\.apple.com\/.*?genre\/.+?\/id([0-9]+)/';
	   		$patterns["artist_link"] = '/https\:\/\/itunes\.apple.com\/.*?artist\/.+?\/id([0-9]+)/';
	   		$patterns["song_link"] = '/https\:\/\/itunes\.apple.com\/.*?album\/.+?\/id([0-9]+)\?i\=([0-9]+)/';
	   		
	   		$data = array();
	   		$i = 0;
	
			if($feed == 'new_releases' || $feed == 'featured' || $feed == 'just_added') {
	
		   		foreach ($rss->channel->item as $item) { 
		
		   			$albumLink = (string) $item->link;
		
		   			preg_match ($patterns["album_link"], $albumLink, $matches);
		   			if(isset($matches[1])) {
		   				$album_id = $matches[1];
		   				$data[$i]["album"]["id"] = $album_id;
		   				$data[$i]["album"]["name"] = (string) $item->title; 
			   			$data[$i]["album"]["link"] = $func->link('album', 'id='.$album_id, $func->seoTitle($data[$i]["album"]["name"]));
			   			$data[$i]["album"]["updated"] = (string) $item->pubDate; 
			   			$data[$i]["album"]["coverArt"] = (string) $item->itmscoverArt[2];
			   			
			   			$genreUrl = (string) $item->category->attributes()->domain;
		   				preg_match ($patterns["genre_link"], $genreUrl, $matches);
		   				$genre_id = $matches[1];
			   			$data[$i]["genre"]["name"] = (string) $item->category;
		   				$data[$i]["genre"]["link"] = $func->link('artists', 'id='.$genre_id, $func->seoTitle($data[$i]["genre"]["name"]));
		   			
		   				$artistUrl = (string) (string) $item->itmsartistLink;
		   				preg_match ($patterns["artist_link"], $artistUrl, $matches);
		   				$artist_id = $matches[1];
		   				$data[$i]["artist"]["id"] = $artist_id;
		   				$data[$i]["artist"]["name"] = (string) $item->itmsartist;
			   			$data[$i]["artist"]["link"] = $func->link('artist', 'id='.$artist_id, $func->seoTitle($data[$i]["artist"]["name"]));
	
			   			$i++;
		   			}
		   		}
		   		
	   		}else if($feed == 'top_albums'){
	   		
		   		foreach ($rss->entry as $item) { 
	
		   			$albumLink = (string) $item->link->attributes()->href;
		
		   			preg_match ($patterns["album_link"], $albumLink, $matches);
		   			if(isset($matches[1])) {
		   				$album_id = $matches[1];
		   				$data[$i]["album"]["id"] = $album_id;
		   				$data[$i]["album"]["name"] = (string) $item->title; 
			   			$data[$i]["album"]["link"] = $func->link('album', 'id='.$album_id, $func->seoTitle($data[$i]["album"]["name"]));
			   			$data[$i]["album"]["updated"] = (string) $item->updated; 
			   			$data[$i]["album"]["coverArt"] = (string) $item->imimage[2];
			   			
		   			
		   				$artistUrl = (string) (string) $item->imartist->attributes()->href;
		   				preg_match ($patterns["artist_link"], $artistUrl, $matches);
		   				$artist_id = $matches[1];
		   				$data[$i]["artist"]["id"] = $artist_id;
		   				$data[$i]["artist"]["name"] = (string) $item->imartist;
			   			$data[$i]["artist"]["link"] = $func->link('artist', 'id='.$artist_id, $func->seoTitle($data[$i]["artist"]["name"]));
	
			   			$i++;
		   			}
		   		}
		   		  		
	   		}else if($feed == 'top_songs'){
	   		
		   		foreach ($rss->entry as $item) { 
	
		   			$albumLink = (string) $item->link->attributes()->href;
		
		   			preg_match ($patterns["song_link"], $albumLink, $matches);
		   			if(isset($matches[1])) {
		   				$album_id = $matches[1];
		   				$song_id = $matches[2];
		   					   			
		   				$data[$i]["collectionId"] = $album_id;	   			
			   			$data[$i]["collectionName"] = (string) $item->imcollection->imname;
			   			$data[$i]["coverArt"] = (string) $item->imimage[2];			   			
	
		   				$data[$i]["trackId"] = $song_id;
		   				$data[$i]["title"] = (string) $item->title; 
		   				$data[$i]["trackName"] = (string) $item->imname; 
			   			$data[$i]["updated"] = (string) $item->updated; 
			   			$data[$i]["release"] = (string) $item->imreleaseDate;
	
		   				$artistUrl = (string) (string) $item->imartist->attributes()->href;
		   				preg_match ($patterns["artist_link"], $artistUrl, $matches);
		   				$artist_id = $matches[1];
		   				$data[$i]["artistName"] = (string) $item->imartist;
		   				$data[$i]["artistId"] = $artist_id;
	
			   			$i++;
		   			}
		   		}
		   		  		
	   		}else if($feed == 'top_videos'){
	   		
		   		foreach ($rss->entry as $item) { 
	
		   					   				   			
		   			$data[$i]["video"]["title"] = (string) $item->title; 
	   				$data[$i]["video"]["name"] = (string) $item->imname; 
		   			$data[$i]["video"]["updated"] = (string) $item->updated; 
		   			$data[$i]["video"]["release"] = (string) $item->imreleaseDate;
					$data[$i]["video"]["image"] = (string) $item->imimage[2];

	   				$artistUrl = (string) (string) $item->imartist->attributes()->href;
	   				preg_match ($patterns["artist_link"], $artistUrl, $matches);
	   				$artist_id = $matches[1];
	   				$data[$i]["artist"]["id"] = $artist_id;
	   				$data[$i]["artist"]["name"] = (string) $item->imartist;
		   			$data[$i]["artist"]["link"] = $func->link('artist', 'id='.$artist_id, $func->seoTitle($data[$i]["artist"]["name"]));

		   			$i++;
		   			
		   		}
		   		  		
	   		}
	   		
			$func->writeCache($cacheFile, serialize($data), true);
	
		}else{
            $data = unserialize($func->readCache($cacheFile, true));
        }

   		return $data;
   }   
   
}

?>