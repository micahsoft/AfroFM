<?
/**
 * Session.php
 * 
 * The Session class is meant to simplify the task of keeping
 * track of logged in users and also guests.
 *
 * Written by: Prismosoft.com
 * Last Updated: January 2nd, 2010
 */

class Session
{
   var $username;     //Username given on sign-up
   var $id;           //Random value generated on current login
   var $userlevel;    //The level to which the user pertains
   var $user_language; //User chosen language
   var $time;         //Time user was last active (page loaded)
   var $expire = 30;  // session expiration in days
   var $logged_in;    //True if user is logged in, false otherwise
   var $userinfo = array();  //The array holding all user info
   var $url;          //The page url current being viewed
   var $referrer;     //Last recorded site page viewed
   var $language;     //Current Language
   var $template;     //Current template
   var $theme;        //Current theme
   var $userip;
   var $adminMode = false;
   var $endPath = '';
   var $adminUrl = '';
   
   /**
    * Note: referrer should really only be considered the actual
    * page referrer in process.php, any other time it may be
    * inaccurate.
    */

   /* Class constructor */
   function Session(){
      $this->time = time();
      $this->expire = $this->expire * 86400;
      $this->startSession();
      $this->setTheme();
      $this->setLanguage();
   }

   /**
    * startSession - Performs all the actions necessary to 
    * initialize this session object. Tries to determine if the
    * the user has logged in already, and sets the variables 
    * accordingly. Also takes advantage of this page load to
    * update the active visitors tables.
    */
   function startSession(){
      global $dbase;  //The database connection
      session_set_cookie_params($this->expire);
      session_start();   //Tell PHP to start the session

      /* check if admin mode */
	  if(ADMIN_MODE) {
	  	 $this->adminMode = true;
	     $this->endPath  = ADMIN_PATH;
	     $this->adminUrl = (SEO_ENABLED) ? ADMIN_SEO_URL : ADMIN_URL;
	    
	  }else{
	  	 $this->adminMode = false;
	     $this->endPath  = 'site/';
	     $this->adminUrl = '';
	  }

      /* Determine if user is logged in */
      $this->logged_in = $this->checkLogin();


      // Set guest value to users not logged in, and update
      // active guests table accordingly.

      
      $this->userip = $_SESSION['IP'] = $this->visitorIP();
      if(!$this->logged_in){
         $this->username = $_SESSION['username'] = GUEST_NAME;
         $this->userlevel = GUEST_LEVEL;
         $dbase->addActiveGuest($this->userip, $this->time);
      }

      
      // Update users last active timestamp 
      else{
         $dbase->addActiveUser($this->id, $this->time);
      }

      // Remove inactive visitors from database 
      $dbase->removeInactiveUsers();
      $dbase->removeInactiveGuests();
      
      
            
      /* Set referrer page */
      if(isset($_SESSION['url'])){
         	$this->referrer = $_SESSION['url'];
      }else{
         $this->referrer = "/";
      }

      /* Set current url */
      if(!isset($_GET["ajax"]) && !isset($_POST["ajax"]))
      	$this->url = $_SESSION['url'] = (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : "");
   }
    /**
    * Visitor IP - get user IP address
    */
    function visitorIP()
    {
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $IP=$_SERVER['HTTP_X_FORWARDED_FOR'];
        else $IP=$_SERVER['REMOTE_ADDR'];

        return trim($IP);
    }
    /**
    * language - get current language and check if change is requested
    */
   function setLanguage() {
		global $func, $dbase;

		if($this->logged_in && isset($this->user_language) && $this->user_language != '') {
		
			 
			 if (isset($_GET['lang'])) {
				$clang = $_GET['lang'];
				$dbase->updateUserField($this->id,"language",$clang);
			 }else{
			 	$this->language = $_COOKIE['clang'] = $this->user_language;
			 }
			
		}else{
	  	
			if (isset($_GET['lang'])) {
				$clang = $_GET['lang'];
				setcookie('clang',$clang,0,'/');
			} else if (isset($_COOKIE['clang'])) {
				$clang = $_COOKIE['clang'];
			}else if($this->user_language != '') {   
				$clang = $this->user_language;
			} else {
				$clang = DEFAULT_LANGUAGE;
			}
	
			$this->language = $_COOKIE['clang'] = $clang;	
		}

		if(isset($_GET['lang'])) {
			$func->redirect($this->referrer, "", "", true);
			exit();
		}
   }

      
   /**
    * setTheme - Set default theme and template and check if change requested
    */
   function setTheme(){
      global $func;

	  if(!$this->adminMode) {

      	// set ctemplate
      	$containFiles = $func->containFiles(BASE_PATH . "templates_c/");
      	if (isset($_GET['tpl'])) {
            $func->deleteFiles(BASE_PATH . "templates_c/");
            $ctemplate = $_GET['tpl'];
            setcookie('ctemplate',$ctemplate,0,'/');
      	} else if (isset($_COOKIE['ctemplate']) && $containFiles) {
            $ctemplate = $_COOKIE['ctemplate'];
      	} else {
            $ctemplate = ACTIVE_TEMPLATE;
            setcookie('ctemplate',$ctemplate,0,'/');
      	}

      	if (isset($_GET['theme'])) {
            $func->deleteFiles(BASE_PATH . "templates_c/");
            $ctheme = $_GET['theme'];
            setcookie('ctheme',$ctheme,0,'/');
      	} else if (isset($_COOKIE['ctheme']) && $containFiles) {
            $ctheme = $_COOKIE['ctheme'];
      	} else {
            $ctheme = ACTIVE_THEME;
            setcookie('ctheme',$ctheme,0,'/');
      	}

      	$this->template = $_COOKIE['ctemplate'] = $ctemplate;
      	$this->theme = $_COOKIE['ctheme'] = $ctheme;
   
      }else{
      
      	$this->template = $_COOKIE['ctemplate'] = '';
      	$this->theme = $_COOKIE['ctheme'] = '';
      }
   }

   /**
    * checkLogin - Checks if the user has already previously
    * logged in, and a session with the user has already been
    * established. Also checks to see if user has been remembered.
    * If so, the database is queried to make sure of the user's 
    * authenticity. Returns true if the user has logged in.
    */
   function checkLogin(){
      global $dbase;  //The database connection
      /* Check if user has been remembered */
      if(isset($_COOKIE['cookname']) && isset($_COOKIE['cookid'])){
         $this->username = $_SESSION['username'] = $_COOKIE['cookname'];
         $this->id   = $_SESSION['id']   = $_COOKIE['cookid'];
      }
      //echo "1";
      /* Username and id have been set and not guest */
      if(isset($_SESSION['username']) && isset($_SESSION['id']) && $_SESSION['username'] != GUEST_NAME){
         //echo "2";
         /* Confirm that username and id are valid */
         if($dbase->confirmUserID($_SESSION['username'], $_SESSION['id']) != 0){
            /* Variables are incorrect, user not logged in */
            //echo "3";
            unset($_SESSION['username']);
            unset($_SESSION['id']);
            return false;
         }
         //echo "4";
         /* User is logged in, set class variables */
         $this->userinfo  = $dbase->getUserInfo($_SESSION['id']);
         $this->username  = $this->userinfo['username'];
         $this->id    = $this->userinfo['id'];
         $this->userlevel = $this->userinfo['userlevel'];
         $this->user_language = $this->userinfo['language'];
         return true;
      }
      /* User not logged in */
      else{
         //echo "5";
         return false;
      }
   }

   /**
    * login - The user has submitted his username and password
    * through the login form, this function checks the authenticity
    * of that information in the database and creates the session.
    * Effectively logging in the user if all goes well.
    */
   function login($subuser, $subpass, $subremember){
      global $dbase, $form;  //The database and form object

      /* Username error checking */
      $field = "login_username";  //Use field name for username
      if(!$subuser || strlen($subuser = trim($subuser)) == 0){
         $form->setError($field, "* Username not entered");
      }
      else{
         /* Check if username is not alphanumeric */
         if(!eregi("^([0-9a-z])*$", $subuser)){
            $form->setError($field, "* Username not alphanumeric");
         }
      }

      /* Password error checking */
      $field = "login_password";  //Use field name for password
      if(!$subpass){
         $form->setError($field, "* Password not entered");
      }
      
      /* Return if form errors exist */
      if($form->num_errors > 0){
         return false;
      }

      /* Checks that username is in database and password is correct */
      $subuser = stripslashes($subuser);
      $result = $dbase->confirmUserPass($subuser, md5($subpass));

      /* Check error codes */
      if($result == 1){
         $field = "login_username";
         $form->setError($field, "* Username not found");
      }
      else if($result == 2){
         $field = "login_password";
         $form->setError($field, "* Invalid password");
      }
      
      /* Return if form errors exist */
      if($form->num_errors > 0){
         return false;
      }

      /* Username and password correct, register session variables */
      $this->userinfo  = $dbase->getUserInfo($subuser);

	  $this->id  = $_SESSION['id'] = $this->userinfo['id'];
      $this->username  = $_SESSION['username'] = $this->userinfo['username'];
      $this->userlevel = $this->userinfo['userlevel'];
      
      /* Insert id into database and update active users table */
      $dbase->addActiveUser($this->id, $this->time);
      $dbase->removeActiveGuest($_SERVER['REMOTE_ADDR']);

      /**
       * This is the cool part: the user has requested that we remember that
       * he's logged in, so we set two cookies. One to hold his username,
       * and one to hold his random value id. It expires by the time
       * specified in constants.php. Now, next time he comes to our site, we will
       * log him in automatically, but only if he didn't log out before he left.
       */
      if($subremember){
         setcookie("cookname", $this->username, time()+COOKIE_EXPIRE, COOKIE_PATH);
         setcookie("cookid",   $this->id,   time()+COOKIE_EXPIRE, COOKIE_PATH);
      }

      /* Login completed successfully */
      return true;
   }

   /**
    * logout - Gets called when the user wants to be logged out of the
    * website. It deletes any cookies that were stored on the users
    * computer as a result of him wanting to be remembered, and also
    * unsets session variables and demotes his user level to guest.
    */
   function logout(){
      global $dbase;  //The database connection
      /**
       * Delete cookies - the time must be in the past,
       * so just negate what you added when creating the
       * cookie.
       */
      if(isset($_COOKIE['cookname']) && isset($_COOKIE['cookid'])){
         setcookie("cookname", "", time()-COOKIE_EXPIRE, COOKIE_PATH);
         setcookie("cookid",   "", time()-COOKIE_EXPIRE, COOKIE_PATH);
      }

      /* Unset PHP session variables */
      unset($_SESSION['username']);
      unset($_SESSION['id']);
      unset($_SESSION["userInfo"]);

      /* Reflect fact that user has logged out */
      $this->logged_in = false;
      
      /**
       * Remove from active users table and add to
       * active guests tables.
       */
      if($this->id) { 
      	$dbase->removeActiveUser($this->id);
      }
      $dbase->addActiveGuest($_SERVER['REMOTE_ADDR'], $this->time);
      
      /* Set user level to guest */
      $this->id = null;
      $this->username  = GUEST_NAME;
      $this->userlevel = GUEST_LEVEL;
   }

   /**
    * register - Gets called when the user has just submitted the
    * registration form. Determines if there were any errors with
    * the entry fields, if so, it records the errors and returns
    * 1. If no errors were found, it registers the new user and
    * returns 0. Returns 2 if registration failed.
    */
   function register($profileInfo){
      global $dbase, $form, $mailer;  //The database, form and mailer object
      
      $subuser = $profileInfo["username"];
      $subpass = $profileInfo["password"];
      $subpass2 = $profileInfo["password2"];
      $subemail = $profileInfo["email"];
      $country = $profileInfo["country"];
      $sex = $profileInfo["sex"];
      $bday = $profileInfo["bday"];
      
      /* Username error checking */
      $field = "username";  //Use field name for username
      if(!$subuser || strlen($subuser = trim($subuser)) == 0){
         $form->setError($field, "* Username not entered");
      }
      else{
         /* Spruce up username, check length */
         $subuser = stripslashes($subuser);
         if(strlen($subuser) < 5){
            $form->setError($field, "* Username below 5 characters");
         }
         else if(strlen($subuser) > 15){
            $form->setError($field, "* Username above 15 characters");
         }
         /* Check if username is not alphanumeric */
         else if(!eregi("^([0-9a-z])+$", $subuser)){
            $form->setError($field, "* Username not alphanumeric");
         }
         /* Check if username is reserved */
         else if(strcasecmp($subuser, GUEST_NAME) == 0){
            $form->setError($field, "* Username reserved word");
         }
         /* Check if username is already in use */
         else if($dbase->usernameTaken($subuser)){
            $form->setError($field, "* Username already in use");
         }
         /* Check if username is banned */
         else if($dbase->usernameBanned($subuser)){
            $form->setError($field, "* Username banned");
         }
      }

      /* Password error checking */
      $field = "password";  //Use field name for password
      if(!$subpass){
         $form->setError($field, "* Password not entered");
      }
      else{
         /* Spruce up password and check length*/
         $subpass = stripslashes($subpass);
         if(strlen($subpass) < 4){
            $form->setError($field, "* Password too short");
         }
         /* Check if password is not alphanumeric */
         else if(!eregi("^([0-9a-z])+$", ($subpass = trim($subpass)))){
            $form->setError($field, "* Password not alphanumeric");
         }
         /**
          * Note: I trimmed the password only after I checked the length
          * because if you fill the password field up with spaces
          * it looks like a lot more characters than 4, so it looks
          * kind of stupid to report "password too short".
          */
      }
      $field = "password2";  //Use field name for password
      if(!$subpass2){
         $form->setError($field, "* Password confirm not entered");
      }else if($subpass != $subpass2){
            $form->setError($field, "* Passwords does not match");
      }

      /* Email error checking */
      $field = "email";  //Use field name for email
      if(!$subemail || strlen($subemail = trim($subemail)) == 0){
         $form->setError($field, "* Email not entered");
      }
      else{
         /* Check if valid email address */
         $regex = "^[_+a-z0-9-]+(\.[_+a-z0-9-]+)*"
                 ."@[a-z0-9-]+(\.[a-z0-9-]{1,})*"
                 ."\.([a-z]{2,}){1}$";
         if(!eregi($regex,$subemail)){
            $form->setError($field, "* Email invalid");
         }
         $subemail = stripslashes($subemail);

         if($dbase->emailTaken($subemail)){
            $form->setError($field, "* Email already in use");
         }
      }


      /* Country error checking */
      $field = "country";  //Use field name for country
      if(!$country){
         $form->setError($field, "* Country not selected");
      }
      
      /* Sex error checking */
      $field = "sex";  //Use field name for bday
      if(!$sex){
         $form->setError($field, "* Please choose...");
      }
      
      /* Bday error checking */
      $field = "bday";  //Use field name for bday
      if(!$bday){
         $form->setError($field, "* Please enter your birthdate");
      }

      /* Errors exist, have user correct them */
      if($form->num_errors > 0){
         return 1;  //Errors with form
      }
      /* No errors, add the new account to the */
      else{
         if($dbase->addNewUser($subuser, md5($subpass), $subemail, $country, $sex, $bday)){
            if(!ADMIN_MODE && EMAIL_WELCOME){
               $mailer->sendWelcome($subuser,$subemail,$subpass);
            }
            return 0;  //New user added succesfully
         }else{
            return 2;  //Registration attempt failed
         }
      }
   }
   
   /**
    * editAccount - Attempts to edit the user's account information
    * including the password, which it first makes sure is correct
    * if entered, if so and the new password is in the right
    * format, the change is made. All other fields are changed
    * automatically.
    */
   function editAccount($profileInfo){
      global $dbase, $form;  //The database and form object
      
      $subcurpass = $profileInfo["curpassword"];
      $subnewpass = $profileInfo["newpassword"];
      $subemail = $profileInfo["email"];
      $country = $profileInfo["country"];
      $sex = $profileInfo["sex"];
      $bday = $profileInfo["bday"];
      $language = $profileInfo["language"];
      if($language == '---') 
      	$language = '';
      $about = $profileInfo["about"];
      
      /* New password entered */
      if($subnewpass){
         /* Current Password error checking */
         $field = "curpassword";  //Use field name for current password
         if(!$subcurpass){
            $form->setError($field, "* Current Password not entered");
         }
         else{
            /* Check if password too short or is not alphanumeric */
            $subcurpass = stripslashes($subcurpass);
            if(strlen($subcurpass) < 4 ||
               !eregi("^([0-9a-z])+$", ($subcurpass = trim($subcurpass)))){
               $form->setError($field, "* Current Password incorrect");
            }
            /* Password entered is incorrect */
            if($dbase->confirmUserPass($this->username,md5($subcurpass)) != 0){
               $form->setError($field, "* Current Password incorrect");
            }
         }
         
         /* New Password error checking */
         $field = "newpassword";  //Use field name for new password
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
      /* Change password attempted */
      else if($subcurpass){
         /* New Password error reporting */
         $field = "newpassword";  //Use field name for new password
         $form->setError($field, "* New Password not entered");
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
      if($subcurpass && $subnewpass){
         $dbase->updateUserField($this->id,"password",md5($subnewpass));
      }
      
      /* Change Email */
      if($subemail){
         $dbase->updateUserField($this->id,"email",$subemail);
      }

      /* Change Country */
      if($country){
         $dbase->updateUserField($this->id,"country",$country);
      }

      /* Change Bday */
      if($bday){
         $dbase->updateUserField($this->id,"bday",$bday);
      }
      
      /* Change Bday */
      if($sex){
         $dbase->updateUserField($this->id,"sex",$sex);
      }
      
      /* Change Language */
   	  $dbase->updateUserField($this->id,"language",$language);
      
      /* Change About */
      $dbase->updateUserField($this->id,"about",$about);
      


      /* Success! */
      return true;
   }

   function changePicture($subpicture, &$user=false){
      global $dbase, $form, $func; //The database and form object

	  if($user) {
	  	$username = $user["username"];
	  	$uid = $user["id"];
	  }else{
	  	$username = $this->username;
	  	$uid = $this->id;
	  }

      $field = "picture";
      if(!$subpicture["name"]){
         $form->setError($field, "* Picture not selected");
      }else{

         //define a maxim size for the uploaded images in Kb
         define ("MAX_SIZE","2000");



         //This variable is used as a flag. The value is initialized with 0 (meaning no error  found)
         //and it will be changed to 1 if an errro occures.
         //If the error occures the file will not be uploaded.
         $errors=0;

         //reads the name of the file the user submitted for uploading
         $image=$subpicture['name'];
         //if it is not empty
         if ($image)
         {
                //get the original name of the file from the clients machine
                $filename = stripslashes($subpicture['name']);
                //get the extension of the file in a lower case format
                $extension = $func->getExtension($filename);
                $extension = strtolower($extension);

                //if it is not a known extension, we will suppose it is an error and will not  upload the file,
                //otherwise we will do more tests
                if (($extension != "jpg") && ($extension != "jpeg") && ($extension != "png") && ($extension != "gif"))
                {
                    //print error message
                    $form->setError($field, "* Unknown extension!");
                }
                else
                {
                    //get the size of the image in bytes
                    //$_FILES['image']['tmp_name'] is the temporary filename of the file
                    //in which the uploaded file was stored on the server
                    $size=filesize($subpicture['tmp_name']);

                    //compare the size with the maxim size we defined and print error if bigger
                    if ($size > MAX_SIZE*1024)
                    {
                            $form->setError($field, "You have exceeded the size limit!");
                    }

                    //we will give an unique name, for example the time in unix time format
                    $image_name=md5($username.time()).'.'.$extension;
                    //the new name will be containing the full path where will be stored (images folder)
                    $this->removePicture(false, false, $user);
                    $newname=BASE_PATH.DIR_PROFILE_PICS.$image_name;
                    //we verify if the image has been uploaded, and print error instead
                    $copied = copy($subpicture['tmp_name'], $newname);

                    if (!$copied)
                    {
                        $form->setError($field, "Copy unsuccessfull!");
                    }
                }


            }
      }

      /* Errors exist, have user correct them */
      if($form->num_errors > 0){
         return false;  //Errors with form
      }

      /* Change Picture */
      if($subpicture && $copied){
         $dbase->updateUserField($uid,"picture",$image_name);
         if(!$user)
         	$this->reloadUserInfo();
      }

      /* Success! */
      return true;

   }

   function removePicture($fromDB = true, $reloadUserInfo = true, &$user=false){
      global $dbase;
      
      if($user) {
	  	$username = $user["username"];
	  	$uid = $user["id"];
	  }else{
	  	$username = $this->username;
	  	$uid = $this->id;
	  }
	  
      $currentPicture = $dbase->getUserField($uid, "picture");

      if(file_exists(BASE_PATH.DIR_PROFILE_PICS.$currentPicture))
           unlink(BASE_PATH.DIR_PROFILE_PICS.$currentPicture);
      
      if($fromDB)
 		$dbase->updateUserField($uid,"picture","");
       		
	  if($reloadUserInfo && !$user)
		$this->reloadUserInfo();
		
       	/* Success! */
       	return true;
   }
   
   function reloadUserInfo() {
    	global $dbase;
   		$this->userinfo = $dbase->getUserInfo($this->id);
   }
   
   /**
    * isAdmin - Returns true if currently logged in user is
    * an administrator, false otherwise.
    */
   function isAdmin(){
      return ($this->userlevel == ADMIN_LEVEL ||
              $this->username  == ADMIN_NAME);
   }
   
   /**
    * generateRandID - Generates a string made up of randomized
    * letters (lower and upper case) and digits and returns
    * the md5 hash of it to be used as a id.
    */
   function generateRandID(){
      return md5($this->generateRandStr(16));
   }
   
   /**
    * generateRandStr - Generates a string made up of randomized
    * letters (lower and upper case) and digits, the length
    * is a specified parameter.
    */
   function generateRandStr($length){
      $randstr = "";
      for($i=0; $i<$length; $i++){
         $randnum = mt_rand(0,61);
         if($randnum < 10){
            $randstr .= chr($randnum+48);
         }else if($randnum < 36){
            $randstr .= chr($randnum+55);
         }else{
            $randstr .= chr($randnum+61);
         }
      }
      return $randstr;
   }
   
    /**
    * isRestricted - Checks if user is logged in and if he can access
    * a specific location.
    */
   function isRestricted($location) {

        global $restrictedLoc;
        $locData = explode(".", $location);
        $module = $locData[0];

		if(!$this->adminMode){
			
        	if($this->logged_in) {
        	    $restricted = false;
        	}else{
        	    if(in_array($location,$restrictedLoc) || in_array($module,$restrictedLoc))
        	        $restricted = true;
        	    else
        	        $restricted = false;
        	}
        }else{
        	if($this->logged_in && $this->isAdmin())
        		$restricted = false;
        	else
        		$restricted = true;
        }
        return $restricted;	
    }

    /**
    * isSessionUser - Checks if user is the same as logged in user
    */
    function isSessionUser($user) {

        return ($user == $this->username) ? true : false;
    }
};

?>