<?php
class Users {

	var $results_per_page;
	var $search_fields = array('id', 'username', 'country', 'userlevel');
	
	function Users() {
	
		$this->results_per_page = (ADMIN_MODE == true) ? 20 : 30;
	}

	function createNew($data) {
		global $dbase, $session;

		$status = $session->register($data);
        return $status;
	}
	
	function updateUser($id, $post){
      global $dbase, $form;  //The database and form object
      
      $subnewpass = $post["password"];
      $subemail = $post["email"];
      $country = $post["country"];
      $sex = $post["sex"];
      $bday = $post["bday"];
      $language = $post["language"];
      if($language == '---') 
      	$language = '';
      	
      $about = $post["about"];

      /* New password entered */
      if($subnewpass){

         /* New Password error checking */
         $field = "password";  //Use field name for new password
         /* Spruce up password and check length*/
         $subpass = stripslashes($subnewpass);
         if(strlen($subnewpass) < 4){
            $form->setError($field, "* New Password too short");
         }
         /* Check if password is not alphanumeric */
         else if(!eregi("^([0-9a-z])+$", ($subnewpass = trim($subnewpass)))){
            $form->setError($field, "* New Password not alphanumeric");
         }
      }

      
      /* Email error checking */
      $field = "email";  //Use field name for email
      if($subemail && strlen($subemail = trim($subemail)) > 0){
         /* Check if valid email address */
         $regex = "^[_+a-z0-9-]+(\.[_+a-z0-9-]+)*"
                 ."@[a-z0-9-]+(\.[a-z0-9-]{1,})*"
                 ."\.([a-z]{2,}){1}$";
         if(!eregi($regex,$subemail)){
            $form->setError($field, "* Email invalid");
         }
         $subemail = stripslashes($subemail);
      }

      /* Country error checking */
      $field = "country";  //Use field name for country
      if(!$country){
         $form->setError($field, "* Country not selected");
      }

      /* Bday error checking */
      $field = "bday";  //Use field name for bday
      if(!$bday){
         $form->setError($field, "* Please enter your birthdate");
      }
      
      /* Bday error checking */
      $field = "sex";  //Use field name for bday
      if(!$sex){
         $form->setError($field, "* Please choose...");
      }

      /* Errors exist, have user correct them */
      if($form->num_errors > 0){
         return false;  //Errors with form
      }
      
      /* Update password since there were no errors */
      if($subnewpass){
         $dbase->updateUserField($id,"password",md5($subnewpass));
      }
    
      /* Change Email */
      if($subemail){

         $dbase->updateUserField($id,"email",$subemail);
      }

      /* Change Country */
      if($country){
         $dbase->updateUserField($id,"country",$country);
      }

      /* Change Bday */
      if($bday){
         $dbase->updateUserField($id,"bday",$bday);
      }
      
      /* Change Bday */
      if($sex){
         $dbase->updateUserField($id,"sex",$sex);
      }
      
      /* Change Language */
   	  $dbase->updateUserField($id,"language",$language);

      /* Change About */
      $dbase->updateUserField($id,"about",$about);

      /* Success! */
      return true;
    }



	function getUsers() {
		global $dbase;
     	
     	$data = array();
     	
     	if(isset($_GET)) {
     		foreach($this->search_fields as $value) {
     			$data[$value] = @$_GET[$value];
     		}
     	}
     	
     	$data = array_diff($data, array(''));
     	$total = sizeof($data);
     	
		$query = "SELECT * FROM ".TBL_USERS;
		
		$i = 0;
		foreach ($data as $key=>$value) {
			$key = $dbase->db_escape_string($key);
     		if($value != "") {
     			if($i==0) {
					$query .= ' WHERE'; 
				}
				$key = TBL_USERS.'.'.$key;
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
     	$query .= ' ORDER BY '.TBL_USERS.'.timestamp DESC';
     	
		//Create a PS_Pagination object
		$pager = newClass('Pagination', $query, $this->results_per_page);

		//The paginate() function returns a mysql result set for the current page
		$results = $pager->paginate();

		//Loop through the result set just as you would loop
		//through a normal mysql result set
		while($row = $dbase->fetch($results)) {
			$row["country"] = $dbase->getCountry($row['country']);
			$row["is_online"] = $this->isOnline($row["timestamp"]);
     		$users["items"][] = $row;
     	}

		$users["summary"] = $pager->renderHeader();
		$users["pages"] = $pager->renderFullNav();
		
		//Display the navigation
		return $users;
	}
	
	function getUser($id) {	
		global $dbase;
		 
		if(is_numeric($id)) {
   	  		$field = 'id';
   	  	}else{
   	  		$field = 'username';
   	  	}
   	  	
     	$user = $dbase->getRowByKey(TBL_USERS, $field, $id);
     	$user["country"] = $dbase->getCountry($user['country']);
     	$user["is_online"] = $this->isOnline($user["timestamp"]);
     	return $user;
	}
	

	function removeUser($id) {
		global $dbase;
		
		$picture = $dbase->getUserField($id, 'picture');
		
		if(file_exists(BASE_PATH.DIR_PROFILE_PICS.$picture))
           unlink(BASE_PATH.DIR_PROFILE_PICS.$picture);

		$PL = newClass("Playlists");
		$PL->removeUserPlaylists($id);
		         
		$dbase->delete(TBL_USERS, 'id', $id);
		
	}
	function removeUserPicture($id) {
		global $dbase;
		
		$picture = $dbase->getUserField($id, 'picture');
		$sex = $dbase->getUserField($id, 'sex');
		
		if(file_exists(BASE_PATH.DIR_PROFILE_PICS.$picture))
           unlink(BASE_PATH.DIR_PROFILE_PICS.$picture);
           
        $dbase->updateUserField($id,"picture","");
        
        return "profile_".$sex.".gif";   

	}
	
	function isOnline($lastlogin) {
		return ($lastlogin > (time()-900));
	}
}

?>