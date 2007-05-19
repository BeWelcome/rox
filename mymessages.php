<?php
require_once "lib/init.php";
require_once "layout/error.php";
require_once "layout/mymessages.php";

// test if is logged, if not logged and forward to the current page
MustLogIn(); // need to be log

// Find parameters
$action = GetParam("action", "");
if ($action == "") { // if no action selected we must choose one to select a tab
	if ($_SESSION['NbNotRead'] > 0) {
		$action = "NotRead";
	} else {
		$action = "Received"; // but we go to Received message if no pending not read
	}
}

$TMess = array ();

$menutab=GetParam("menutab",$action); // find back the previous menutab to display the proper tab menu at refresh, by default it will be $action

switch ($action) {
	case "del" : // if the member requested for a delete message
		$rm = LoadRow("select * from messages where messages.id=" . GetParam("IdMess"));
		if ($rm->IdSender==$_SESSION["IdMember"]) {
		    if ($rm->DeleteRequest!="") {
			    $rm->DeleteRequest.=",";
			}
	    	$DeleteRequest=$rm->DeleteRequest."senderdeleted";
		}
		if ($rm->IdReceiver==$_SESSION["IdMember"]) {
		    if ($rm->DeleteRequest!="") {
			    $rm->DeleteRequest.=",";
			}
	    	$DeleteRequest=$rm->DeleteRequest."receiverdeleted";
		}
		$str="update messages set DeleteRequest='".$DeleteRequest."' where id=". GetParam("IdMess");
		sql_query($str);
		LogStr("Request to delete message #".GetParam("IdMess")." in Tab:".$menutab,"del message");
		$action=$menutab;
		EvaluateMyEvents(); // Recompute nb mail to read
		break;
		
	case "marknospam" : // todo
		$rm = LoadRow("select messages.*,Username from messages,members where messages.IdSender=members.id and messages.id=" . GetParam("IdMess"));
		if ($rm->id) {
			$tt = explode(",", $rm->SpamInfo);
			$SpamInfo = "NotSpam";
			for ($ii = 0; $ii < count($tt); $ii++) {
				if ($tt[$ii] == "NotSpam")
					continue;
				if ($tt[$ii] == "SpamSayMember") // don't keep a SpamSayMember 
					continue;
				if ($tt[$ii] == "SpamSayChecker") // don't keep a SpamSayChecker 
					continue;
				if ($SpamInfo != "")
					$SpamInfo .= ",";
				$SpamInfo .= $SpamInfo . $tt[$ii];
			}
			$str = "update messages set SpamInfo='".$SpamInfo."',InFolder='Normal' where id=" . $rm->id . " and messages.IdReceiver=" . $_SESSION["IdMember"] . " ";
			sql_query($str);
			echo "removed";
			LogStr("Remove spam mark (".$rm->SpamInfo.") a message from " . $rm->Username . " MesId=#" . $rm->id, "Remove Mark Spam");
		}
		$action=$menutab;
		break;

	case "markspam" :
		$rm = LoadRow("select messages.*,Username from messages,members where messages.IdSender=members.id and messages.id=" . GetParam("IdMess"));
		if ($rm->id) {
			$tt = explode(",", $rm->SpamInfo);
			$SpamInfo = "SpamSayMember";
			for ($ii = 0; $ii < count($tt); $ii++) {
				if ($tt[$ii] == "NotSpam") // a NoSpam will not be kept
					continue;
				if ($tt[$ii] == "SpamSayMember") // if Already set don't set it again
					continue;
				$SpamInfo .= $SpamInfo . $tt[$ii];
			}
			$str = "update messages set SpamInfo='" . $SpamInfo . "',InFolder='Spam' where id=" . $rm->id . " and messages.IdReceiver=" . $_SESSION["IdMember"] . " ";
			sql_query($str);
			LogStr("Mark as spam a message from " . $rm->Username . " MesId=#" . $rm->id, "Mark Spam");
		}
		$action=$menutab;
		break;
	case "reply" :
		echo "not yet ready";
		exit (0);
	case "" : // if empty we will consider member want Received Messages
	case "ShowMessage" :
		$Title = ww("ShowNotReadMessage", GetParam("IdMess"));
		$FromTo = "MessageFrom";
		$str = "select messages.id as IdMess,Username,SpamInfo,Message,messages.created from messages,members where messages.IdReceiver=" . $_SESSION["IdMember"] . " and members.id=messages.IdSender and messages.Status='Sent' and (not FIND_IN_SET('receiverdeleted',DeleteRequest)) and messages.id=" . GetParam("IdMess")." order by messages.id desc";
		$qry = sql_query($str);
		$rWhile = mysql_fetch_object($qry);
		array_push($TMess, $rWhile);
		$Title = ww("ShowNotReadMessage", LinkWithUsername($rWhile->Username));
		$str = "update messages set WhenFirstRead=now() where id=" . GetParam("IdMess");
		//			echo "str=$str<br>";
		$qry = sql_query($str);
		LogStr("Has read message #" . GetParam("IdMess"), "readmessage");
		EvaluateMyEvents(); // in order to keep update Not read message counter
		DisplayMyMessages($TMess, $Title, "Received", $FromTo);
		EvaluateMyEvents(); // Recompute nb mail to read
		exit (0);
		break;
}

// This is a second switch action because previous one can have alterated $action
switch ($action) {
	case "Received" :
		$Title = ww("MessagesThatIHaveReceived");
		$FromTo = "MessageFrom";
		$str = "select messages.id as IdMess,SpamInfo,Username,Message,messages.created from messages,members where messages.IdReceiver=" . $_SESSION["IdMember"] . " and members.id=messages.IdSender and messages.Status='Sent' and messages.SpamInfo='NotSpam' and (not FIND_IN_SET('receiverdeleted',DeleteRequest)) order by created desc";
		//			echo "str=$str<br>";
		$qry = sql_query($str);
		while ($rWhile = mysql_fetch_object($qry)) {
			array_push($TMess, $rWhile);
		}
		break;
	case "Sent" :
		$Title = ww("MessagesThatIHaveSent");
		$FromTo = "MessageTo";
		$str = "select messages.id as IdMess,SpamInfo,Username,Message,messages.created from messages,members where messages.IdSender=" . $_SESSION["IdMember"] . " and members.id=messages.IdReceiver and (not FIND_IN_SET('senderdeleted',DeleteRequest)) and messages.Status!='Draft'";
//					echo "str=$str<br>";
		$qry = sql_query($str);
		while ($rWhile = mysql_fetch_object($qry)) {
			array_push($TMess, $rWhile);
		}

		break;
	case "Spam" :
		$Title = ww("PageSpamFolderTitle");
		$FromTo = "MessageTo";
		$str = "select messages.id as IdMess,SpamInfo,Username,WhenFirstRead,Message,messages.created from messages,members where messages.IdReceiver=" . $_SESSION["IdMember"] . " and members.id=messages.IdSender and (not FIND_IN_SET('receiverdeleted',DeleteRequest)) and messages.SpamInfo!='NotSpam'";
		//			echo "str=$str<br>";
		$qry = sql_query($str);
		while ($rWhile = mysql_fetch_object($qry)) {
			array_push($TMess, $rWhile);
		}

		break;
	case "NotRead" :
		$Title = ww("MessagesThatIHaveNotRead");
		$FromTo = "MessageFrom";
		$str = "select messages.id as IdMess,SpamInfo,Username,WhenFirstRead,Message,messages.created from messages,members where messages.IdReceiver=" . $_SESSION["IdMember"] . " and members.id=messages.IdSender and messages.Status='Sent' and WhenFirstRead='0000-00-00 00:00:00' and (not FIND_IN_SET('receiverdeleted',DeleteRequest)) order by created desc";
		//			echo "str=$str<br>";
		$qry = sql_query($str);
		while ($rWhile = mysql_fetch_object($qry)) {
			array_push($TMess, $rWhile);
		}
		break;
	case "Draft" :
		$Title = ww("MessagesDraft");
		$FromTo = "MessageTo";
		$str = "select messages.id as IdMess,messages.Status as Status,SpamInfo,Username,Message,messages.created from messages,members where messages.IdSender=" . $_SESSION["IdMember"] . " and members.id=messages.IdReceiver and messages.Status='Draft' and (not FIND_IN_SET('senderdeleted',DeleteRequest)) order by created desc";
		//			echo "str=$str<br>";
		$qry = sql_query($str);
		while ($rWhile = mysql_fetch_object($qry)) {
			array_push($TMess, $rWhile);
		}
		break;
}

DisplayMyMessages($TMess, $Title, $menutab, $FromTo);
?>
