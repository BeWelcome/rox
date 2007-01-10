<?php


//------------------------------------------------------------------------------
// Logout function unlog member and fisplay the login page 
Function Logout($nextlink="") {
  if (isset($_SESSION['IdMember'])) {
	  
    // todo optimize periodically online table because it will be a gruyere 
		// remove from online list
		$str="delete from online where IdMember=".$_SESSION['IdMember'] ;
		sql_query($str) ;

	  unset($_SESSION['WhoIsOnlineCount']);
	  unset($_SESSION['IdMember']) ;
		LogStr("Loging out","Login") ;
	}
  if (isset($_SESSION['MemberCryptKey'])) unset($_SESSION['MemberCryptKey']) ;
  if (isset($_SESSION['IdMember'])) unset($_SESSION['IdMember']) ;

  if ($nextlink!="") {
		header("Location: login.php?nextlink=".$nextlink);
	}
} // end of function Logout

//------------------------------------------------------------------------------
// Login function does the proper verification for Login, 
// update members.LastLogin and link to main page or to other proposed
// page in main link
Function Login($UsernameParam,$passwordParam,$nextlink="main.php") {

	$Username=strtolower((ltrim(rtrim($UsernameParam)))) ; // we are cool and help members with big fingers
	$password=ltrim(rtrim($passwordParam)) ; // we are cool and help members with big fingers
	
// todo : improve this security weakness !
	$_SESSION["key_to_tb"]=$password ; // storing the password to acces travelbook

  Logout("") ; // if was previously logged then force logout
	$str="select * from members where Username='$Username' and PassWord=PASSWORD('".$password."')" ;
//	echo "\$str=$str","<br>" ;
	$m=LoadRow($str) ;
	if (!isset($m->id)) { // If Username does'nt exist
			LogStr("Failed to connect with Username=[<b>".$Username."</b>]","Login") ;
			refuse_login("no such username and password",$nextlogin) ;
	}

// Set the session identifier
	$_SESSION['IdMember']=$m->id ;
	
  if ($_SESSION['IdMember']!=$m->id) { // Check is session work of
			LogStr("Session problem detected in FunctionsLogin.php","Login") ;
			refuse_login("Session problem detected in FunctionsLogin.php",$next_login) ;
	} ; // end Check is session work of

	$_SESSION['MemberCryptKey']=crypt($password,"rt") ;  // Set the key which will be used for member personal cryptation
	$_SESSION['LogCheck']=Crc32($_SESSION['MemberCryptKey'].$_SESSION['IdMember']) ;  // Set the key for checking id and LohCheck (will be restricted in future)
	
	mysql_query("update members set LastLogin=now() where id=".	$_SESSION['IdMember']) ; // update the LastLogin date
	
	// Load language prederence (IdPreference=1)
	$rPrefLanguage=LoadRow("select memberspreferences.Value,ShortCode from memberspreferences,languages where IdMember=".$_SESSION['IdMember']." and IdPreference=1 and memberspreferences.Value=languages.id") ;
	if (isset($rPrefLanguage->Value)) { // If there is a member selected preference set it
	  $_SESSION["IdLanguage"]=$rPrefLanguage->Value ;
	  $_SESSION["lang"]=$rPrefLanguage->ShortCode ;
	}
	
	
// Process the login of the member according to his status
	switch ($m->Status) {
	  case "Active" :
			LogStr("Successful login with <b>".$_SERVER['HTTP_USER_AGENT']."</b>","Login") ;
		  if (HasRight("Words")) $_SESSION['switchtrans']="on" ; // Activate switchtrans oprion if its a translator
			break ;

	  case "ToComplete" :
			LogStr("Login with (needmore)<b>".$_SERVER['HTTP_USER_AGENT']."</b>","Login") ;
			header("Location: completeprofile.php");
			exit(0) ;
		
	  case "Banned" :
			LogStr("Banned member tried to log<b>".$_SERVER['HTTP_USER_AGENT']."</b>","Login") ;
			refuse_Login("You are not allowed to log anymore","index.php") ;
			exit(0) ;
		
			
	  case "TakenOut" :
			LogStr("Takenout member want to Login<b>".$_SERVER['HTTP_USER_AGENT']."</b>","Login") ;
			refuse_Login("You have been taken out at your demand, you will certainly be please to see you back, please contact us to re-active your profile","index.php") ;
			exit(0) ;
			
	  case "CompletedPending":			
	  case "Pending" :
		  $str="You must wait a bit, your appliance hasn't yet be reviewed by our volunteers <b>".$_SERVER['HTTP_USER_AGENT']."</b>" ;
			LogStr($str,"Login") ;
			refuse_Login($str,"index.php") ;
			break;
			
	  case "NeedMore" :
      header("Location: updatemandatory.php"); 
		  exit(0) ;
			break;

	  default:
			LogStr("Unprocessed status=[<b>".$m->Status."</b>] in FunctionsLogin.php with <b>".$_SERVER['HTTP_USER_AGENT']."</b>","Login") ;
	    refuse_Login("You can't log because your status is set to ".$m->Status."<br>",$nextlogin);
			break ;
  }
		
	if ($nextlink!="") {
	  header("Location: ".$nextlink); // go to next page
	  exit(0) ;
	}

}

//------------------------------------------------------------------------------
// function refuse login is called when log fail and display a proper message
function refuse_login($message,$nextlink) {
	    $title=ww('login');
	    include("layout/header.php");
			echo "<b>".$message."<br>" ;
			echo "<a href=".$nextlink.">back</a><br><br><br>" ;
		  include("layout/footer.php");
			
			exit(0) ;
} // end of refuse_login($message,$nextlink)

?>
