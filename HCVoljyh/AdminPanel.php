<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsTools.php" ;
require_once "lib/FunctionsLogin.php" ;


if (!IsLogged()) {
  include "layout/Login.php" ;
	exit(0) ;
}

if (!HasRight("Admin")) { // Check if Right are correct
  $strerror="Need Admin right." ;
  LogStr($strerror." ".$_SERVER['PHP_SELF'],"noright") ;
  echo $strerror ;
	exit() ;
}

  if (isset($_GET['action'])) {
    $action=$_GET['action'] ;
  }
  if (isset($_POST['action'])) {
    $action=$_POST['action'] ;
  }
	
  switch($action) {
	  case "rebuild" :
		  if (HasRight("Admin")>=10) {
		    // here todo rebuild pannel (file Hcvol_Config.php)
			}
			else {
			  echo "Admin Level 10 is required to rebuild panel" ;
			}
			break ;
	  case "logout" :
		  Logout("Main.php") ;
			exit(0) ;
	}
	
	
  // here todo : list the content of HCVol_Config table 



?>
