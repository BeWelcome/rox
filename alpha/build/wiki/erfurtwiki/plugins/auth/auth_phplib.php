<?php
/*
   This auth method uses phplib to authenticate users and set the ewiki_author.
   It also initializes the phplib perm object.

   you'll need:
    - EWIKI_PROTECTED_MODE
	- EWIKI_AUTH_DEFAULT_RING as a minimum
	    - plugins/auth_perm_ring.php for better support
    - phplib-7.4-pre2 or compatible installed version

   You can only load __one__ auth method plugin!

   * To use you must add the following line to the end of your layout:
   	 <?php page_close() ?>	
   * You may also want to add some code to your layout to display the login and logout 
     links selectively this code is what I use:
	 * 
  	 if($perm->have_perm(EWIKI_PHPLIB_ALLUSERS_PERM)){
	?>
		Welcome <?php echo($GLOBALS["ewiki_author"]); ?><br />
		» <A HREF='<?php echo(EWIKI_SCRIPT); ?>Logout'>Logout</A><br />
	<?php
		}else{
	?>
		» <A HREF='<?php echo(EWIKI_SCRIPT); ?>Login'>Login</A><br />
	<?php
		};
	?>
*/

//There must be one group that includes all valid wiki users, this can be used for
// determing whether to display a login or logout link
define("EWIKI_PHPLIB_ALLUSERS_PERM","user");

#-- Connect to login query plugin
#-- Thereby requiring that the user have a name to edit pages
#-- permissions are not required for any particular edit 
#-- the user permission is checked below only to see if they are logged in 
#-- with an account other than nobody.
$ewiki_plugins["auth_query"][0] = "ewiki_auth_query_phplib";

#-- Login/Logout pages
$ewiki_plugins["page"]["LogOut"] = "ewiki_page_phplib_logout";
$ewiki_plugins["page"]["LogIn"] = "ewiki_page_phplib_login";
$ewiki_plugins["page"]["ChangePassword"] = "ewiki_page_phplib_chpw";


  page_open(array("sess" => "Example_Session", "auth" => "My_Auth", "perm" => "Example_Perm", "user" => "My_User"));

  @$GLOBALS["ewiki_author"]=$auth->auth["uname"];

  //echo($_REQUEST["cancel_login"]);
  if((!$perm->have_perm(EWIKI_PHPLIB_ALLUSERS_PERM))){	
  	define("EWIKI_AUTO_EDIT",0);	
  }
  
#-- text data
$ewiki_t["en"]["CANNOTCHANGEPAGE"] = "This page cannot be changed.  Perhaps you can <A href='".EWIKI_SCRIPT."LogOut'>LogIn</A> to and change it then.";
$ewiki_t["en"]["RESTRICTED_ACCESS"] = "You must be authenticated to use this part of the wiki.  If you have an account you can <A href='".EWIKI_SCRIPT."LogOut'>LogIn</A>.";
$ewiki_t["en"]["NOTLOGGEDIN"] = "You are not logged in.  You must <A href='".EWIKI_SCRIPT."LogIn'>LogIn</A> to access some features of this site.";
@$ewiki_t["en"]["LOGGEDIN"] = "You have logged in as ".$auth->auth['uname'].".  You must <A href='".EWIKI_SCRIPT."LogOut'>LogOut</A> to login again.";
@$ewiki_t["en"]["LOGINFORM"]='
<h1>Test for Login</h1>

Welcome!

Please identify yourself with a username and a password:<br />

<form action='.$auth->url().' method=post>
<table border=0 bgcolor="#eeeeee" align="center" cellspacing=0 cellpadding=4>
 <tr valign=top align=left>
  <td>Username:</td>
  <td><input type="text" name="username"
  value="'.htmlentities($auth->auth["uname"]).'"
  size=32 maxlength=32></td>
 </tr>
 <tr valign=top align=left>
  <td>Password:</td>
  <td><input type="password" name="password" size=32 maxlength=32></td>
 </tr>
 
 <tr>
  <td>&nbsp;</td>
  <td align=right><input type="submit" name="cancel_login" value="Cancel">
  <input type="submit" name="submit" value="Login now"></td>
 </tr>
</table>


</table>
</form>
';
$ewiki_t["en"]["BADOLDPW"]='You have misentered your old password and been logged out.  '.
	"Please <A href='".EWIKI_SCRIPT."LogIn'>LogIn</A> again to resume your session.";
$ewiki_t["en"]["PWCHNGD"]='Your password has been changed.';
$ewiki_t["en"]["NOMATCH"]='Your new password does not match with your retyping of it.';
$ewiki_t["en"]["CHPWFORM"]='
<h1>Change Password:</h1>

Please enter your old password once and your new password twice in the blanks below:<br />

<form action='.$auth->url().' method=post>
<table border=0 bgcolor="#eeeeee" align="center" cellspacing=0 cellpadding=4>
 <tr valign=top align=left>
  <td>Old Password:</td>
  <td><input type="password" name="oldpassword" size=32 maxlength=32></td>
 </tr>
 <tr valign=top align=left>
  <td>New Password:</td>
  <td><input type="password" name="newpassword1" size=32 maxlength=32></td>
 </tr>
 <tr valign=top align=left>
  <td>Repeat New Password:</td>
  <td><input type="password" name="newpassword2" size=32 maxlength=32></td>
 </tr>
 
 <tr>
  <td>&nbsp;</td>
  <td align=right><input type="submit" name="cancel_pwchng" value="Cancel">
  <input type="submit" name="submit" value="Change Password"></td>
 </tr>
</table>


</table>
</form>
';
 
function ewiki_page_phplib_chpw($id=0, $data=0) { 
	global $user;

	if(!isset($_REQUEST['oldpassword'])){
		return(ewiki_t("CHPWFORM"));
	}else{
		return($user->changepw($_REQUEST['oldpassword'],$_REQUEST['newpassword1'],$_REQUEST['newpassword2']).ewiki_t("CHPWFORM"));
	}
	
	
}
 
function ewiki_page_phplib_login($id=0, $data=0) {
	global $auth,$sess,$perm;

	//if we did not just submit user data
	if(isset($_REQUEST['username'])){
		if($perm->have_perm(EWIKI_PHPLIB_ALLUSERS_PERM)){
		  	return( ewiki_t("LOGGEDIN") );		
		}else{
		  	return( ewiki_t("NOTLOGGEDIN") );					
		}
	}
	   $auth->unauth();  # We have to relogin, so clear current auth info
 	   $auth->nobody = false; # We are forcing login, so default auth is 
                             # disabled
	   $auth->auth["uid"] = "form";
       $auth->auth["exp"] = 0x7fffffff;
       $auth->auth["refresh"] = 0x7fffffff;
       $sess->freeze();		
	   
  return( ewiki_t("LOGINFORM") );
} 
 

 
function ewiki_page_phplib_logout($id=0, $data=0) {
	global $auth;

  $auth->logout();  
  page_close();
/**
 *    return(  "<h1>Logout</h1> 
  You have been logged in as <b>".$auth->auth["uname"]."</b> with
  <b>".$auth->auth["perm"]."</b> permission.You have been logged out.");
 */
   return(  "<h1>Logout</h1> You have been logged out.");
 }
function ewiki_auth_query_phplib(&$output, $force_query=0) {

   global $auth,$perm,$sess, $ewiki_author, $ewiki_ring;
	
  if($_REQUEST["cancel_login"]=="Cancel"){
	return(false);
  }
	
	//attempt login if user not in group user
   if(!$perm->have_perm(EWIKI_PHPLIB_ALLUSERS_PERM)){
	$auth->unauth();  # We have to relogin, so clear current auth info
 	$auth->nobody = false; # We are forcing login, so default auth is 
                             # disabled
    $auth->auth["uid"] = "form";
    $auth->auth["exp"] = 0x7fffffff;
    $auth->auth["refresh"] = 0x7fffffff;
    $sess->freeze();							 
							 	
   	$output=ewiki_t("LOGINFORM");
   	return(false);
   }
   
   //If we have a valid user (in group user) return sucess
   if ($perm->have_perm(EWIKI_PHPLIB_ALLUSERS_PERM)) {
	$ewiki_ring=1;   // priviliged but ordinary user
   }
   else {
	$ewiki_ring=3;   // every other stupid, browse-only access
   }

   
	//If we have a valid user (in group user) return sucess
   return($perm->have_perm(EWIKI_PHPLIB_ALLUSERS_PERM));
}

class My_User extends User {
  var $classname = "My_User";
  var $register_globals = false;		
 
  var $magic          = "Abracadabra";     ## ID seed
  var $that_class     = "Example_CT_Sql";  ## name of data storage container class
  
  function changepw($oldpw,$newpw1,$newpw2){
  	global $auth;
	
	if(!$auth->check_login($auth->auth['uname'],$oldpw)){
		$auth->unauth();  # Invalid password, log them out		
		return(ewiki_t("BADOLDPW"));	
	}elseif($reason=$this->is_pw_invalid($newpw1)){
		return($reason);
	}elseif($newpw1!=$newpw2){
		return(ewiki_t("NOMATCH"));
	}  	
	$auth->db->query(sprintf("UPDATE %s SET password='%s'".
		"       where user_id = '%s' ",
	$auth->database_table,
	addslashes($newpw1),	
	addslashes($this->id)
	));
	return(ewiki_t("PWCHNGD"));

	 
	
  }
  
	function is_pw_invalid($pw){
		return($pw=='');
	}

}


class My_Auth extends Auth {
  var $classname = "My_Auth";
  
  var $mode = "log";              ## "log" for login only systems,
#  var $classname      = "Example_Auth";

  var $lifetime       = 15;

  var $database_class = "DB_Example";
  var $database_table = "auth_user";
  var $nobody    = true;
  
  function auth_loginform() {
    global $sess;
    global $_PHPLIB;

    include($_PHPLIB["libdir"] . "loginform.ihtml");
  }
  function check_login($username,$password){
    $uid = false;
	
      $this->db->query(sprintf("select user_id, perms ".
                             "        from %s ".
                             "       where username = '%s' ".
                             "         and password = '%s'",
                          $this->database_table,
                          addslashes($username),
                          addslashes($password)));

    while($this->db->next_record()) {
      $uid = $this->db->f("user_id");
      $this->auth["perm"] = $this->db->f("perms");
    }
	
	return $uid;
  }
  
  function auth_validatelogin() {
    global $HTTP_POST_VARS;
	
    if(isset($HTTP_POST_VARS["username"])) {
      $this->auth["uname"] = $HTTP_POST_VARS["username"];        ## This provides access for "loginform.ihtml"
    }
    
    
	return $this->check_login($HTTP_POST_VARS["username"],$HTTP_POST_VARS["password"]);
	

     
  }  
  
}



?>