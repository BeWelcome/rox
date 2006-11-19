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

	$IdMember=$_SESSION['IdMember'] ; // by default it will be current member comments
	
  if (isset($_GET['cid'])) {
      $IdMember=$_GET['cid'] ;
  }
  if (isset($_POST['cid'])) {
      $IdMember=$_POST['cid'] ;
  }


	
  switch($action) {
	  case "logout" :
		  Logout("Main.php") ;
			exit(0) ;
	}
	

// Try to load the Comments, prepare the layout data
  $rWho=LoadRow("select * from members where id=".$IdMember) ;
  $str="select comments.*,members.Username as Commenter from comments,members where IdToMember=".$IdMember." and members.id=comments.IdFromMember" ;
	$qry=mysql_query($str) ;
	$TCom=array() ;
	while ($rWhile=mysql_fetch_object($qry)) {
	  array_push($TCom,$rWhile) ;
	}
	
  require_once "layout/ViewComments.php" ;
  DisplayComments($TCom,$rWho->Username) ; // call the layout

?>