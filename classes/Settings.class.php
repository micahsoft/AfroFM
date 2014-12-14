<?php
class Settings {

	var $results_per_page;
	var $search_fields = array('id', 'name', 'key', 'value', 'type', 'group');

	function Settings() {
	
		$this->results_per_page = 10000000;
	}
	
	function constantsToSettings() {
	
		$constants = get_defined_constants(true);
		$constants = $constants["user"];
		foreach($constants as $key=>$val) {
			if(strpos($key, "SMARTY") === false) {
				$group = "Main Settings";
				if (strpos($key, "TBL_") !== false || strpos($key, "DB_") !== false) {
					$group = "Database";
				}
				
				$setting = array(
					'name'=>ucwords(strtolower(str_replace("_", " ", $key))),
					'key'=>$key,
					'value'=>$val,
					'group'=>$group,
					'type'=>(is_numeric($val) ? 'numeric' : 'text')
				);
				print_r($setting);
				$id = $this->addSetting($setting);
			}
		}
	}

	function defineSettings() {
		$settings = $this->getSettings(true);

		foreach($settings["items"] as $setting) {
			define($setting["key"], $setting["value"]); 
		}
	}
		
	function getGroups() {
		global $dbase;
		$groups = array();
		$query = "SELECT DISTINCT ".TBL_SETTINGS.".group FROM ".TBL_SETTINGS." ORDER BY ".TBL_SETTINGS.".group DESC";
		$result = $dbase->query($query);
		while($row = $dbase->fetch($result)) {
     		$groups[] = $row['group'];
      	}
      	return $groups;
	}
	
	function getSettings($all=false) {
		global $dbase;
     	
     	$data = array();
     	
     	if(isset($_GET)) {
     		foreach($this->search_fields as $value) {
     			$_GET[$value] = @urldecode($_GET[$value]);
     			$data[$value] = $_GET[$value];
     		}
     	}
		if($data["group"] == '')
			$data["group"] = 'Main Settings';
						
     	$data = array_diff($data, array(''));

     	$total = sizeof($data);

		$query = "SELECT * FROM ".TBL_SETTINGS;
     	
     	if(!$all) {
     		$i = 0;
			foreach ($data as $key=>$value) {
			
				if(!is_numeric($key))
					$key = $dbase->db_escape_string($key);
				
     			if($value != "") {
     				if($i==0) {
						$query .= ' WHERE'; 
					}
	
					$key = TBL_SETTINGS.".".$key;
					
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
	     	$query .= ' ORDER BY '.TBL_SETTINGS.'.order DESC';
			//echo $query;
		
		}		
		//Create a PS_Pagination object
		$pager = newClass('Pagination', $query, $this->results_per_page);

		//The paginate() function returns a mysql result set for the current page
		$results = $pager->paginate();

		//Loop through the result set just as you would loop
		//through a normal mysql result set
		while($row = $dbase->fetch($results)) {
		    $row["value"] = stripslashes( htmlentities( $row["value"] ));
     		$settings["items"][] = $row;
     	}

		$settings["summary"] = $pager->renderHeader();
		$settings["pages"] = $pager->renderFullNav();
		$settings["attr"] = $data;
		
		//Display the navigation
		return $settings;
	}
	
	function getSetting($sid) {	
		global $dbase;
     	$setting = $dbase->getRowByKey(TBL_SETTINGS, 'id', $sid);
        $setting["value"] = stripslashes( htmlentities( $setting["value"] ));

     	return $setting;
	}
	
	function addSetting($sData) {
		global $dbase, $form;
		$form->num_errors = 0;
		
		$field = 'name';
		if($sData[$field] == '') 
			$form->setError($field, "* Setting name not entered");
		else{
			if($sData['key'] == '') 
				$sData["key"] = $sData["name"];
			$sData["key"] = $this->generateKey($sData["key"]);	
		}
			
		$field = 'type';
		if($sData[$field] == '') 
			$form->setError($field, "* Setting type not selected");
			
		if($sData["group"] == '')
			$sData["group"] = 'Main Settings';
				
		if($form->num_errors > 0){
        	return -1;  //Errors with form
      	}
      		
		$found = $dbase->rowExistsByAttr(TBL_SETTINGS, array('key'=>$sData["key"]), true);
		if(is_array($found)) {
			$id = $found['id'];
		}else{      	
     		$dbase->insertArray(TBL_SETTINGS, $sData);
     		$id = $dbase->lastInsertedId(TBL_SETTINGS);
		}
     	return $id;
	}
	

	function updateSetting($sid, $sData) {
		global $dbase, $form;
		$form->num_errors = 0;
			
		$field = 'name';
		if($sData[$field] == '') 
			$form->setError($field, "* Setting name not entered");
		else{
			if($sData['key'] == '') 
				$sData["key"] = $sData["name"];
			$sData["key"] = $this->generateKey($sData["key"]);	
		}
			
		$field = 'type';
		if($sData[$field] == '') 
			$form->setError($field, "* Setting type not selected");
			
		if($sData["group"] == '')
			$sData["group"] = 'Main Settings';
				
		if($form->num_errors > 0){
        	return -1;  //Errors with form
      	}
      	
		$sData["key"] = $this->generateKey($sData["key"]);
		
		$dbase->updateArray(TBL_SETTINGS, $sData, 'id', $sid);
		$this->clearCookies();
		
		return true;
	}
	
	function removeSetting($sid) {
		global $dbase;
		$settings = $this->getSetting($sid);
		if($setting["system"] == 0)
			$dbase->delete(TBL_SETTINGS, 'id', $sid);
	}

	function generateKey($string){
		$string = preg_replace("`\[.*\]`U","",$string);
		$string = preg_replace('`&(amp;)?#?[a-z0-9]+;`i','_',$string);
		$string = htmlentities($string, ENT_COMPAT, 'utf-8');
		$string = preg_replace( "`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i","\\1", $string );
		$string = preg_replace( array("`[^a-z0-9]`i","`[-]+`") , "_", $string);
		return strtoupper(trim($string, '_'));
	}
	
	
	function clearCookies() {
		setcookie('ctemplate','',-1000,'/');
		setcookie('ctheme','',-1000,'/');
	}

}

?>