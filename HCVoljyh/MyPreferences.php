<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/Error.php" ;

  if (!IsLogged()) {
	  $errcode="ErrorMustBeLogged" ;
	  DisplayError(ww($errcode)) ;
		exit(0) ;
	}

	$IdMember=$_SESSION['IdMember'] ;
	
	if (HasRight(Admin)) { // Admin will have access to any member right thru cid
	  $IdMember=GetParam("cid",$_SESSION['IdMember']) ;
	}

  switch(GetParam("action")) {
	  case "logout" :
		  Logout("Main.php") ;
			exit(0) ;
	  case "Update" :
      $str="select * from preferences" ;
	    $qry=mysql_query($str) ;
			$countinsert=0 ;
			$countupdate=0 ;
	    while ($rWhile=mysql_fetch_object($qry)) { // browse all preference
			  $Value=GetParam($rWhile->codeName) ;
			  if ($Value!="") {
		      $rr=LoadRow("select memberspreferences.id as id from memberspreferences,preferences where IdMember=".$IdMember." and IdPreference=preferences.id and preferences.codeName='".$rWhile->codeName."'") ;
					if (isset($rr->id)) {
					  $str="update memberspreferences set Value='".addslashes($Value)."' where id=".$rr->id ;
						countupdate++ ;
					}
					else {
					  $str="insert into memberspreferences(IdPreference,IdMember,Value,created) values(".$rWhile->id.",".$IdMember.",'".addslashes($Value)."',now() )" ; 
						countinsert++ ;
					}
					$count++ ;
//					echo "str=",$str,"<br>" ;
					sql_query($str) ;
				}
			}
			LogStr("updating/inserting ".$countupdate."/".$countinsert." preferences","Update Preference") ;
			
			break ;
	}
	
// Try to load or reload the Preferences, prepare the layout data
  $str="select preferences.*,Value from preferences left join memberspreferences on memberspreferences.IdPreference=preferences.id" ;
	$qry=sql_query($str) ;
	$TPref=array() ;
	while ($rWhile=mysql_fetch_object($qry)) {
	  array_push($TPref,$rWhile) ;
	}
	
  require_once "layout/MyPreferences.php" ;
  DisplayMyPreferences($TPref,$IdMember) ; // call the layout

?>
