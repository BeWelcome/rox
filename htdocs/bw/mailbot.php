<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/

// Mail bot is a php script used to send automatically the mail

// tell the init.php that this is a mailbot
// (so it does not run dbupdate.php)

$i_am_the_mailbot = true;

require_once "lib/init.php";
require_once "lib/FunctionsMessages.php";
require_once "layout/error.php";

if (IsLoggedIn()) {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
<title>Mail bot manual page</title>
</head>
<body>
<?php	 
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


// -----------------------------------------------------------------------------
// broadcast messages for members
// -----------------------------------------------------------------------------
$str = "select broadcastmessages.*,Username,members.Status as MemberStatus ,broadcast.Name as word from broadcast,broadcastmessages,members where broadcast.id=broadcastmessages.IdBroadcast and broadcastmessages.IdReceiver=members.id and broadcastmessages.Status='ToSend'";
$qry = sql_query($str);

$countbroadcast = 0;
while ($rr = mysql_fetch_object($qry)) {
	$Email = GetEmail($rr->IdReceiver);
	$MemberIdLanguage = GetDefaultLanguage($rr->IdReceiver);
	
	$subj = wwinlang("BroadCast_Title_".$rr->word,$MemberIdLanguage, $rr->Username);
	$text = wwinlang("BroadCast_Body_".$rr->word,$MemberIdLanguage, $rr->Username);
//	if (!bw_mail($Email, $subj, $text, "", $_SYSHCVOL['MessageSenderMail'], $MemberIdLanguage, "html", "", "")) {
	if (!bw_mail($Email, $subj, $text, "", "info@bewelcome.org", $MemberIdLanguage, "html", "", "")) {
		$str = "update broadcastmessages set Status='Failed' where IdBroadcast=" . $rr->IdBroadcast." and IdReceiver=".$rr->IdReceiver;
		LogStr("Cannot send broadcastmessages.id=#" . $rr->IdBroadcast . " to <b>".$rr->Username."</b> \$Email=[".$Email."]","mailbot");
	}
	else {
		 $str = "update broadcastmessages set Status='Sent' where IdBroadcast=" . $rr->IdBroadcast." and IdReceiver=".$rr->IdReceiver;
		 $countbroadcast++ ;
	}
	sql_query($str);
}


// -----------------------------------------------------------------------------
// Forum notifications
// -----------------------------------------------------------------------------
$str = "select posts_notificationqueue.*,Username from posts_notificationqueue,members where posts_notificationqueue.IdMember=members.id and (members.Status='Active' or members.Status='ActiveHidden') and posts_notificationqueue.Status='ToSend'";
$qry = sql_query($str);

$countposts_notificationqueue = 0;
while ($rr = mysql_fetch_object($qry)) {
	$Email = GetEmail($rr->IdMember);
	$MemberIdLanguage = GetDefaultLanguage($rr->IdMember);

	$rPost=LoadRow("select forums_posts.*,members.Username,members.id as IdMember,forums_threads.title as thread_title,forums_threads.threadid as IdThread,forums_posts.message,cities.Name as cityname,countries.Name as countryname from cities,countries,forums_posts,forums_threads,members,user where forums_threads.threadid=forums_posts.threadid and forums_posts.authorid=user.id and members.Username=user.handle and forums_posts.postid=".$rr->IdPost." and cities.id=members.IdCity and countries.id=cities.IdCountry") ; 
	$rImage=LoadRow("select * from membersphotos where IdMember=".$rPost->IdMember." and SortOrder=0");
	
	$UnsubscribeLink="" ;
	if ($rr->IdSubscription!=0) { // Compute the unsubscribe link according to the table where the subscription was coming from
	   $rSubscription=LoadRow("select * from ".$rr->TableSubscription." where id=".$rr->IdSubscription) ;
	   if ($rr->TableSubscription=="members_threads_subscribed") {
	   	  $UnsubscribeLink="<a href=\"http://".$_SYSHCVOL['SiteName']."/forums/subscriptions/unsubscribe/thread/".$rSubscription->id."/".$rSubscription->UnSubscribeKey."\">".wwinlang("ForumUnSubscribe",$MemberIdLanguage)."</a>" ;
	   }
	}
	
	$NotificationType=$rr->Type ;

	switch($rr->Type) {
	
		case 'newthread' :
//			 $subj = wwinlang("ForumNotification_Title_newthread",$MemberIdLanguage, $ForumSenderUsername->Username);
//			 $text = wwinlang("ForumNotification_Body",$MemberIdLanguage,$rr->Username,$rr->type);
			 $NotificationType=wwinlang("ForumMailbotNewThread",$MemberIdLanguage) ;
			 break ;
		case 'reply':
			 $NotificationType=wwinlang("ForumMailbotReply",$MemberIdLanguage) ;
			 break ;
		case 'moderatoraction':
		case 'deletepost':
		case 'deletethread':
		case 'useredit':
			 $NotificationType=wwinlang("ForumMailbotEditedPost",$MemberIdLanguage) ;
			 break ;
		case 'translation':
			 break ;
		case 'buggy' :
		default :
	   		LogStr("problem with posts_notificationqueue \$Type=".$rr->Type."for id #".$rr->id,"mailbot");
			$text="Problem in forum notification Type=".$rr->Type."<br />" ;
			break ;
		
	}

// Setting some default values
	$subj = "Forum Bewelcome, ".$NotificationType.":".$rPost->thread_title." from ".$rPost->Username ; 
	$text="<html><head>";
	$text.="<title>".$subj."</title></head>";
	$text.="<body>";
	
	$text .= "<table border=\"0\" cellpadding=\"0\" cellspacing=\"10\" width=\"700\" style=\"margin: 20px; background-color: #fff; font-family:Arial, Helvetica, sans-serif; font-size:12px; color: #333;\" align=\"left\">" ;
	$text .= "<tr><th colspan=\"2\"  align=\"left\"><a href=\"http://".$_SYSHCVOL['SiteName']."/forums/s".$rPost->IdThread."\">".$rPost->thread_title."</a></th></tr>" ;
	$text .= "<tr><td colspan=\"2\">from: <a href=\"http://".$_SYSHCVOL['SiteName']."/member.php?cid=".$rPost->Username."\">".$rPost->Username."</a> ".$rPost->countryname."(".$rPost->cityname.")</td></tr>" ;
	$text .= "<tr><td valign=\"top\">" ;
	if (isset($rImage->FilePath)) {
	   $text.="<img alt=\"picture of ".$rPost->Username."\" height=\"150px\" src=\"http://".$_SYSHCVOL['SiteName'].$rImage->FilePath."\" />";
	}
	else {
	   $text.="<img alt=\"Bewelcome\" src=\"http://www.bewelcome.org/styles/YAML/images/logo.gif\" />";
	}
	$text .="</td><td>".$rPost->message."</td></tr>" ;
	if ($UnsubscribeLink!="") {
	   $text = $text."<tr><td colspan=\"2\">".$UnsubscribeLink."</td></tr>" ;
	}
	else { // This case should be for moderators only
		 $text .= "<tr><td colspan=\"2\"> IdPost #".$rr->IdPost." action=".$NotificationType."</td></tr>" ;
	}
	$text .= "</table>" ;
	$text.="</body></html>";
	
	if (!bw_mail($Email, $subj, $text, "", "forum@bewelcome.org", $MemberIdLanguage, "html", "", "")) {
		LogStr("Cannot send posts_notificationqueue=#" . $rr->id . " to <b>".$rPost->Username."</b> \$Email=[".$Email."]","mailbot");
		 // Telling that the notification has been not sent
		 $str = "update posts_notificationqueue set posts_notificationqueue.Status='Failed' where posts_notificationqueue.id=".$rr->id ;
	}
	else {
		 $countposts_notificationqueue++ ;
		 // Telling that the notification has been sent
		 $str = "update posts_notificationqueue set posts_notificationqueue.Status='Sent' where posts_notificationqueue.id=".$rr->id ;
	}
	sql_query($str);
}
$sResult = $countposts_notificationqueue . " forum notification sent <br \>";


// -----------------------------------------------------------------------------
// Normal messages between members
// -----------------------------------------------------------------------------

$str = "select messages.*,Username,members.Status as MemberStatus from messages,members where messages.IdSender=members.id and messages.Status='ToSend'";
$qry = sql_query($str);

$count = 0;
while ($rr = mysql_fetch_object($qry)) {
	if (($rr->MemberStatus!='Active')and ($rr->MemberStatus!='ActiveHidden')) {  // Messages from not actived members will not be send this can happen because a member can have been just banned
	   if (IsLoggedIn()) {
	   	  echo "Message from ".$rr->Username." is rejected (".$rr->MemberStatus.")" ;
	   }
	   $str="Update messages set Status='Freeze' where id=".$rr->id ; 
      sql_query($str);
	   LogStr("Mailbot refuse to send message #".$rr->id." Message from ".$rr->Username." is rejected (".$rr->MemberStatus.")","mailbot");
	   continue ;
	} 
	 
	$Email = GetEmail($rr->IdReceiver);
	$MemberIdLanguage = GetDefaultLanguage($rr->IdReceiver);
	$subj = ww("YouveGotAMail", $rr->Username);
	$urltoreply = "http://".$_SYSHCVOL['SiteName'] .$_SYSHCVOL['MainDir']. "contactmember.php?action=reply&cid=".$rr->Username."&iMes=".$rr->id;
	$MessageFormatted=$rr->Message;
	if ($rr->JoinMemberPict=="yes") {
	  $rImage=LoadRow("select * from membersphotos where IdMember=".$rr->IdSender." and SortOrder=0");
	  $MessageFormatted="<html><head>";
	  $MessageFormatted.="<title>".$subj."</title></head>";
	  $MessageFormatted.="<body>";
	  $MessageFormatted.="<table>";

	  $MessageFormatted.="<tr><td>";
	  if (isset($rImage->FilePath)) $MessageFormatted.="<img alt=\"picture of ".$rr->Username."\" height=\"200px\" src=\"http://".$_SYSHCVOL['SiteName'].$rImage->FilePath."\" />";

	  $MessageFormatted.="</td>";
	  $MessageFormatted.="<td>";
//	  $MessageFormatted.=ww("YouveGotAMailText", $rr->Username, $rr->Message, $urltoreply);
	  $MessageFormatted.=ww("mailbot_YouveGotAMailText", fUsername($rr->IdReceiver),$rr->Username, $rr->Message, $urltoreply,$rr->Username,$rr->Username);
	  $MessageFormatted.="</td>";

if (IsLoggedIn()) { // In this case we display the tracks for the admin who will probably need to check who is sending for sure and who is not
	 		echo " from ".$rr->Username." to ".fUsername($rr->IdReceiver). " email=".$Email,"<br>" ;
}
		if ((isset($rr->JoinSenderMail)) and ($rr->JoinSenderMail=="yes")) { // Preparing what is needed in case a joind sender mail option was added
			 $MessageFormatted= $MessageFormatted."<tr><td colspan=2>".ww("mailbot_JoinSenderMail",$rr->Username,GetEmail($rr->IdSender))."</td>" ;
		}

	  $MessageFormatted.="</table>";
	  $MessageFormatted.="</body>";
	  $MessageFormatted.="</html>";
	  
	  $text=$MessageFormatted;
	}
	else {
//	  $text = ww("YouveGotAMailText", $rr->Username, $MessageFormatted, $urltoreply);
	  $text=ww("mailbot_YouveGotAMailText", fUsername($rr->IdReceiver),$rr->Username, $rr->Message, $urltoreply,$rr->Username,$rr->Username);
	 }

	$_SERVER['SERVER_NAME'] = "www.bewelcome.org"; // to force because context is not defined

	if (!bw_mail($Email, $subj, $text, "", $_SYSHCVOL['MessageSenderMail'], $MemberIdLanguage, "html", "", "")) {
		 	 LogStr("Cannot send messages.id=#" . $rr->id . " to <b>".$rr->Username."</b> \$Email=[".$Email."]","mailbot");
			 $str = "update messages set Status='Failed' where id=" . $rr->id;
	}
	else {
			 $str = "update messages set Status='Sent',IdTriggerer=" . $IdTriggerer . ",DateSent=now() where id=" . $rr->id;
			 $count++;
	}
	sql_query($str);

}
// and for Test server
	$str = "update hcvoltest.messages set Status='Sent',IdTriggerer=" . $IdTriggerer . ",DateSent=now() where Status='ToSend'";
	sql_query($str);
	
$sResult = $sResult.$count . " intermember Messages sent";
if ($countbroadcast>0) {
	$sResult=$sResult. " and ".$countbroadcast. " broadcast messages sent" ;
} 


if (IsLoggedIn()) {
	LogStr("Manual mail triggering " . $sResult, "mailbot");
	echo $sResult;
	echo "<br>\$_SYSHCVOL['MessageSenderMail']=",$_SYSHCVOL['MessageSenderMail'] ;
?>
</body></html>
<?php	 
} else {
	LogStr("Auto mail triggering " . $sResult, "mailbot");
}
?>
