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
require_once "layout/error.php";
require_once "layout/inviteafriend.php";
require_once "lib/prepare_profile_header.php";

MustLogIn(); // member must login

$IdMember = $_SESSION["IdMember"];
$Email = GetStrParam("Email"); // find the email concerned 

$m = prepareProfileHeader($IdMember,"",0); // This is the profile of the member who is going to send the mail

$m->FullName=AdminReadCrypted ($m->FirstName)." ".AdminReadCrypted ($m->SecondName)." ".AdminReadCrypted ($m->LastName);


switch (GetParam("action")) {

	case "Send" : // Send the mail
		$MemberIdLanguage = GetDefaultLanguage($IdMember);
		$subj = ww("MailInviteAFriendSubject", $m->FullName,$_SESSION['Username']);
		$urltosignup = "http://".$_SYSHCVOL['SiteName'] .$_SYSHCVOL['MainDir']. "signup.php";
		$Message=str_replace("\n","<br \>",GetStrParam("Message"));
//		echo $Message;
//		die(0);
		if (GetStrParam("JoinMemberPict")=="on") {
	  	   $rImage=LoadRow("select * from membersphotos where IdMember=".$IdMember." and SortOrder=0");
	  	   $MessageFormatted="<html>\n<head>\n";
	  	   $MessageFormatted.="<title>".$subj."</title>\n</head>\n";
	  	   $MessageFormatted.="<body>\n";
	  	   $MessageFormatted.="<table>\n";

	  	   $MessageFormatted.="<tr><td>\n";
	  	   $MessageFormatted.="<img alt=\"picture of ".$_SESSION['Username']."\" height=\"200px\" src=\"http://".$_SYSHCVOL['SiteName'].$rImage->FilePath."\" />";

	  	   $MessageFormatted.="</td>\n";
	  	   $MessageFormatted.="<td>\n";
	  	   $MessageFormatted.=ww("MailInviteAFriendText", $m->FullName, $Message, $urltosignup);
	  	   $MessageFormatted.="</td>\n";
	  	   $MessageFormatted.="</table>\n";
	  	   $MessageFormatted.="</body>\n";
	  	   $MessageFormatted.="</html>\n";
	  
	  	   $text=$MessageFormatted;
		}
		else {
	  	   $text = ww("MailInviteAFriendText", $m->FullName, $Message, $urltosignup);
	 	}

		$_SERVER['SERVER_NAME'] = "www.bewelcome.org"; // to force because context is not defined

		if (!bw_mail($Email, $subj, $text, "", $_SYSHCVOL['MessageSenderMail'], $MemberIdLanguage, "html", "", "")) {
		   die("\nCannot send message to ".$Email."<br />\n");
		};

		DisplayResults($m,ww("MailSentToFriend",$Message,$Email));
		LogStr("Sending a invite a friend mail to <b>".$Email."</b>","InviteAFriend");
		exit(0);
		break;
}


DisplayForm($m);

?>
