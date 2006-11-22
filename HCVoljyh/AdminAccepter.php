<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsTools.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/Error.php" ;
require_once "layout/AdminAccepter.php" ;

  if (isset($_POST['action'])) {
    $action=$_POST['action'] ;
  }
	
  if (isset($_POST['cid'])) {
      $IdMember=$_POST['cid'] ;
  }

	$countmatch=0 ;

  $RightLevel=HasRight('Accepter'); // Check the rights
  if ($RightLevel<1) {  
    echo "This Need the suffcient <b>Accepter</b> rights<br>" ;
	  exit(0) ;
  }
	
  $scope=RightScope('Accepter') ;
	
	$lastaction="" ;
  switch($action) {
	  case "logout" :
		  Logout("Main.php") ;
			exit(0) ;
			break ;
	  case "accept" :
		  $rr=LoadRow("select * from members where id=".$IdMember) ;
			$lastaction="accepting ".$rr->Username ;
	    $str="update members set Status='Active' where Status='Pending' and id=".$IdMember ;
	    $qry=mysql_query($str) ;
			break ;
	  case "tocomplete" :
		  $rr=LoadRow("select * from members where id=".$IdMember) ;
			$lastaction="setting to profile of  ".$rr->Username. " to NeedMore"  ;
	    $str="update members set Status='NeedMore' where Status='Pending' and id=".$IdMember ;
	    $qry=mysql_query($str) ;
			break ;
	}
	
	$Taccepted=array() ;
	$Ttoaccept=array() ;
	$Tmailchecking=array() ;
	$Tpending=array() ;
	$TNeedMore=array() ;
	
	
	$str="select * from members where Status='Pending'" ;
	$qry=mysql_query($str) ;
	while ($rr=mysql_fetch_object($qry)) {
	  array_push($Tpending,$rr) ;
	} 
	
	$str="select * from members where Status='Active'" ;
	$qry=mysql_query($str) ;
	while ($rr=mysql_fetch_object($qry)) {
	  array_push($Taccepted,$rr) ;
	} 
	
	$str="select * from members where Status='MailToConfirm'" ;
	$qry=mysql_query($str) ;
	while ($rr=mysql_fetch_object($qry)) {
	  array_push($Tmailchecking,$rr) ;
	} 
	
	$str="select * from members where Status='NeedMore'" ;
	$qry=mysql_query($str) ;
	while ($rr=mysql_fetch_object($qry)) {
	  array_push($TNeedMore,$rr) ;
	} 
	
	
	
	

  DisplayAdminAccepter($Taccepted,$Ttoaccept,$Tmailchecking,$Tpending,$TNeedMore,$lastaction) ; // call the layout
	
?>