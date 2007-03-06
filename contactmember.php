<?php
require_once "lib/init.php";
require_once "lib/FunctionsMessages.php";
require_once "layout/error.php";
include "layout/contactmember.php";
require_once "prepare_profile_header.php";

$IdMember = IdMember(GetParam("cid", 0)); // find the concerned member 
$Message = GetParam("Message", ""); // find the Message
$iMes = GetParam("iMes", 0); // find Message number 
$IdSender = $_SESSION["IdMember"];

MustLogIn() ; // member must login

$m = prepare_profile_header($IdMember,"") ; 

$JoinMemberPictRes="no" ;
if (GetParam("JoinMemberPict")=="on") {
  $JoinMemberPictRes="yes" ;
}

switch (GetParam("action")) {

	case "reply" :
		$rm=LoadRow("select * from messages where id=".GetParam("IdMess")." and IdReceiver=".$IdSender) ;
		$iMes=$rm->id ;
		$tt=array() ;
		$tt=explode("\r\n",$rm->Message) ;
		$max=count($tt) ;
		$Message=">".fUsername($IdMember)." ".$rm->created."\r\n" ; ;
		for ($ii=0;$ii<$max;$ii++) {
			$Message.=">".$tt[$ii]."\r\n" ;
		}

		if ($rm->WhenFirstRead=="0000-00-00 00:00:00") { // set the message to read status if it was not read before
		   $str = "update messages set WhenFirstRead=now() where id=" . GetParam("IdMess")." and IdReceiver=".$IdSender;
		   $qry = sql_query($str);
		   LogStr("Has read message #" . GetParam("IdMess")." (With reply link)", "readmessage");
		}
		
		$Warning="" ;	
//		DisplayContactMember($m, stripslashes($Message), $iMes, $Warning,GetParam("JoinMemberPict"));
		DisplayContactMember($m, stripslashes($Message), 0, $Warning,GetParam("JoinMemberPict"));
		exit(0) ;
	case "edit" :
		$rm=LoadRow("select * from messages where id=".GetParam("iMes")." and Status='Draft'") ;
		$iMes=$rm->id ;
		$Message=$rm->Message ;
		$Warning="" ;
		$m=LoadRow("select * from members where id=".$rm->IdReceiver) ; 
	
		DisplayContactMember($m, stripslashes($Message), $iMes, $Warning,GetParam("JoinMemberPict"));
		exit(0) ;
	case "sendmessage" :
		if (GetParam("IamAwareOfSpamCheckingRules") != "on") { // check if has accepted the vondition of sending
			$Warning = ww("MustAcceptConditionForSending");
			DisplayContactMember($m, stripslashes($Message), $iMes, $Warning,GetParam("JoinMemberPict"));
			exit(0) ;
		}
		$Status = "ToSend"; // todo compute a real status
		
		if ($iMes != 0) { // case there was a draft before
			$str = "update messages set Messages='" . $Message . "',IdReceiver=" . $IdMember . ",IdSender=" . $IdSender . "InFolder='Normal',Status='" . $Status . "',JoinMemberPict='".$JoinMemberPictRes."' where id=".$iMes;
			sql_query($str);
		} else {
			$str = "insert into messages(created,Message,IdReceiver,IdSender,Status,InFolder,JoinMemberPict) values(now(),'" . $Message . "'," . $IdMember . "," . $IdSender.",'".$Status."','Normal','".$JoinMemberPictRes."') ";
			sql_query($str);
			$iMes = mysql_insert_id();
		}
		
		$result = ww("YourMessageWillBeProcessed", $iMes);
		DisplayResult($m, stripslashes($Message), $result);
		exit (0);
	case ww("SaveAsDraft") :
		if ($iMes != 0) {
			$str = "update messages set Messages='" . $Message . "',IdReceiver=" . $IdMember . ",IdSender=" . $IdSender . "InFolder='Draft',Status='Draft'";
			sql_query($str);
		} else {
			$str = "insert into messages(created,Message,IdReceiver,IdSender,Status,InFolder) values(now(),'" . $Message . "'," . $IdMember . "," . $IdSender . ",'Draft','Draft') ";
			sql_query($str);
			$iMes = mysql_insert_id();
		}
		$result = ww("YourMessageIsSavedAsDraft", $iMes);
		DisplayResult($m, $Message, $result);
		exit (0);

}

DisplayContactMember($m, stripslashes($Message), $iMes, "",GetParam("JoinMemberPict"));
?>
