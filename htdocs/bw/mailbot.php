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
	if (!bw_mail($Email, $subj, $text, "", "newsletter@bewelcome.org", $MemberIdLanguage, "html", "", "")) {
		bw_error("\nCannot send broadcastmessages.id=#" . $rr->IdBroadcast . "<br />\n");
	}
	else {
		 $countbroadcast++ ;
	}
	$str = "update broadcastmessages set Status='Sent' where IdBroadcast=" . $rr->IdBroadcast." and IdReceiver=".$rr->IdReceiver;
	sql_query($str);
}


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
	   LogStr("Mailbot refuse to send message #".$rr->id." Message from ".$rr->Username." is rejected (".$rr->MemberStatus.")","Sending Mail");
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
	  $MessageFormatted.="<img alt=\"picture of ".$rr->Username."\" height=\"200px\" src=\"http://".$_SYSHCVOL['SiteName'].$rImage->FilePath."\" />";

	  $MessageFormatted.="</td>";
	  $MessageFormatted.="<td>";
//	  $MessageFormatted.=ww("YouveGotAMailText", $rr->Username, $rr->Message, $urltoreply);
	  $MessageFormatted.=ww("mailbot_YouveGotAMailText", fUsername($rr->IdReceiver),$rr->Username, $rr->Message, $urltoreply,$rr->Username,$rr->Username);
	  $MessageFormatted.="</td>";
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
		bw_error("\nCannot send messages.id=#" . $rr->id . "<br />\n");
	};
	$str = "update messages set Status='Sent',IdTriggerer=" . $IdTriggerer . ",DateSent=now() where id=" . $rr->id;
	sql_query($str);

	$count++;
}
// and for Test server
	$str = "update hcvoltest.messages set Status='Sent',IdTriggerer=" . $IdTriggerer . ",DateSent=now() where Status='ToSend'";
	sql_query($str);
	
$sResult = $count . " intermember Messages sent";
if ($countbroadcast>0) {
	$sResult=$sResult. " and ".$countbroadcast. " broadcast messages sent" ;
} 


if (IsLoggedIn()) {
	LogStr("Manual mail triggering " . $sResult, "Sending Mail");
	echo $sResult;
	echo "<br>\$_SYSHCVOL['MessageSenderMail']=",$_SYSHCVOL['MessageSenderMail'] ;
} else {
	LogStr("Auto mail triggering " . $sResult, "Sending Mail");
}
?>
