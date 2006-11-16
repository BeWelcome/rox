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
	
  switch($action) {
	  case "logout" :
		  Logout("Main.php") ;
			exit(0) ;
	}
	
// Find parameters
	$IdMember="" ;
  if (isset($_GET['cid'])) {
    $IdMember=$_GET['cid'] ;
  }
  if (isset($_POST['cid'])) {
    $IdMember=$_POST['cid'] ;
  }
	if ($IdMember=="") {
	  $errcode="ErrorWithParameters" ;
	  DisplayError(ww("ErrorWithParameters","\$IdMember is not defined")) ;
		exit(0) ;
	}
	

// Try to load the member
	if (is_numeric($IdMember)) {
	  $m=LoadRow("select * from members where id=".$IdMember." and Status='Active'") ;
	}
	else {
		$m=LoadRow("select * from members where Username='".$IdMember."' and Status='Active'") ;
	}

	if (!isset($m->id)) {
	  $errcode="ErrorNoSuchMember" ;
	  DisplayError(ww($errcode,$IdMember)) ;
//		die("ErrorMessage=".$ErrorMessage) ;
		exit(0) ;
	}

  include "layout/Member.php" ;
  DisplayMember($m) ;

?>
