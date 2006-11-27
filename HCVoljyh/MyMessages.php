<?php
include "lib/dbaccess.php" ;
require_once "lib/FunctionsTools.php" ;
require_once "lib/FunctionsLogin.php" ;
require_once "layout/Error.php" ;
  include "layout/MyMessages.php" ;


// Find parameters

	
	$TMess=array() ;
		
  switch(GetParam("action")) {
	  case "Received" :
		  $Title=ww("MessagesThatIHaveReceived") ;
			$FromTo="MessageFrom" ;
			break ;
	  case "Sent" :
		  $Title=ww("MessagesThatIHaveSent") ;
			$FromTo="MessageTo" ;
			$str="select Username,Message,messages.created from messages,members where messages.IdSender=".$_SESSION["IdMember"]." and members.id=messages.IdReceiver and messages.Status!='Draft'" ;
			echo "str=$str<br>" ;
	    $qry=mysql_query($str) ;
	    while ($rWhile=mysql_fetch_object($qry)) {
	      array_push($TMess,$rWhile) ;
	    }
	
			break ;
	  case "NotRead" :
		  $Title=ww("MessagesThatIHaveNotRead") ;
			break ;
	  case "Draft" :
		  $Title=ww("MessagesDraft") ;
			break ;
	  case "ShowMessage" :
		  $Title=ww("ShowMessage",$iMes) ;
			break ;
	  case "logout" :
		  Logout("Main.php") ;
			exit(0) ;
	}
	
  DisplayMyMessages($TMess,$Title,$action,$FromTo) ;

?>
