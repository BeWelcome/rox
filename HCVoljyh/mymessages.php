<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsTools.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/Error.php" ;
include "layout/MyMessages.php" ;


// test if is logged, if not logged and forward to the current page
if (!IsLogged()) {
  Logout($_SERVER['PHP_SELF']) ;
	exit(0) ;
}


// Find parameters

	
	$TMess=array() ;
		
  switch(GetParam("action")) {
	  case "del" :
	  case "marknospam" :
	  case "markspam" :
	  case "reply" :
		  echo "not yet ready" ;
			exit(0) ;
	  case "Received" :
		  $Title=ww("MessagesThatIHaveReceived") ;
			$FromTo="MessageFrom" ;
			$str="select messages.id as IdMess,SpamInfo,Username,Message,messages.created from messages,members where messages.IdReceiver=".$_SESSION["IdMember"]." and members.id=messages.IdSender and messages.Status='Sent' and messages.SpamInfo='NotSpam' order by created desc" ;
//			echo "str=$str<br>" ;
	    $qry=sql_query($str) ;
	    while ($rWhile=mysql_fetch_object($qry)) {
	      array_push($TMess,$rWhile) ;
	    }
			break ;
	  case "Sent" :
		  $Title=ww("MessagesThatIHaveSent") ;
			$FromTo="MessageTo" ;
			$str="select messages.id as IdMess,SpamInfo,Username,Message,messages.created from messages,members where messages.IdSender=".$_SESSION["IdMember"]." and members.id=messages.IdReceiver and messages.Status!='Draft'" ;
//			echo "str=$str<br>" ;
	    $qry=sql_query($str) ;
	    while ($rWhile=mysql_fetch_object($qry)) {
	      array_push($TMess,$rWhile) ;
	    }
	
			break ;
	  case "Spam" :
		  $Title=ww("MessagesInSpamFolder") ;
			$FromTo="MessageTo" ;
			$str="select messages.id as IdMess,SpamInfo,Username,WhenFirstRead,Message,messages.created from messages,members where messages.IdSender=".$_SESSION["IdMember"]." and members.id=messages.IdReceiver and messages.SpamInfo!='NotSpam'" ;
//			echo "str=$str<br>" ;
	    $qry=sql_query($str) ;
	    while ($rWhile=mysql_fetch_object($qry)) {
	      array_push($TMess,$rWhile) ;
	    }
	
			break ;
	  case "" : // if empty we will consider member want not read messages
	  case "NotRead" :
		  $Title=ww("MessagesThatIHaveNotRead") ;
			$FromTo="MessageFrom" ;
			$str="select messages.id as IdMess,SpamInfo,Username,WhenFirstRead,Message,messages.created from messages,members where messages.IdReceiver=".$_SESSION["IdMember"]." and members.id=messages.IdSender and messages.Status='Sent' and WhenFirstRead='0000-00-00 00:00:00' order by created desc" ;
//			echo "str=$str<br>" ;
	    $qry=sql_query($str) ;
	    while ($rWhile=mysql_fetch_object($qry)) {
	      array_push($TMess,$rWhile) ;
	    }
			break ;
	  case "Draft" :
		  $Title=ww("MessagesDraft") ;
			$FromTo="MessageTo" ;
			$str="select messages.id as IdMess,SpamInfo,Username,Message,messages.created from messages,members where messages.IdSender=".$_SESSION["IdMember"]." and members.id=messages.IdReceiver and messages.Status='Draft' order by created desc" ;
//			echo "str=$str<br>" ;
	    $qry=sql_query($str) ;
	    while ($rWhile=mysql_fetch_object($qry)) {
	      array_push($TMess,$rWhile) ;
	    }
			break ;
	  case "ShowMessage" :
		  $Title=ww("ShowNotReadMessage",GetParam("IdMess")) ;
			$FromTo="MessageFrom" ;
			$str="select messages.id as IdMess,Username,SpamInfo,Message,messages.created from messages,members where messages.IdReceiver=".$_SESSION["IdMember"]." and members.id=messages.IdSender and messages.Status='Sent' and messages.id=".GetParam("IdMess") ;
	    $qry=sql_query($str) ;
	    $rWhile=mysql_fetch_object($qry) ;
	    array_push($TMess,$rWhile) ;
		  $Title=ww("ShowNotReadMessage",LinkWithUsername($rWhile->Username)) ;
			$str="update messages set WhenFirstRead=now() where id=".GetParam("IdMess") ;
//			echo "str=$str<br>" ;
	    $qry=sql_query($str) ;
			LogStr("Has read message #".GetParam("IdMess"),"readmessage") ;
			EvaluateMyEvents() ; // in order to keep update Not read message counter
      DisplayMyMessages($TMess,$Title,"Received",$FromTo) ;
			exit(0) ;
			break ;
	  case "logout" :
		  Logout("Main.php") ;
			exit(0) ;
	}
	
  DisplayMyMessages($TMess,$Title,GetParam("action"),$FromTo) ;

?>
