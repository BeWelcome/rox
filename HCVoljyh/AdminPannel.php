<?php
include "lib/dbaccess.php" ;
require_once "layout/Error.php" ;
require_once "layout/AdminPannel.php" ;	

  $RightLevel=HasRight('Admin'); // Check the rights
//  if ($RightLevel<1) {  
//    echo "This Need the sufficient <b>Admin</b> rights<br>" ;
//	  exit(0) ;
//  }
	
  $scope=RightScope('Admin') ;
	
	$lastaction="" ;
  switch(GetParam("action")) {
	  case "logout" :
		  Logout("Main.php") ;
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