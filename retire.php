<?php
require_once "lib/init.php";
require_once "layout/error.php";
require_once "layout/retire.php";
require_once "lib/prepare_profile_header.php";

MustLogIn(); // member must login

$IdMember = $_SESSION["IdMember"];
$reason = GetStrParam("Reason",""); // find the reason if any 

$m = prepareProfileHeader($IdMember,"",0); // This is the profile of the member who is going to send the mail


switch (GetParam("action")) {

	case "retire" : // Send the mail
		if (GetParam("Complete_retire")=="on") {
			 	 $str="update members set Status='AskToLeave' where members.id=".$IdMember ;
				 sql_query($str) ;
				 $strlog="Members has withraw with reason [<b>".$reason."</b>]" ;
				 $Message=ww("retire_FullWithdrawConfirmation") ;
				 $subj=" Member ".$_SESSION["Username"]." has left bewelcome" ;
		}
		else {
			 	 $str="update members set Status='ChoiceInactive' where members.id=".$IdMember ;
				 sql_query($str) ;
				 LogStr("Members has inactivated his profile with reason [<b>".$reason."</b>]","retire") ;
				 $Message=ww("retire_InactivateProfileConfirmation") ;
				 $subj=" Member ".$_SESSION["Username"]." has inactivated his profile" ;
		}
	  LogStr($strlog,"retire") ;
		bw_mail($_SYSHCVOL['MailToNotifyWhenNewMemberSignup'], $subj, $strlog, "", $_SYSHCVOL['SignupSenderMail'], 0, "html", "", "");

		DeleteLoginInSession() ; // the member will not be anymor logged in
		DisplayResults($m,$Message);
		exit(0);
		break;
}


DisplayForm($m);

?>
