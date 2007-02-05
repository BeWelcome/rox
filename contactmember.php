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

$m = prepare_profile_header($IdMember,$wherestatus) ; 

$JoinMemberPictRes="no" ;
if (GetParam("JoinMemberPict")=="on") {
  $JoinMemberPictRes="yes" ;
}

switch (GetParam("action")) {

	case "edit" :
		$rm=LoadRow("select * from messages where id=".GetParam("iMes")." and Status='Draft'") ;
		$iMes=$rm->id ;
		$Message=$rm->Message ;
		$Warning="" ;
		$m=LoadRow("select * from members where id=".$rm->IdReceiver) ; 
	
		DisplayContactMember($m, $Message, $iMes, $Warning,GetParam("JoinMemberPict"));
		exit(0) ;
	case "sendmessage" :
		if (GetParam("IamAwareOfSpamCheckingRules") != "on") { // check if has accepted the vondition of sending
			$Warning = ww("MustAcceptConditionForSending");
			DisplayContactMember($m, $Message, $iMes, $Warning,GetParam("JoinMemberPict"));
			exit(0) ;
		}
		$Status = "ToSend"; // todo compute a real status
		
		if ($iMes != 0) {
			$str = "update messages set Messages='" . $Message . "',IdReceiver=" . $IdMember . ",IdSender=" . $IdSender . "InFolder='Normal',Status='" . $Status . "',JoinMemberPict='".$JoinMemberPictRes."' where id=".$iMes;
			sql_query($str);
		} else {
			$str = "insert into messages(created,Message,IdReceiver,IdSender,Status,InFolder,JoinMemberPict) values(now(),'" . $Message . "'," . $IdMember . "," . $IdSender.",'".$Status."','Normal','".$JoinMemberPictRes."') ";
			sql_query($str);
			$iMes = mysql_insert_id();
		}
		
		$result = ww("YourMessageWillBeProcessed", $iMes);
		DisplayResult($m, $Message, $result);
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

DisplayContactMember($m, $Message, $iMes, "",GetParam("JoinMemberPict"));
?>
