<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsTools.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/Error.php" ;


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
	

  if (isset($_POST['action'])) {
    $action=$_POST['action'] ;
  }
	
	
  switch($action) {
	  case "logout" :
		  Logout("Main.php") ;
			exit(0) ;
	}
	

// Try to load the member
	if (is_numeric($IdMember)) {
	  $str="select * from members where id=".$IdMember." and Status='Active'" ;
	}
	else {
		$str="select * from members where Username='".$IdMember."' and Status='Active'" ;
	}

	$m=LoadRow($str) ;

	if (!isset($m->id)) {
	  $errcode="ErrorNoSuchMember" ;
	  DisplayError(ww($errcode,$IdMember)) ;
//		die("ErrorMessage=".$ErrorMessage) ;
		exit(0) ;
	}

	$IdMember=$m->id ; // to be sure to have a numeric ID

	

  include "layout/ContactMember.php" ;
  DisplayContactMember($m) ;

?>
