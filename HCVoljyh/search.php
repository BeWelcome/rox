<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/error.php" ;


  switch($action) {
	  case "logout" :
		  Logout("main.php") ;
			exit(0) ;
	} 
	

	  $errcode="ErrorTodoPage" ;
	  DisplayError(ww($errcode,$_SERVER["PHP_SELF"])) ;

?>
