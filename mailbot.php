<?php

// Mail bot is a php script used to send automatically the mail
require_once "lib/init.php";
require_once "lib/FunctionsMessages.php";
require_once "layout/error.php";

if (IsLoggedIn()) {
	if (HasRight("RunBot") <= 0) {
		echo "This need right <b>RunBot</b>";
		exit (0);
	}
	$IdTriggerer = $_SESSION['IdMember'];
} else { // case not logged
	// todo check if not logged that this script is effectively runned by the cron
	$IdTriggerer = 0; /// todo here need to set the Bot id
	$_SESSION['IdMember'] = 0;
} // not logged

$str = "select messages.*,Username from messages,members where messages.IdSender=members.id and messages.Status='ToSend'";
$qry = sql_query($str);

$count = 0;
while ($rr = mysql_fetch_object($qry)) {
	$Email = GetEmail($rr->IdReceiver);
	$MemberIdLanguage = GetDefaultLanguage($rr->IdReceiver);
	$subj = ww("YouveGotAMail", $rr->Username);
	$urltoreply = "http://".$_SYSHCVOL['SiteName'] .$_SYSHCVOL['MainDir']. "contactmember.php?action=reply&cid=".$rr->Username."&IdMess=".$rr->id ;
	$MessageFormatted=$rr->Message ;
	if ($rr->JoinMemberPict=="yes") {
	  $rImage=LoadRow("select * from membersphotos where IdMember=".$rr->IdSender." and SortOrder=0") ;
	  $MessageFormatted="<html>\n<head>\n" ;
	  $MessageFormatted.="<title>".$subj."</title>\n</head>\n" ;
	  $MessageFormatted.="<body>\n" ;
	  $MessageFormatted.="<table>\n" ;

	  $MessageFormatted.="<tr><td>\n" ;
	  $MessageFormatted.="<img alt=\"picture of ".$rr->Username."\" height=\"200px\" src=\"http://".$_SYSHCVOL['SiteName'].$rImage->FilePath."\" />" ;

	  $MessageFormatted.="</td>\n" ;
	  $MessageFormatted.="<td>\n" ;
	  $MessageFormatted.=ww("YouveGotAMailText", $rr->Username, $rr->Message, $urltoreply) ;
	  $MessageFormatted.="</td>\n" ;
	  $MessageFormatted.="</table>\n" ;
	  $MessageFormatted.="</body>\n" ;
	  $MessageFormatted.="</html>\n" ;
	  
	  $text=$MessageFormatted ;
	}
	else {
	  $text = ww("YouveGotAMailText", $rr->Username, $MessageFormatted, $urltoreply);
	 }

	$_SERVER['SERVER_NAME'] = "www.bewelcome.org"; // to force because context is not defined

	if (!bw_mail($Email, $subj, $text, "", $_SYSHCVOL['MessageSenderMail'], $MemberIdLanguage, "html", "", "")) {
		die("\nCannot send messages.id=#" . $rr->id . "<br>\n");
	};
	$str = "update messages set Status='Sent',IdTriggerer=" . $IdTriggerer . ",DateSent=now() where id=" . $rr->id;
	sql_query($str);

	$count++;
}
$sResult = $count . " Messages sent";

if (IsLoggedIn()) {
	LogStr("Manual mail triggering " . $sResult, "Sending Mail");
	echo $sResult;
} else {
	LogStr("Auto mail triggering " . $sResult, "Sending Mail");
}
?>
