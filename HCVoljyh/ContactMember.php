<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "lib/FunctionsMessages.php" ;
require_once "layout/Error.php" ;
include "layout/ContactMember.php" ;

  $IdMember=GetParam("cid",0) ; // find the concerned member 
  $Message=GetParam("Message","") ; // find the Message
  $iMes=GetParam("iMes",o) ; // find Message number 
	$IdSender=$_SESSION["IdMember"] ;

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
	
  switch(GetParam("action")) {
	
	  case "sendmessage" :
		  if (GetParam("IamAwareOfSpamCheckingRules")!="on") { // check if has accepted the vondition of sending
			  $Warning=ww("MustAcceptConditionForSending") ;
        DisplayContactMember($m,$Message,$iMes,$Warning) ;
			}
			$Status="ToSend" ; // todo compute a real status
			if ($iMes!=0) {
			  $str="update messages set Messages='".addslashes($Message)."',IdReceiver=".$IdMember.",IdSender=".$IdSender."InFolder='Normal',Status='".$Status."'" ;
			  sql_query($str) ;
			}
			else {
			  $str="insert into messages(created,Message,IdReceiver,IdSender,Status,InFolder) values(now(),'".addslashes($Message)."',".$IdMember.",".$IdSender.",'".$Status."','Normal') " ;
			  sql_query($str) ;
			  $iMes=mysql_insert_id() ;
			}
			
      $result=ww("YourMessageWillBeProcessed",$iMes) ;		  
      DisplayResult($m,$Message,$result) ;
		  exit(0) ;
	  case ww("SaveAsDraft") :
			if ($iMes!=0) {
			  $str="update messages set Messages='".addslashes($Message)."',IdReceiver=".$IdMember.",IdSender=".$IdSender."InFolder='Draft',Status='Draft'" ;
			  sql_query($str) ;
			}
			else {
			  $str="insert into messages(created,Message,IdReceiver,IdSender,Status,InFolder) values(now(),'".addslashes($Message)."',".$IdMember.",".$IdSender.",'Draft','Draft') " ;
			  sql_query($str) ;
			  $iMes=mysql_insert_id() ;
			}
			
			ComputeSpamCheck($iMes) ;
			
      $result=ww("YourMessageIsSavedAsDraft",$iMes) ;		  
      DisplayResult($m,$Message,$result) ;
		  exit(0) ;
	  case "logout" :
		  Logout("Main.php") ;
			exit(0) ;
	}
	

//  DisplayContactMember($m,$Message,$Warning) ;
  DisplayContactMember($m,$Message,$iMes,"") ;

?>
