<?php

class Dbase
{
   var $db;         //The MySQL database connection
   var $num_active_users;   //Number of active users viewing site
   var $num_active_guests;  //Number of active guests viewing site
   var $num_members;        //Number of signed-up users
   var $installed = false;
   var $use_pdo = true;

   /* Note: call getNumMembers() to access $num_members! */

   /* Class constructor */
   function Dbase($info = null){

	  if($this->use_pdo) {
	  
	   	  try{
	      		$this->connectWithPDO($info);
		
		  } catch (PDOException $e) {
		  
				$this->use_pdo = false;
				$this->connect($info);
		  }
		  
	  }else{
	  		$this->use_pdo = false;
	  		$this->connect($info);
	  }

      /**
       * Only query database to find out number of members
       * when getNumMembers() is called for the first time,
       * until then, default value set.
       */
      $this->num_members = -1;

      if(TRACK_VISITORS && $this->installed){
         /* Calculate number of users at site */
         $this->calcNumActiveUsers();

         /* Calculate number of guests at site */
         $this->calcNumActiveGuests();
      }
   }
 
   function connect($info = null) {

  		if($info) {
			$this->db = mysql_connect($info["DB_HOST"], $info["DB_USER"], $info["DB_PASS"]) or die(mysql_error());
  			mysql_select_db($info["DB_NAME"], $this->db) or die(mysql_error());
		}else{
			$this->db = mysql_connect(DB_HOST, DB_USER, DB_PASS) or die(mysql_error());
  			mysql_select_db(DB_NAME, $this->db) or die(mysql_error());
		}   
   }
      
   function connectWithPDO($info = null) {
   
	      	/* Make connection to database */
      	if($info) 
      		$this->db = new PDO("mysql:host=".$info["DB_HOST"].";dbname=".$info["DB_NAME"], $info["DB_USER"], $info["DB_PASS"]);
	  	else	
			$this->db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);  
   }
   
   function defineSettings() {
		$query = "SELECT * FROM ".TBL_SETTINGS."";
		$result = $this->query($query);
		while($setting = $this->fetch($result)) {
     		define($setting["key"], $setting["value"]); 
      	}
   }

   /**
    * confirmUserPass - Checks whether or not the given
    * username is in the database, if so it checks if the
    * given password is the same password in the database
    * for that user. If the user doesn't exist or if the
    * passwords don't match up, it returns an error code
    * (1 or 2). On success it returns 0.
    */
   function confirmUserPass($username, $password){
      /* Add slashes if necessary (for query) */
      if(!get_magic_quotes_gpc()) {
	      $username = addslashes($username);
      }

      /* Verify that user is in database */
      $q = "SELECT password FROM ".TBL_USERS." WHERE username = '$username'";
      $result = $this->query($q);
      if(!$result || ($this->num_rows($result) < 1)){
         return 1; //Indicates username failure
      }

      /* Retrieve password from result, strip slashes */
      $dbarray = $this->fetch($result);
      $dbarray['password'] = stripslashes($dbarray['password']);
      $password = stripslashes($password);

      /* Validate that password is correct */
      if($password == $dbarray['password']){
         return 0; //Success! Username and password confirmed
      }
      else{
         return 2; //Indicates password failure
      }
   }

   /**
    * confirmUserID - Checks whether or not the given
    * username is in the database, if so it checks if the
    * given id is the same id in the database
    * for that user. If the user doesn't exist or if the
    * ids don't match up, it returns an error code
    * (1 or 2). On success it returns 0.
    */
   function confirmUserID($username, $id){
      /* Add slashes if necessary (for query) */
      if(!get_magic_quotes_gpc()) {
	      $username = addslashes($username);
      }

      /* Verify that user is in database */
      $q = "SELECT id FROM ".TBL_USERS." WHERE username = '$username'";
      $result = $this->query($q);
      if(!$result || ($this->num_rows($result) < 1)){
         return 1; //Indicates username failure
      }

      /* Retrieve id from result, strip slashes */
      $dbarray = $this->fetch($result);
      $dbarray['id'] = stripslashes($dbarray['id']);
      $id = stripslashes($id);

      /* Validate that id is correct */
      if($id == $dbarray['id']){
         return 0; //Success! Username and id confirmed
      }
      else{
         return 2; //Indicates id invalid
      }
   }

   /**
    * usernameTaken - Returns true if the username has
    * been taken by another user, false otherwise.
    */
   function usernameTaken($username){
      if(!get_magic_quotes_gpc()){
         $username = addslashes($username);
      }
      $q = "SELECT username FROM ".TBL_USERS." WHERE username = '$username'";
      $result = $this->query($q);
      return ($this->num_rows($result) > 0);
   }

   /**
    * emailTaken - Returns true if the email has
    * been used by another user, false otherwise.
    */
   function emailTaken($email){
      if(!get_magic_quotes_gpc()){
         $email = addslashes($email);
      }
      $q = "SELECT email FROM ".TBL_USERS." WHERE email = '$email'";
      $result = $this->query($q);
      return ($this->num_rows($result) > 0);
   }

   /**
    * usernameBanned - Returns true if the username has
    * been banned by the administrator.
    */
   function usernameBanned($username){

      $q = "SELECT u.username FROM ".TBL_USERS." u JOIN ".TBL_USERS_BANNED." b ON u.id = b.id WHERE u.username = '$username'";
      $result = $this->query($q);
      return ($this->num_rows($result) > 0);
   }

   /**
    * addNewUser - Inserts the given (username, password, email)
    * info into the database. Appropriate user level is set.
    * Returns true on success, false otherwise.
    */
   function addNewUser($username, $password, $email, $country, $sex, $bday){
      /* If admin sign up, give admin user level */
      if(strcasecmp($username, ADMIN_NAME) == 0){
         $ulevel = ADMIN_LEVEL;
      }else{
         $ulevel = USER_LEVEL;
      }
      $q = "INSERT INTO ".TBL_USERS." (email, username, password, userlevel, country, sex, bday)
            VALUES ('$email', '$username', '$password', $ulevel, '$country', '$sex', '$bday')";
      return $this->exec($q);
   }

   /** 
    * updateUserField - Updates a field, specified by the field
    * parameter, in the user's row of the database.
    */
   function updateUserField($id, $field, $value){
   
   	  if(is_numeric($id)) {
   	  	$key = 'id';
   	  }else{
   	  	$key = 'username';
   	  }
   	  
      $q = "UPDATE ".TBL_USERS." SET ".$field." = '$value' WHERE ".$key." = '$id'";
      return $this->exec($q);
   }

   /**
    * getUserInfo - Returns the result array from a mysql
    * query asking for all information stored regarding
    * the given username. If query fails, NULL is returned.
    */
   function getUserInfo($user){
   	  
   	  if(is_numeric($user)) {
   	  	$field = 'id';
   	  }else{
   	  	$field = 'username';
   	  }
   	  
   	  if(!is_numeric($user))
   	  	$user = "'".$user."'";
      $q = "SELECT * FROM ".TBL_USERS." WHERE $field = $user";
      $result = $this->query($q);
      /* Error occurred, return given name by default */
      if(!$result || ($this->num_rows($result) < 1)){
         return NULL;
      }
      /* Return result array */
      $dbarray = $this->fetch($result);
      $dbarray["country"] = $this->getCountry($dbarray["country"]);
      $dbarray["is_online"] = $this->isOnline($dbarray["timestamp"]);
      $dbarray["timestamp"] = date("F j, Y, g:i a", $dbarray["timestamp"]);
      return $dbarray;
      
   }

    /**
    * getNewUsers - Returns the result array from a mysql
    * query asking for all new users. If query fails, NULL is returned.
    */
   function getNewUsers($max=5){
   	  
      $users = array();
      $q = "SELECT * FROM ".TBL_USERS." LIMIT ".$max."";
      $result = $this->query($q);
      /* Error occurred, return given name by default */
      if(!$result || ($this->num_rows($result) < 1)){
         return NULL;
      }
      /* Return result array */

      $i = 0;
      while($row = $this->fetch($result)) {
      		$users[$i]["id"] = $row['id'];
            $users[$i]["username"] = $row['username'];
            $users[$i]["country"] = $this->getCountry($row['country']);
            $users[$i]["picture"] = $row['picture'];
            $users[$i]["is_online"] = $this->isOnline($row["timestamp"]);
            $users[$i]["timestamp"] = formatDate($row['timestamp']);
            $i++;
      }
      return $users;

   }

   /**
    * getUserInfo - Returns the result array from a mysql
    * query asking for all information stored regarding
    * the given username. If query fails, NULL is returned.
    */
   function getUserField($id, $field){
   	  if(is_numeric($id)) {
      	$q = "SELECT ".$field." FROM ".TBL_USERS." WHERE id = $id";
      }else{
      	$q = "SELECT ".$field." FROM ".TBL_USERS." WHERE username = '$id'";
      }
      $result = $this->query($q);
      /* Error occurred, return given name by default */
      if(!$result || ($this->num_rows($result) < 1)){
         return NULL;
      }
      /* Return result array */
      $dbarray = $this->fetch($result);
      return $dbarray[$field];
   }

   /**
    * getNumMembers - Returns the number of signed-up users
    * of the website, banned members not included. The first
    * time the function is called on page load, the database
    * is queried, on subsequent calls, the stored result
    * is returned. This is to improve efficiency, effectively
    * not querying the database when no call is made.
    */
   function getNumMembers(){
      if($this->num_members < 0){
         $q = "SELECT * FROM ".TBL_USERS;
         $result = $this->query($q);
         $this->num_members = $this->num_rows($result);
      }
      return $this->num_members;
   }

   /**
    * calcNumActiveUsers - Finds out how many active users
    * are viewing site and sets class variable accordingly.
    */
   function calcNumActiveUsers(){
      /* Calculate number of users at site */
      $q = "SELECT count(id) as count FROM ".TBL_USERS_ACTIVE;
      $result = $this->query($q);
      $row = $this->fetch($result);
      $this->num_active_users = $row['count'];
   }

   /**
    * calcNumActiveGuests - Finds out how many active guests
    * are viewing site and sets class variable accordingly.
    */
   function calcNumActiveGuests(){
      /* Calculate number of guests at site */
      $q = "SELECT count(ip) FROM ".TBL_GUESTS_ACTIVE;
      $result = $this->query($q);
      $row = $this->fetch($result);
      $this->num_active_guests = $row['count'];
   }

   /**
    * addActiveUser - Updates username's last active timestamp
    * in the database, and also adds him to the table of
    * active users, or updates timestamp if already there.
    */
   function addActiveUser($id, $time){
      $q = "UPDATE ".TBL_USERS." SET timestamp = '$time' WHERE id = $id";
      $this->exec($q);

      if(!TRACK_VISITORS) return;
      $q = "REPLACE INTO ".TBL_USERS_ACTIVE." VALUES ('$id', '$time')";
      $this->exec($q);
      $this->calcNumActiveUsers();
   }

   /* addActiveGuest - Adds guest to active guests table */
   function addActiveGuest($ip, $time){
      if(!TRACK_VISITORS) return;
      $q = "REPLACE INTO ".TBL_GUESTS_ACTIVE." VALUES ('$ip', '$time')";
      $this->exec($q);
      $this->calcNumActiveGuests();
   }

   /* These functions are self explanatory, no need for comments */

   /* removeActiveUser */
   function removeActiveUser($id){
      if(!TRACK_VISITORS) return;
      $q = "DELETE FROM ".TBL_USERS_ACTIVE." WHERE id = $id";
      $this->exec($q);
      $this->calcNumActiveUsers();
   }

   /* removeActiveGuest */
   function removeActiveGuest($ip){
      if(!TRACK_VISITORS) return;
      $q = "DELETE FROM ".TBL_GUESTS_ACTIVE." WHERE ip = '$ip'";
      $this->exec($q);
      $this->calcNumActiveGuests();
   }

   /* removeInactiveUsers */
   function removeInactiveUsers(){
      if(!TRACK_VISITORS) return;
      $timeout = time()-USER_TIMEOUT*60;
      $q = "DELETE FROM ".TBL_USERS_ACTIVE." WHERE timestamp < $timeout";
      $this->exec($q);
      $this->calcNumActiveUsers();
   }

   /* removeInactiveGuests */
   function removeInactiveGuests(){
      if(!TRACK_VISITORS) return;
      $timeout = time()-GUEST_TIMEOUT*60;
      $q = "DELETE FROM ".TBL_GUESTS_ACTIVE." WHERE timestamp < $timeout";
      $this->exec($q);
      $this->calcNumActiveGuests();
   }
   /* getCountries */
   function getCountries(){
      $q = "SELECT * FROM ".TBL_COUNTRIES;
      $result = $this->query($q);
      /* Error occurred, return given name by default */
      if(!$result){
         return NULL;
      }
      /* Return result array */
      $i = 0;
      while($row = $this->fetch($result)) {
          $countries[$i]["id"] = $row["id"];
          $countries[$i]["name"] = $row["name"];
          $i++;
      }
      return $countries;
   }

   function getCountry($country_id){
      $q = "SELECT * FROM ".TBL_COUNTRIES." WHERE id='".$country_id."'";
      $result = $this->query($q);
      /* Error occurred, return given name by default */
      if(!$result){
         return NULL;
      }
      /* Return result array */

      $row = $this->fetch($result);

      $country["id"] = $row["id"];
      $country["name"] = $row["name"];


      return $country;
   }

   /**
    * query - Performs the given query on the database and
    * returns the result, which may be false, true or a
    * resource identifier.
    */
   function query($query){
   
   		if($this->use_pdo) {
        	$result = $this->db->query($query);
	        if (!$result) {
	            die('Invalid query: <br>' . $query);
	        }else{
	            return $result;
	        }
	    }else{
	    	 return $this->mysql_query($query);
	    }    
   }

   function exec($query){
   
   		if($this->use_pdo) {
        	$this->db->exec($query);
        }else{
	    	$this->mysql_query($query);
	    } 
	    return true;
   }
   
   function fetch(&$result) {
   		if($this->use_pdo) {
	   		if($result)
	        	return $result->fetch(PDO::FETCH_ASSOC);
	        else
	        	return array();
	    }else{
	    	return $this->mysql_fetch($result);
	    }		
   }

   function mysql_query($query){
        $result = mysql_query($query, $this->db);
        if (!$result) {
            die('Invalid query: <br>' . $query. '<br>'. mysql_error());
        }else{
            return $result;
        }
   }
 
 
   function mysql_fetch($result) {

        return mysql_fetch_array($result);
   }
   
        
   function num_rows($result) {
   		if($this->use_pdo) {
   			return $result->rowCount();
   		}else{
   			return mysql_num_rows($result);
   		}
   }
   
   function count($table, $field, $condition=null) {
   		$q = "SELECT count(".$field.") as count FROM ".$table."";
   		if($condition)
   			$q .= " WHERE ".$condition;
   			
      	$result = $this->query($q);
      	$row = $this->fetch($result);
   		return $row['count'];
   }

   function getResultsByKey($table, $key, $value, $order=null, $group=null) {
   
   	  $results = array();
   	  
	  if(!is_numeric($value)) {
           $value = "'" . $this->db_escape_string($value) . "'";
      }

      $sql = "SELECT * FROM ".$table." WHERE ".$table.".".$key." = ".$value."";
      
      if(isset($order)) {
     		$sql .= ' ORDER BY '.$order['field'].' '.$order['direction'];
      }
      if(isset($group)) {
     		$sql .= ' GROUP BY '.$group['field'].' '.$group['direction'];
      }
      
      $result = $this->query($sql);

	  while($row = $this->fetch($result)) {
     	$results[] = $row;
      }

      if(is_array($results)) {
            return $results;
      }else{
            return null;
      }
   }

   function getResultsByAttr($table, $attributes, $order=null, $group=null) {
   
   	  $results = array();
   	  

      $sql = "SELECT * FROM ".$table."";
      $t = sizeof($attributes);
   	  $i = 0;
   	  if($t > 0)
   	  	$sql .= " WHERE ";
   	  	
   	  foreach($attributes as $key=>$value) {
		if($value){
   			if(!is_numeric($value)) {
            	$value = "'" . $this->db_escape_string($value) . "'";
        	}
        	$sql .= "$table.$key = $value";
        }else{
        	$value = "NULL";
        	$sql .= "$table.$key is $value";
        }
   		
   		if($i < ($t-1)){
   			$sql .= " AND ";
   		}
   		$i++;
   	  }
   	  if(isset($order)) {
     		$sql .= ' ORDER BY '.$order['field'].' '.$order['direction'];
      }
      if(isset($group)) {
     		$sql .= ' GROUP BY '.$group['field'].' '.$group['direction'];
      }
      echo $sql;
      $result = $this->query($sql);

	  while($row = $this->fetch($result)) {
     	$results[] = $row;
      }

      if(is_array($results)) {
            return $results;
      }else{
            return null;
      }
   }
      
   function getRowByKey($table, $key, $value) {
   
      if(!is_numeric($value)) {
           $value = "'" . $this->db_escape_string($value) . "'";
      }

      $query = "SELECT * FROM ".$table." WHERE ".$table.".".$key." = ".$value." LIMIT 1";
      $result = $this->query($query);
      $row = $this->fetch($result);

      if(is_array($row)) {
            return $row;
      }else{
            return null;
      }
   }

   function getRowByAttr($table, $attributes) {

      $sql = "SELECT * FROM ".$table." WHERE ";
      $t = sizeof($attributes);
   	  $i = 0;
   	  foreach($attributes as $key=>$value) {
   			if(!is_numeric($value)) {
            	$value = "'" . $this->db_escape_string($value) . "'";
        	}
   			$sql .= "$table.$key = $value";
   			if($i < ($t-1)){
   				$sql .= " AND ";
   			}
   			$i++;
   	  }
   	  $sql .= " LIMIT 1";
   	  
      $result = $this->query($sql);
      $row = $this->fetch($result);

      if(is_array($row)) {
            return $row;
      }else{
            return null;
      }
   }
   
   function getValueByKey($table, $field, $key, $value) {
      
      if(!is_numeric($value)) {
           $value = "'" . $this->db_escape_string($value) . "'";
      }

      $query = "SELECT ".$field." FROM ".$table." WHERE ".$table.".".$key." = ".$value." LIMIT 1";
      $result = $this->query($query);
      $row = $this->fetch($result);

      if(is_array($row)) {
            return $row[$field];
      }else{
            return null;
      }
   }
   

   function insertArray($table, $array, $type='INSERT') {

	$columns = array();
	$data = array();

	foreach ( $array as $key => $value) {

            if(is_array($value)) {
                if(sizeof($value) > 0){
                    $value = "'" . serialize($value) . "'";
                }else{
                    $value = "''";
                }
            }else if ($value != "") {
                if(!is_numeric($value)) {
                    $value = "'" . $this->db_escape_string($value) . "'";
                }
            } else {
		$value = "''";
            }

            $columns[] = $table.'.'.$key;
            $data[] = $value;
		
	}

	$cols = implode(",",$columns);
	$values = implode(",",$data);

$sql = <<<EOSQL
	$type INTO `$table`
	($cols)
	VALUES
	($values)
EOSQL;

       $this->exec($sql);

     
   }

   function delete($table, $key, $value, $limit=1) {
   		if(!is_numeric($value)) {
            $value = "'" . $this->db_escape_string($value) . "'";
        }
   		$sql = "DELETE FROM `$table` WHERE $key = $value";
   		if($limit)
   			$sql .= " LIMIT $limit";
   			
   		$this->exec($sql);
   }
   
   function deleteByAttr($table, $attributes, $limit=1) {
   
   		$sql = "DELETE FROM `$table` WHERE ";
   		$t = sizeof($attributes);
   		$i = 0;
   		foreach($attributes as $key=>$value) {
   			if(!is_numeric($value)) {
            	$value = "'" . $this->db_escape_string($value) . "'";
        	}
   			$sql .= "$key = $value";
   			if($i < ($t-1)){
   				$sql .= " AND ";
   			}
   			$i++;
   		}
   		if($limit)
   			$sql .= " LIMIT $limit";
   			
   		$this->exec($sql);
   }
   
   function updateArray($table, $array, $field, $value) {

        if(!is_numeric($value)) {
            $value = "'" . $this->db_escape_string($value) . "'";
        }

        $data = array();

		foreach ( $array as $key => $val) {
            if(is_array($val)) {
                if(sizeof($val) > 0){
                    $val = "'" . serialize($val) . "'";
                }else{
                    $val = "";
                }
            }else if ($val != "") {
                if(!is_numeric($val)) {
                    $val = "'" . $this->db_escape_string($val) . "'";
                }
            } else {
				$val = "''";
            }

            $data[] = $table.".".$key."=".$val;
		}

		$values = implode(",",$data);

        $sql = "UPDATE `$table` SET $values WHERE $field = $value";

        $this->exec($sql);

   }
   
   function updateArrayByAttr($table, $array, $attributes) {

        $data = array();

		foreach ( $array as $key => $val) {
            if(is_array($val)) {
                if(sizeof($val) > 0){
                    $val = "'" . serialize($val) . "'";
                }else{
                    $val = "";
                }
            }else if ($val != "") {
                if(!is_numeric($val)) {
                    $val = "'" . $this->db_escape_string($val) . "'";
                }
            } else {
				$val = "''";
            }

            $data[] = $table.".".$key."=".$val;
		}

		$values = implode(",",$data);

        $sql = "UPDATE `$table` SET $values WHERE ";
        $t = sizeof($attributes);
   		$i = 0;
   		foreach($attributes as $key=>$value) {
   			if(!is_numeric($value)) {
            	$value = "'" . $this->db_escape_string($value) . "'";
        	}
   			$sql .= "$key = $value";
   			if($i < ($t-1)){
   				$sql .= " AND ";
   			}
   			$i++;
   		}
        $this->exec($sql);
   }

   function updateValue($table, $updateField, $updateValue, $field, $value) {

		if(!is_numeric($value)) {
            $value = "'" . $this->db_escape_string($value) . "'";
        }
        if(!is_numeric($updateValue)) {
            $updateValue = "'" . $this->db_escape_string($updateValue) . "'";
        }
        
        $sql = "UPDATE `$table` SET $table.$updateField=$updateValue WHERE $field=$value";
        $this->exec($sql);
   }
   
   function updateValueByAttr($table, $updateField, $updateValue, $attributes) {

        if(!is_numeric($updateValue)) {
            $updateValue = "'" . $this->db_escape_string($updateValue) . "'";
        }
        
        $sql = "UPDATE `$table` SET $updateField=$updateValue WHERE ";
        $t = sizeof($attributes);
   		$i = 0;
   		foreach($attributes as $key=>$value) {
   			if(!is_numeric($value)) {
            	$value = "'" . $this->db_escape_string($value) . "'";
        	}
   			$sql .= "$table.$key = $value";
   			if($i < ($t-1)){
   				$sql .= " AND ";
   			}
   			$i++;
   		}
        $this->exec($sql);
   }
   
   function increaseValue($table, $increaseField, $increment, $field, $value) {

		if(!is_numeric($value)) {
            $value = "'" . $this->db_escape_string($value) . "'";
        }
        
        $sql = "UPDATE `$table` SET $table.$increaseField=$increaseField+$increment WHERE $field=$value";
        $this->exec($sql);
   }
   function decreaseValue($table, $increaseField, $increment, $field, $value) {

		if(!is_numeric($value)) {
            $value = "'" . $this->db_escape_string($value) . "'";
        }
        
        $sql = "UPDATE `$table` SET $table.$increaseField=$increaseField-$increment WHERE $field=$value";
        return $this->exec($sql);
   }

   function lastInsertedId($table, $key = "id") {
        $query = "SELECT ".$key." FROM ".$table." ORDER BY ".$table.".".$key." DESC LIMIT 1";
        $result = $this->query($query);
        $row = $this->fetch($result);

        if(is_array($row)) {
              return $row[$key];
        }else{
              return -1;
        }
   }
   
   function existsInField($table, $field, $search, $type = 'exact', $returnRow = true) {

       if($search == "")
           return false;

		if($type == "like") {
        	$query = "SELECT * FROM ".$table." WHERE ".$table.".".$field." LIKE '%".$search."%'";
        }else{
        	$query = "SELECT * FROM ".$table." WHERE ".$table.".".$field." = '".$search."'";
        }
        $result = $this->query($query);
        $row = $this->fetch($result);

		if($returnRow)
			return $row;
			
        if(is_array($row)) {
            return true;
        }else{
            return false;
        }
   } 

   function valueExistsByKey($table, $field, $key, $value, $returnValue = true) {
   
   	  if(!is_numeric($value)) {
           $value = "'" . $this->db_escape_string($value) . "'";
      }
        	
   	  $results = array();

      $sql = "SELECT ".$field." FROM ".$table." WHERE $table.$key=$value LIMIT 1";

      $result = $this->query($sql);
      $row = $this->fetch($result);

      if(is_array($row)) {
            if($returnValue)
				return $row[$field];
			return true;
      }else{
            return false;
      }
   }
   
   function valueExistsByAttr($table, $field, $attributes, $returnValue = true) {
   
   	  $results = array();

      $sql = "SELECT ".$field." FROM ".$table." WHERE ";
      $t = sizeof($attributes);
   	  $i = 0;
   	  foreach($attributes as $key=>$value) {
   			if(!is_numeric($value)) {
            	$value = "'" . $this->db_escape_string($value) . "'";
        	}
   			$sql .= "$table.$key = $value";
   			if($i < ($t-1)){
   				$sql .= " AND ";
   			}
   			$i++;
   	  }
   	  $sql .= ' LIMIT 1';

      $result = $this->query($sql);
      $row = $this->fetch($result);

      if(is_array($row)) {
            if($returnValue)
				return $row[$field];
			return true;
      }else{
            return false;
      }
   }
   
   function rowExistsByKey($table, $key, $value, $returnRow = true) {
   
   	  if(!is_numeric($value)) {
           $value = "'" . $this->db_escape_string($value) . "'";
      }
        	
   	  $results = array();

      $sql = "SELECT * FROM ".$table." WHERE $table.$key=$value LIMIT 1";

      $result = $this->query($sql);
      $row = $this->fetch($result);

      if(is_array($row)) {
            if($returnRow)
				return $row;
			return true;
      }else{
            return false;
      }
   }
      
   function rowExistsByAttr($table, $attributes, $returnRow = true) {
   
   	  $results = array();

      $sql = "SELECT * FROM ".$table." WHERE ";
      $t = sizeof($attributes);
   	  $i = 0;
   	  foreach($attributes as $key=>$value) {
   			if(!is_numeric($value)) {
            	$value = "'" . $this->db_escape_string($value) . "'";
        	}
   			$sql .= "$table.$key = $value";
   			if($i < ($t-1)){
   				$sql .= " AND ";
   			}
   			$i++;
   	  }
   	  $sql .= ' LIMIT 1';

      $result = $this->query($sql);
      $row = $this->fetch($result);

      if(is_array($row)) {
            if($returnRow)
				return $row;
			return true;
      }else{
            return false;
      }
   }
   
   function tableExist($table) {

            $result = $this->query("show tables like '$table'");

            if ($this->num_rows($result)>0)
                    return true;
            else
                    return false;
   }

   function isOnline($lastlogin) {
		return ($lastlogin > (time()-900));
   }
   
   
   function db_escape_string($string) {
		if ( get_magic_quotes_gpc() ) {
			$string = stripslashes($string);
		}
		if(!$this->use_pdo) {
			$string = mysql_real_escape_string($string);
		}else{
			$string = mysql_escape_string($string);
		}
		return $string;
   }
   
   function close() {
   	if($this->use_pdo)
   		$this->db = null;
   	else
   		mysql_close($this->db);
   }   
   
};

?>