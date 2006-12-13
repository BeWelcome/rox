<?php
include "lib/dbaccess.php" ;
require_once "layout/Error.php" ;
require_once "layout/AdminChecker.php" ;

  $username=GetParam("username") ;
  $rightname=GetParam("rightname") ;
	

  $RightLevel=HasRight('Checker'); // Check the rights
  if ($RightLevel<1) {  
    echo "This Need the suffcient <b>Checker</b> rights<br>" ;
	  exit(0) ;
  }
	
  $scope=RightScope('Checker') ;
	
	$lastaction="" ;
  switch(GetParam("action")) {
	  case "logout" :
		  Logout("Main.php") ;
			exit(0) ;
			break ;
	  case "add" :
			break ;
	  case "update" :
 			break ;
	}
	
	$TMess=array() ;
	
	

// Load the right list
	$str="select messages.*,mSender.Username as Username_sender,mReceiver.Username as Username_receiver from messages,members as mSender,members as mReceiver where messages.Status='ToCheck' and messages.WhenFirstRead='0000-00-00 00:00:00' and mSender.id=IdSender and mReceiver.id=IdReceiver" ;
	$qry=sql_query($str) ;
	while ($rr=mysql_fetch_object($qry)) {
//	  if not scope test continue ; // Skip not allowed rights  todo manage an eventual scope test
	  array_push($TMess,$rr) ;
	} 
// end of Load the right list
	
	
	
  DisplayMessages($TMess) ; // call the layout
	
?>