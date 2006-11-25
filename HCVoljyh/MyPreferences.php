<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsTools.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/Error.php" ;


	$IdMember=$_SESSION['IdMember'] ;
	
	if (HasRight(Admin)) { // Admin will have access to any member right thru cid
	  $IdMember=GetParam("cid",$_SESSION['IdMember']) ;
	}


	
  switch(GetParam("action")) {
	  case "logout" :
		  Logout("Main.php") ;
			exit(0) ;
	  case "Update" :
		  echo "sorry not ready it does nothing" ;
			break ;
	}
	

// Try to load the Preferences, prepare the layout data
  $str="select * from preferences" ;
	$qry=mysql_query($str) ;
	$TPref=array() ;
	$TPrefMember=array() ;
	while ($rWhile=mysql_fetch_object($qry)) {
	  array_push($TPref,$rWhile) ;
		$rr=LoadRow("select * from memberspreferences where IdMember=".$IdMember." and IdPreference=".$rWhile->id) ;
		if (isset($rr->id)) $TPrefMember['$rr->codeName']=$rr ;
	}
	
  require_once "layout/MyPreferences.php" ;
  DisplayMyPreferences($TPref,$TPrefMember,$IdMember) ; // call the layout

?>
