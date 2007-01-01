<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/Error.php" ;


  switch($action) {
	  case "logout" :
		  Logout("main.php") ;
			exit(0) ;
	} 
	

  include "layout/search.php" ;
  DisplaySearch() ;

?>
