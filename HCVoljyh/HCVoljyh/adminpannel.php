<?php
include "lib/dbaccess.php" ;
require_once "layout/error.php" ;
require_once "layout/adminPannel.php" ;	

  $RightLevel=HasRight('Admin'); // Check the rights
//  if ($RightLevel<1) {  
//    echo "This Need the sufficient <b>Admin</b> rights<br>" ;
//	  exit(0) ;
//  }
	
  $scope=RightScope('Admin') ;
	
	$lastaction="" ;
  switch(GetParam("action")) {
	  case "logout" :
		  Logout("main.php") ;
			exit(0) ;
			break ;
	  case "phpinfo" :
		  phpinfo() ;
			break ;
	  case "update" :
 			break ;
	}
	
	$TData=array() ;
	

  DisplayPannel($TData,$sResult) ; // call the layout
	
?>