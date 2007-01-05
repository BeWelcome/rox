<?php
// Mail bot is a php script used to send automatically the mail
include "lib/dbaccess.php" ;
require_once "lib/FunctionsMessages.php" ;
require_once "layout/error.php" ;


if (IsLogged()) {
  if (HasRight("RunBot")<=0) {
	  echo "This need right <b>RunBot</b>" ;
		exit(0) ;
	}
	$IdTriggerer=$_SESSION['IdMember'] ;
}
else {  // case not logged
// todo check if not logged that this script is effectively runned by the cron
	$IdTriggerer=0 ; /// todo here need to set the Bot id
  $_SESSION['IdMember']=0  ;
} // not logged

$str="select messages.*,Username from messages,members where messages.IdSender=members.id and messages.Status='ToSend'" ;
$qry=sql_query($str) ;

$count=0 ;
while ($rr=mysql_fetch_object($qry)) {
  $Email=GetEmail($rr->IdReceiver) ;
	$MemberIdLanguage=GetDefaultLanguage($rr->IdReceiver) ;
	$subj=ww("YouveGotAMail",$rr->Username) ;
	$urltoreply=$_SYSHCVOL['SiteName']."/MyMessages.php" ;
	$text=ww("YouveGotAMailText",$rr->Username,$rr->Message,$urltoreply) ;
	if (!hvol_mail($Email,$subj,$text,$hh,$_SYSHCVOL['MessageSenderMail'],$MemberIdLanguage,"","","")) {
	  die ("Cant send messages.id=#$rr->IdMessage\n") ;
	};
	$str="update messages set Status='Sent',IdTriggerer=".$IdTriggerer.",DateSent=now() where id=".$rr->id ;
	sql_query($str) ;
	
	$count++ ;
}
$sResult=$count." Messages sent" ;

if (IsLogged()) {
	  LogStr("Manual mail triggering ".$sResult,"Sending Mail") ;
    echo $sResult ;
}
else {
	  LogStr("Auto mail triggering ".$sResult,"Sending Mail") ;
}



?>
