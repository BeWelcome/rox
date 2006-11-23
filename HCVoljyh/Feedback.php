<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsTools.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/Error.php" ;


  switch($action) {
	  case "logout" :
		  Logout("Main.php") ;
			exit(0) ;
	} 
	

	$TFeedBackCategory=array() ;
	$str="select * from feedbackcategories " ;
	$qry=mysql_query($str) ;
	while ($rr=mysql_fetch_object($qry)) {
	  array_push($TFeedBackCategory,$rr) ;
	} 
	
	
  include "layout/Feedback.php" ;
  DisplayFeedback($TFeedBackCategory) ;

?>
