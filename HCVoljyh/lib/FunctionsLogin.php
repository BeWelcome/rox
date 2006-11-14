<?php


//------------------------------------------------------------------------------
// Logout function unlog member and fisplay the login page 
Function Logout($nextlink="") {
  if (isset($_SESSION['IdMember'])) unset($_SESSION['IdMember']) ;
  if (isset($_SESSION['MemberCryptKey'])) unset($_SESSION['MemberCryptKey']) ;
  if (isset($_SESSION['IdMember'])) unset($_SESSION['IdMember']) ;

  if ($nextlink!="") {
		header("Location: ".$nextlink);
	}
} // end of function Logout

//------------------------------------------------------------------------------
// Login function does the proper verification for Login, 
// update members.LastLogin and link to main page or to other proposed
// page in main link
Function Login($UsernameParam,$passwordParam,$nextlink="Main.php") {


	$Username=strtolower((ltrim(rtrim($UsernameParam)))) ; // we are cool and help members with big fingers
	$password=ltrim(rtrim($passwordParam)) ; // we are cool and help members with big fingers
	

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
	
	
// Process the login of the member according to his status
	switch ($m->Status) {
	  case "Active" :
			LogStr("Successful login","Login") ;
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
		  $str="You must wait a bit, your appliance hasen't yet be reviewed by our volunteers" ;
			LogStr($str,"Login") ;
			refuse_Login($str,"index.php") ;
			break ;

	  default:
			LogStr("Unprocessed status=[<b>".$m->Status."</b>] in FunctionsLogin.php","Login") ;
	    refuse_Login("You can't log because your status is set to ".$m->Status."<br>",$nextlogin);
			break ;
	}
	
	if ($nextlogin!="") {
	  header("Location: ".$nextlogin); // go to next page
	  exit(0) ;
	}
/*	

			LoadMyParam(1) ; // Force reloading pparam and privileges JY 28/9/04

      if (IsSpeed()<=3) {
			  updatelastlogin($m) ; // Don't update last login if speed above 3
			}
			
		  if ($m->get("accepted")=="Inactive") {
			    hc_mysql_query("update members set accepted='True' where username='".$username."'") ;
				  $_SESSION["welcomebackatlogin"]=ww("welcomebackatfrominactive",$username) ;
			    LogStr("Login with <b>".$_SERVER['HTTP_USER_AGENT']."</b> Inactive ! confirm to be back","Login") ;
			}
		  if ($m->get("nbofremindreceivedsincelastlog")>0) { // dealing with sleeping members and reminded members
			  if ($m->get("accepted")=="sleeper") {
			    hc_mysql_query("update members set nbofremindreceivedsincelastlog=0,nbofemailreceivedsincelastlog=0,accepted='True' where username='".$username."'") ;
				  $_SESSION["welcomebackatlogin"]=ww("welcomebackatlogin",$username) ;
			    LogStr("Login with <b>".$_SERVER['HTTP_USER_AGENT']."</b> Sleeper ! back after ".$m->get("nbofremindreceivedsincelastlog")." reminds and ".$m->get("nbofemailreceivedsincelastlog")." mails received,","Login") ;
				}
				else {
			    hc_mysql_query("update members set nbofremindreceivedsincelastlog=0,nbofemailreceivedsincelastlog=0 where username='".$username."'") ;
				  $_SESSION["welcomebackatlogin"]=ww("welcomebackatlogin",$username) ;
			    LogStr("Login with <b>".$_SERVER['HTTP_USER_AGENT']."</b> back after ".$m->get("nbofremindreceivedsincelastlog")." reminds and ".$m->get("nbofemailreceivedsincelastlog")." mails received,","Login") ;
				}
	      $m->getUser($username); // reload data to have real accepted status
			} // end of dealing with sleeping members and reminded members
			else {
			  LogStr("Login with <b>".$_SERVER['HTTP_USER_AGENT']."</b>","Login") ;
			}
      AddInWhoIsOnLine($m) ; // Add the current member in the whoisonline

// Check if a new record is broken
		  $rdlimit=LoadRow("select now() as dnow") ;
		  $rr=LoadRow("select count(*) as cnt from DynMembers where lastaccess >date_sub('$rdlimit->dnow', interval 10 minute) ");
		  if ($rr->cnt >$adminsetup->maxonline) {
        hc_mysql_query("update adminsetup set maxonline=$rr->cnt where id=1 and maxonline<$rr->cnt") ;
			  if (mysql_affected_rows()>0) {
			    LogStr("New record $rr->cnt members online","new record !") ;
			  }
		  }

			// Update the default language
			$sqry="select SQL_NO_CACHE * from memberparam where Name='PreferenceLang' and IdMember=".$m->get('id') ;
			$qry=hc_mysql_query($sqry)  or die($sqry." ".mysql_error());
			if ($rlang=mysql_fetch_object($qry)) {
				if ($rlang->Value!="") {
					$lang=$rlang->Value ;
					reload_language($lang) ;
				}
			}
			
			//echo "loginnext: $loginnext";
			//exit();
			if(!empty($nextlogin)) {
				header("Location: ".$nextlogin);
			} 
			else {
				header("Location: menu.php");
			}
		  exit(0) ;
		}
		elseif ($m->get("accepted")=="needmore") {
			$_SESSION['userName']=$username;
			$_SESSION['level']=0;
			$_SESSION['gid']=$m->get("id");
			$_SESSION['gpassword']=$password;
			LogStr("Login with (needmore)<b>".$_SERVER['HTTP_USER_AGENT']."</b>","Identity") ;
			header("Location: completeprofile.php");
			exit(0) ;
		}
		elseif ($m->get("accepted")=="emailnotyetconfirmed") {

			$_SESSION['userName']=$username;
			$_SESSION['level']=0;
			$_SESSION['gid']=$m->get("id");
			$_SESSION['gpassword']=$password;

			LogStr("Login with (emailnotyetaccepted)<b>".$_SERVER['HTTP_USER_AGENT']."</b>","Login") ;

			LoadMyParam(1) ; // Force reloading pparam and privileges JY 28/9/04
			
			// Update the default language
			$sqry="select * from memberparam where Name='PreferenceLang' and IdMember=".$m->get('id') ;
			$qry=hc_mysql_query($sqry)  or die($sqry." ".mysql_error());
			if ($rlang=mysql_fetch_object($qry)) {
					if ($rlang->Value!="") {
						$lang=$rlang->Value ;
						reload_language($lang) ;
					}
				}
			header("Location: signup2.php");
			exit(0) ;
		}
	}
	*/ 
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
