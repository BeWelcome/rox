<?php
include "lib/dbaccess.php" ;
require_once "layout/error.php" ;
require_once "layout/adminaccepter.php" ;
  $IdMember=GetParam("cid") ;

	$countmatch=0 ;

  $RightLevel=HasRight('Accepter'); // Check the rights
  if ($RightLevel<1) {  
    echo "This Need the sufficient <b>Accepter</b> rights<br>" ;
	  exit(0) ;
  }
	
  $scope=RightScope('Accepter') ;
	
	$lastaction="" ;
  switch(GetParam("action")) {
	  case "logout" :
		  Logout("main.php") ;
			exit(0) ;
			break ;
	  case "accept" :
		  $rr=LoadRow("select * from members where id=".$IdMember) ;
			$lastaction="accepting ".$rr->Username ;
	    $str="update members set Status='Active' where Status='Pending' and id=".$IdMember ;
	    $qry=sql_query($str) ;


			$Email=AdminReadCrypted($rr->Email) ;
			// todo change what need to be change to answer in member default language
			$subj=ww("SignupSubjAccepted",$_SYSHCVOL['SiteName']) ;
			$loginurl=$_SYSHCVOL['SiteName']."/login.php?&Username=".$rr->Username ;
			$text=ww("SignupYouHaveBeenAccepted",$rr->Username,$_SYSHCVOL['SiteName'],$loginurl) ;
			hvol_mail($Email,$subj,$text,$hh,$_SYSHCVOL['AccepterSenderMail'],$_SESSION['IdLanguage'],"","","") ;
			

			break ;
	  case "tocomplete" :
		  $rr=LoadRow("select * from members where id=".$IdMember) ;
			$lastaction="setting to profile of  ".$rr->Username. " to NeedMore"  ;
			
	    $str="update members set Status='NeedMore' where Status='Pending' and id=".$IdMember ;
	    $qry=sql_query($str) ;
			// to do manage the need more
			break ;
	}
	
	$Taccepted=array() ;
	$Ttoaccept=array() ;
	$Tmailchecking=array() ;
	$Tpending=array() ;
	$TNeedMore=array() ;
	
	
	$str="select * from members where Status='Pending'" ;
	$qry=sql_query($str) ;
	while ($rr=mysql_fetch_object($qry)) {
	  array_push($Tpending,$rr) ;
	} 
	
	$str="select * from members where Status='Active'" ;
	$qry=sql_query($str) ;
	while ($rr=mysql_fetch_object($qry)) {
	  array_push($Taccepted,$rr) ;
	} 
	
	$str="select * from members where Status='MailToConfirm'" ;
	$qry=sql_query($str) ;
	while ($rr=mysql_fetch_object($qry)) {
	  array_push($Tmailchecking,$rr) ;
	} 
	
	$str="select * from members where Status='NeedMore'" ;
	$qry=sql_query($str) ;
	while ($rr=mysql_fetch_object($qry)) {
	  array_push($TNeedMore,$rr) ;
	} 
	
  DisplayAdminAccepter($Taccepted,$Ttoaccept,$Tmailchecking,$Tpending,$TNeedMore,$lastaction) ; // call the layout
	
?>