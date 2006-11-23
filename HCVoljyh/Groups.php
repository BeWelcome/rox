<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsTools.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/Error.php" ;

  if (isset($_GET['action'])) {
    $action=$_GET['action'] ;
  }
  if (isset($_POST['action'])) {
    $action=$_POST['action'] ;
  }

	$IdMember=$_SESSION['IdMember'] ;
	
	if (HasRight(Admin)) { // Admin will have access to any member right thru cid
    if (isset($_GET['cid'])) {
      $IdMember=$_GET['cid'] ;
    }
    if (isset($_POST['cid'])) {
      $IdMember=$_POST['cid'] ;
    }
	}


	
  switch($action) {
	  case "logout" :
		  Logout("Main.php") ;
			exit(0) ;
	  case "Update" :
		  Logout("Main.php") ;
			exit(0) ;
	}
	

// Try to load the Preferences, prepare the layout data
  $str="select * from groups" ;
	$qry=mysql_query($str) ;
	$TGroup=array() ;
	while ($rr=mysql_fetch_object($qry)) {
	  array_push($TGroup,$rr) ;
	}
	
  require_once "layout/Groups.php" ;
  DisplayGroupList($TGroup) ; // call the layout

?>
