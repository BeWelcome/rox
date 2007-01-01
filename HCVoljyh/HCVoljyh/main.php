<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/Error.php" ;

  switch(GetParam("action")) {
	  case "login" :
		  Login(GetParam("Username"),GetParam("password"),"main.php") ;
			break ;
			
		case "confirmsignup" :  // case a new signupper confirm his mail
		  $m=LoadRow("select * from members where Username='".GetParam("username")."'") ;

			if (isset($m->id)) {
			
			  if ($m->Status!="MailToConfirm") {
	        $errcode="ErrorMailAllreadyConfimed" ;
				  LogStr("action confirm signup ErrorMailAllreadyConfimed Status=".$m->Status,"login") ;
	        DisplayError(ww($errcode,$m->Status)) ;
				  exit(0) ;
				} 
				
				$_SESSION['IdMember']=$m->id ; // In this case we must have an identified member
							  
				// todo here use something else that AdminReadCrypted (will not work when crypted right will be added)
			  $key=CreateKey($m->Username,AdminReadCrypted($m->LastName),$m->id,"registration") ; // retrieve the nearly unique key

/*				
				  echo "key=",$key,"<br>";
				  echo " GetParam(\"key\")=",GetParam("key"),"<br>"; 
					echo "\$m->id=",$m->id,"<br>" ;
					echo "ReadCrypted(\$m->LastName)=",AdminReadCrypted($m->LastName),"<br>" ;
					echo "\$m->Username=",$m->Username,"<br>" ;
*/


			  if ($key!=GetParam("key")) {
	        $errcode="ErrorBadKey" ;
				  LogStr("Bad Key","hacking") ;
	        DisplayError(ww($errcode)) ;
				  exit(0) ;
			  }
			  $str="update members set Status='Pending' where id=".$m->id ; // The email is confirmed make the status Pending
				sql_query($str) ;
				$m->Status="Pending" ;
			}
			break ;
	  case "logout" :
		  Logout("login.php") ;
			exit(0) ;
	}

if ($m->Status=="Pending") { // Members with Pending status can only update ther profile
  if ($m->IdCity>0) {
    $rWhere=LoadRow("select cities.Name as cityname,regions.Name as regionname,countries.Name as countryname from cities,countries,regions where cities.IdRegion=regions.id and countries.id=regions.IdCountry and cities.id=".$m->IdCity) ;
  }
  include "layout/editmyprofile.php" ;
  $profilewarning=ww("YouCanCompleteProfAndWait",$m->Username) ;
  DisplayEditMyProfile($m,"","",0,$rWhere->cityname,$rWhere->regionname,$rWhere->countryname,$profilewarning,array()) ;
	exit(0) ;
}

if (IsLogged()) {
  $m=LoadRow("select * from members where id=".$_SESSION['IdMember']) ;
  include "layout/main.php" ;
  DisplayMain($m) ;
}
else {
  Logout("login.php") ;
	exit(0) ;
}

?>
