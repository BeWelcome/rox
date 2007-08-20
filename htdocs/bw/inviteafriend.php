<?php
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
