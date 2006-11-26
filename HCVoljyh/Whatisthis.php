<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsTools.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/Error.php" ;
include "layout/Whatisthis.php" ;


  switch($action) {
	  case "logout" :
		  Logout("Main.php") ;
			exit(0) ;
	} 
	

  DisplayWhatisthis() ;

?>
