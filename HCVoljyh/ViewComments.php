<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/Error.php" ;
require_once "layout/ViewComments.php" ;

  $IdMember=GetParam("cid",$_SESSION['IdMember']) ;
	
  switch(GetParam("action")) {
	  case "logout" :
		  Logout("Main.php") ;
			exit(0) ;
	}
	

// Try to load the Comments, prepare the layout data
  $rWho=LoadRow("select * from members where id=".$IdMember) ;
  $str="select comments.*,members.Username as Commenter from comments,members where IdToMember=".$IdMember." and members.id=comments.IdFromMember" ;
	$qry=mysql_query($str) ;
	$TCom=array() ;
	while ($rWhile=mysql_fetch_object($qry)) {
	  array_push($TCom,$rWhile) ;
	}
	
  DisplayComments($TCom,$rWho->Username) ; // call the layout

?>