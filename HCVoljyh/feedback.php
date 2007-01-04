<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/error.php" ;


  switch($action) {
	  case "logout" :
		  Logout("main.php") ;
			exit(0) ;
	} 
	

	$TFeedBackCategory=array() ;
	$str="select * from feedbackcategories " ;
	$qry=mysql_query($str) ;
	while ($rr=mysql_fetch_object($qry)) {
	  array_push($TFeedBackCategory,$rr) ;
	} 
	
	
  include "layout/feedback.php" ;
  DisplayFeedback($TFeedBackCategory) ;

?>
