<?php
class Playlists {

	var $results_per_page;
	var $search_fields = array('id', 'name', 'uid', 'username');

	function Playlists() {
	
		$this->results_per_page = (ADMIN_MODE == true) ? 20 : 30;
	}
	
	function createNew($pData) {
		global $dbase;
		$found = $dbase->rowExistsByAttr(TBL_PLAYLISTS, array('uid'=>$pData["uid"], 'name'=>$pData["name"]), true);
		if(is_array($found)) {
			$id = $found['id'];
		}else{
     		$dbase->insertArray(TBL_PLAYLISTS, $pData);
     		$id = $dbase->lastInsertedId(TBL_PLAYLISTS);
			$dbase->increaseValue(TBL_USERS, 'playlists', 1, 'id', $pData["uid"]);
		}
     	return $id;
	}

	
	function addToPlaylist($pData) {
	
		global $dbase;
		$found = $dbase->rowExistsByAttr(TBL_PLAYLISTS_TRACKS, array('pid'=>$pData["pid"], 'trackId'=>$pData["trackId"]));
		if($found) {
			return false;
		}else{
			$dbase->insertArray(TBL_PLAYLISTS_TRACKS, $pData);
			$dbase->increaseValue(TBL_PLAYLISTS, 'count', 1, 'id', $pData["pid"]);
			return true;
		}
	}
	

	
	function getPlaylists($uid = false, $includeEmpty = true) {
		global $dbase;
     	
     	$data = array();
     	
     	if(isset($_GET)) {
     		foreach($this->search_fields as $value) {
     			$data[$value] = @$_GET[$value];
     		}
     	}
     	
     	if($uid !== false) {
     		$data["uid"] = $uid;
     	}
     	$data = array_diff($data, array(''));
     	$total = sizeof($data);

		$query = "SELECT * FROM ".TBL_PLAYLISTS;
		if($data["username"] != ""){
			$query .= " JOIN ".TBL_USERS." ON ".TBL_USERS.".id = ".TBL_PLAYLISTS.".uid";
		}

     	
     	$i = 0;
		foreach ($data as $key=>$value) {
		
			if(!is_numeric($key))
				$key = $dbase->db_escape_string($key);
			
     		if($value != "") {
     			if($i==0) {
					$query .= ' WHERE'; 
				}

				if($key == 'username'){
					$key = TBL_USERS.".".$key;
				}else{
					$key = TBL_PLAYLISTS.".".$key;
				}
     			if(!is_numeric($value)) {
                    $value = "'" . $dbase->db_escape_string($value) . "%'";
                    $query .= ' '.$key.' LIKE '.$value.'';
                }else{
                	$query .= ' '.$key.'='.$value;
                }
                
                if($i < ($total-1)) {
                	$query .= ' AND';
                }
                
				$i++;
     		}
     	}
     	
     	if(!$includeEmpty) {
     		if($i > 0)
     			$query .= ' AND';
     		else
     			$query .= ' WHERE';	
     		
     		$query .= ' count > 0';	
     	}
     	
     	$query .= ' ORDER BY '.TBL_PLAYLISTS.'.timestamp DESC';

		//Create a PS_Pagination object
		$pager = newClass('Pagination', $query, $this->results_per_page);

		//The paginate() function returns a mysql result set for the current page
		$results = $pager->paginate();

		//Loop through the result set just as you would loop
		//through a normal mysql result set
		$username = '';
		while($row = $dbase->fetch($results)) {
			if($username == ""){
				$row["username"] = $dbase->getUserField($row["uid"], 'username');
				if($uid !== false) {
					$username = $row["username"];
				}
			}else{
				$row["username"] = $username;
			}
     		$playlists["items"][] = $row;
     	}

		$playlists["summary"] = $pager->renderHeader();
		$playlists["pages"] = $pager->renderFullNav();
		
		//Display the navigation
		return $playlists;
	}
	
	function getPlaylist($pid) {	
		global $dbase;
     	$playlist = $dbase->getRowByKey(TBL_PLAYLISTS, 'id', $pid);
     	if($playlist)
     		$playlist["username"] = $dbase->getUserField($playlist["uid"], 'username');
     	return $playlist;
	}
	
	function getTracks($pid, $order=null) {	
		global $dbase;
     	$songs = $dbase->getResultsByKey(TBL_PLAYLISTS_TRACKS, 'pid', $pid, $order);
     	return $songs;
	}

	function getUserPlaylists($uid) {	
		global $dbase;
		
		$query = "SELECT * FROM ".TBL_PLAYLISTS." WHERE uid = ".$uid." ORDER BY timestamp DESC";
     	$results = $dbase->query($query);
     	while($row = $dbase->fetch($results)) {
     		$playlists[] = $row;
     	}
     	return $playlists;
	}
		
	function getTopUserTracks($uid, $limit = 20, $order=null) {	
		global $dbase;
		
		if(isset($order)) {
     		$orderBy .= 'ORDER BY t.'.$order['field'].' '.$order['direction'];
      	}else{
      		$orderBy = '';
      	}
		$query = "SELECT * FROM ".TBL_PLAYLISTS_TRACKS." t JOIN ".TBL_PLAYLISTS." p ON t.pid = p.id WHERE p.uid = ".$uid." ".$orderBy." LIMIT ".$limit;
     	$results = $dbase->query($query);
     	while($row = $dbase->fetch($results)) {
     		$songs[] = $row;
     	}
     	return $songs;
	}
	
	function removeTrack($pid, $trackId) {
		global $dbase;
		$dbase->deleteByAttr(TBL_PLAYLISTS_TRACKS, array('pid'=>$pid, 'trackId'=>$trackId), null);
		$dbase->decreaseValue(TBL_PLAYLISTS, 'count', 1, 'id', $pid);
	}
	
	function removePlaylist($pid, $uid = false) {
		global $dbase;
		if(!$uid) {
			$playlist = $this->getPlaylist($pid);
			$uid = $playlist["uid"];
		}
		$dbase->delete(TBL_PLAYLISTS, 'id', $pid);
		$dbase->delete(TBL_PLAYLISTS_TRACKS, 'pid', $pid, null);
		$dbase->decreaseValue(TBL_USERS, 'playlists', 1, 'id', $uid);
	}
	
	function removeUserPlaylists($uid) {
		global $dbase;
		$query = "SELECT id FROM ".TBL_PLAYLISTS." WHERE uid = ".$uid;
		$results = $dbase->query($query);
     	while($row = $dbase->fetch($results)) {
     		$pid = $row["id"];
     		$this->removePlaylist($pid, $uid);
     	}
	}
	
	function updatePlaylist($pid, $pname) {
		global $dbase;
		$dbase->updateValue(TBL_PLAYLISTS, 'name', $pname, 'id', $pid);
	}
	
	function reorderPlaylist($pid, $trackIds) {
		global $dbase;
		$i = 1;
		foreach($trackIds as $trackId) { 
    		$dbase->updateValueByAttr(TBL_PLAYLISTS_TRACKS, 'position', $i, array('pid'=>$pid, 'trackId'=>$trackId)); 
    		$i++;
    	}
	}
	
	function log_activity($uid, $action, $obj_id) {
		global $dbase;

		$pdata["uid"] = $uid;
		$pdata["action"] = $action;
		$pdata["obj_id"] = $obj_id;
		$dbase->insertArray(TBL_ACTIVITY, $pData);

	}
	/*	
	function playedTrack($id) {
		global $dbase;
		
		$cookie_name = 'played_track_' . $id;
		
		if (!isset($_COOKIE[$cookie_name])) {
			setcookie($cookie_name, time() + 60 * 60 * 24 * 365);
            $dbase->increaseValue(TBL_TRACKS, 'plays', 1, 'id', $id);
       		return true;
        }
		
		return false;
	}
		
	function likeTrack($id) {
		global $dbase;
		
		$cookie_name = 'liked_track_' . $id;
		
		if (!isset($_COOKIE[$cookie_name])) {
			setcookie($cookie_name, time() + 60 * 60 * 24 * 365);
            $dbase->increaseValue(TBL_TRACKS, 'likes', 1, 'id', $id);
        	return true;
        }
		
		return false;
		
	}
	
		
	function dislikeTrack($id) {
		global $dbase;
		
		$cookie_name = 'disliked_track_' . $id;
		
		if (!isset($_COOKIE[$cookie_name])) {
			setcookie($cookie_name, time() + 60 * 60 * 24 * 365);
            $dbase->increaseValue(TBL_TRACKS, 'dislikes', 1, 'id', $id);
            return true;
        }
		
		return false;
	}
	*/

}

?>