<?php
// Mail bot is a php script used to send automatically the mail
include "lib/dbaccess.php" ;
require_once "lib/FunctionsMessages.php" ;
require_once "layout/Error.php" ;


if (IsLogged()) {
  if (HasRight("RunBot")<=0) {
	  echo "This need right <b>RunBot</b>" ;
		exit(0) ;
	}
}
else {  // case not logged
// todo check if not logged that this script is effectively runned by the cron
} // not logged

$str="select messages.*,Username from messages,members where messages.IdSender=members.id and messages.Status='ToSend'" ;
$qry=sql_query($str) ;
$count=0 ;
while ($rr=mysql_fetch_object($qry)) {
  $MemberIdLanguage=0 ; // todo fin the real default language of the receiver
	$subj=ww("YouveGotAMail",$rr->Username) ;
	$urltoreply=$_SYSHCVOL['SiteName']."/MyMessages.php" ;
	$text=ww("YouveGotAMailText",$rr->Username,$rr->Message,$urltoreply) ;
	hvol_mail($Email,$subj,$text,$hh,$_SYSHCVOL['MessageSenderMail'],$MemberIdLanguage,"","","") ;
	$count++ ;
}
$sResult=$count." Messages sent" ;
if ($count>0) {
  if (IsLogged()) {
	  LogStr("Manual mail triggering ".$sResult,"Sending Mail") ;
	}
	else {
	  LogStr("Auto mail triggering ".$sResult,"Sending Mail") ;
	}
}
echo $sResult ;


?>
