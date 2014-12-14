<?
/**
 * Process.php
 * 
 * The Process class is meant to simplify the task of processing
 * username submitted forms, redirecting the username to the correct
 * pages if errors are found, or if form is successful, either
 * way. Also handles the logout procedure.
 *
 * Written by: Jpmaster77 a.k.a. The Grandmaster of C++ (GMC)
 * Last Updated: August 19, 2004
 */

class Process
{
   /* Class constructor */
   function Process(){
      global $session;
      
      /* User submitted login form */
      if(isset($_POST['sublogin'])){
         $this->procLogin();
      }
      /* User submitted registration form */
      else if(isset($_POST['subjoin'])){
         $this->procRegister();
      }
      /* User submitted forgot passwordword form */
      else if(isset($_POST['subforgot'])){
         $this->procForgotPass();
      }
      /* User submitted edit account form */
      else if(isset($_POST['subsettings']) && $session->logged_in){
         $this->procEditAccount();
      }
      /* User submitted change picture  form */
      else if(isset($_POST['subpicture']) && $session->logged_in){
         $this->procChangePicture();
      }
      /* User requested remove picture  */
      else if(isset($_POST['rempicture']) && $session->logged_in){
         $this->procRemovePicture();
      }
      /**
       * The only other reason username should be directed here
       * is if he wants to logout, which means username is
       * logged in currently.
       */
      else if(isset($_GET['logout'])){
         $this->procLogout();
      }

   }

   /**
    * procLogin - Processes the username submitted login form, if errors
    * are found, the username is redirected to correct the information,
    * if not, the username is effectively logged in to the system.
    */
   function procLogin(){
      global $session, $form;
      /* Login attempt */
      $retval = $session->login($_POST['login_username'], $_POST['login_password'], isset($_POST['remember']));

      /* Login successful */
      if($retval){
         $_SESSION['logsuccess'] = true;
      }
      /* Login failed */
      else{
         $_SESSION['logsuccess'] = false;
         $form->values = $_POST;
         $form->errors = $form->getErrorArray();
      }
   }
   
   /**
    * procLogout - Simply attempts to log the username out of the system
    * given that there is no logout form to process.
    */
   function procLogout(){ 
      global $session;
      $retval = $session->logout();
      header("Location: ".$session->referrer);
   }
   
   /**
    * procRegister - Processes the username submitted registration form,
    * if errors are found, the username is redirected to correct the
    * information, if not, the username is effectively registered with
    * the system and an email is (optionally) sent to the newly
    * created username.
    */
   function procRegister(){
      global $session, $form;
      /* Convert usernamename to all lowercase (by option) */
      if(ALL_LOWERCASE){
         $_POST['username'] = strtolower($_POST['username']);
      }
      /* Registration attempt */
      $retval = $session->register($_POST);

      /* Registration Successful */
      if($retval == 0){
         $_SESSION['reguname'] = $_POST['username'];
         $_SESSION['regsuccess'] = true;
      }
      /* Error found with form */
      else if($retval == 1){
         $form->values = $_POST;
         $form->errors = $form->getErrorArray();
      }
      /* Registration attempt failed */
      else if($retval == 2){
         $_SESSION['reguname'] = $_POST['username'];
         $_SESSION['regsuccess'] = false;
      }
   }
   
   /**
    * procForgotPass - Validates the given usernamename then if
    * everything is fine, a new passwordword is generated and
    * emailed to the address the username gave on sign up.
    */
   function procForgotPass(){
      global $dbase, $session, $mailer, $form;
      /* Username error checking */
      $subusername = $_POST['username'];
      $field = "username";  //Use field name for usernamename
      if(!$subusername || strlen($subusername = trim($subusername)) == 0){
         $form->setError($field, "* Username not entered<br>");
      }
      else{
         /* Make sure usernamename is in database */
         $subusername = stripslashes($subusername);
         if(strlen($subusername) < 5 || strlen($subusername) > 30 || !eregi("^([0-9a-z])+$", $subusername) || (!$dbase->usernameTaken($subusername))){
            $form->setError($field, "* Username does not exist<br>");
         }
      }
      
      /* Errors exist, have username correct them */
      if($form->num_errors > 0){
         $form->values = $_POST;
         $form->errors = $form->getErrorArray();
      }
      /* Generate new passwordword and email it to username */
      else{
         /* Generate new passwordword */
         $newpassword = $session->generateRandStr(8);
         
         /* Get email of username */
         $usrinf = $dbase->getUserInfo($subusername);
         $email  = $usrinf['email'];
         
         /* Attempt to send the email with new passwordword */
         if($mailer->sendNewPass($subusername,$email,$newpassword)){
            /* Email sent, update database */
            $dbase->updateUserField($subusername, "password", md5($newpassword));
            $_SESSION['forgotpassword'] = true;
         }
         /* Email failure, do not change passwordword */
         else{
            $_SESSION['forgotpassword'] = false;
         }
      }
      
   }
   
   /**
    * procEditAccount - Attempts to edit the username's account
    * information, including the passwordword, which must be verified
    * before a change is made.
    */
   function procEditAccount(){
      global $session, $form;
      /* Account edit attempt */
      $retval = $session->editAccount($_POST);

      /* Account edit successful */
      if($retval){
         $_SESSION['settings_changed'] = true;
      }
      /* Error found with form */
      else{
         $form->values = $_POST;
         $form->errors = $form->getErrorArray();
      }
   }

    /**
    * procChangePicture - Attempts to edit the username's Picture.
    */
   function procChangePicture(){
      global $session, $form;
      /* Picture Change attempt */
      $retval = $session->changePicture($_FILES['picture']);

      /* Picture Change successful */
      if($retval){
         $_SESSION['changepicture'] = true;
      }
      /* Error found with form */
      else{
         $form->values = $_POST;
         $form->errors = $form->getErrorArray();
      }
   }
       /**
    * procRemovePicture - Attempts to remove the username's Picture.
    */
   function procRemovePicture(){
      global $session, $form;
      /* Picture remove attempt */
      $retval = $session->removePicture();

      /* Picture Change successful */
      if($retval){
         $_SESSION['rempicture'] = true;
      }

   }
};
   

?>