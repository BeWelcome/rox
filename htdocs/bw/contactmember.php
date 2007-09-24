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
require_once "lib/init.php";
require_once "lib/FunctionsMessages.php";
require_once "layout/error.php";
require_once "layout/contactmember.php";
require_once "lib/prepare_profile_header.php";

$IdMember = IdMember(GetStrParam("cid", 0)); // find the concerned member 
$Message = GetStrParam("Message", ""); // find the Message
$iMes = GetParam("iMes", 0); // find Message number 
$IdSender = $_SESSION["IdMember"];

MustLogIn(); // member must login*

if (!CheckStatus("Active")) { // only Active member can send a Message
	 $errcode = "ErrorYouCantPostWithYourCurrentStatus";
	 DisplayError(ww($errcode));
	 exit (0);
}

$m = prepareProfileHeader($IdMember,""); 

$JoinMemberPictRes="no";
if (GetParam("JoinMemberPict")=="on") {
  $JoinMemberPictRes="yes";
}

switch (GetParam("action")) {

	case "reply" :
		$rm=LoadRow("select * from messages where id=".$iMes." and IdReceiver=".$IdSender);
		$iMes=$rm->id;
		$tt=array();
		$tt=explode("\n",$rm->Message);
		$max=count($tt);
		$Message=">".fUsername($IdMember)." ".$rm->created."\n";;
		for ($ii=0;$ii<$max;$ii++) {
			$Message.=">".$tt[$ii]."\n";
		}

		if ($rm->WhenFirstRead=="0000-00-00 00:00:00") { // set the message to read status if it was not read before
		   $str = "update messages set WhenFirstRead=now() where id=" . $iMes." and IdReceiver=".$IdSender;
		   $qry = sql_query($str);
		   LogStr("Has read message #" . $iMes." (With reply link)", "readmessage");
		}
		
		$Warning="";	
		EvaluateMyEvents(); // Recompute nb mail to read
//		DisplayContactMember($m, stripslashes($Message), $iMes, $Warning,GetParam("JoinMemberPict"));
		DisplayContactMember($m, stripslashes($Message), 0, $Warning,GetStrParam("JoinMemberPict"));
		exit(0);
	case "edit" :
		$rm=LoadRow("select * from messages where id=".$iMes." and Status='Draft'");
		$iMes=$rm->id;
		$Message=$rm->Message;
		$Warning="";
		$m=LoadRow("select * from members where id=".$rm->IdReceiver); 
	
		DisplayContactMember($m, stripslashes($Message), $iMes, $Warning,GetStrParam("JoinMemberPict"));
		exit(0);
	case "sendmessage" :
		if (GetParam("IamAwareOfSpamCheckingRules") != "on") { // check if has accepted the vondition of sending
			$Warning = ww("MustAcceptConditionForSending");
			DisplayContactMember($m, stripslashes($Message), $iMes, $Warning,GetStrParam("JoinMemberPict"));
			exit(0);
		}
		$Status = "ToSend"; // todo compute a real status
		
		if ($iMes != 0) { // case there was a draft before
			$str = "update messages set Messages='" . $Message . "',IdReceiver=" . $IdMember . ",IdSender=" . $IdSender . "InFolder='Normal',Status='',JoinMemberPict='".$JoinMemberPictRes."' where id=".$iMes;
			sql_query($str);
		} else {
			$str = "insert into messages(created,Message,IdReceiver,IdSender,Status,InFolder,JoinMemberPict) values(now(),'" . $Message . "'," . $IdMember . "," . $IdSender.",'','Normal','".$JoinMemberPictRes."') ";
			sql_query($str);
			$iMes = mysql_insert_id();
		}
		
		ComputeSpamCheck($iMes); // Check whether the message is to send or to check
		$result = ww("YourMessageWillBeProcessed",$_SESSION['Username'],$iMes,"<a href=\"member.php?cid=".$m->Username."\">".$m->Username."</a>");
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

DisplayContactMember($m, stripslashes($Message), $iMes, "",GetStrParam("JoinMemberPict"));
?>
