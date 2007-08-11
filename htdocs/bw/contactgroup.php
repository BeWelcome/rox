<?php
require_once "lib/init.php";
require_once "lib/FunctionsMessages.php";
require_once "layout/error.php";
require_once "layout/contactgroup.php";
require_once "lib/prepare_profile_header.php";

$IdGroup = IdGroup(GetParam("IdGroup", 0)); // find the concerned member 
$Message = GetStrParam("Message", ""); // find the Message
$Title = GetStrParam("Title", ""); // find the Message
$iMes = GetParam("iMes", 0); // find Message number 
$IdSender = $_SESSION["IdMember"];

MustLogIn(); // member must login

if (!CheckStatus("Active")) { // only Active member can send a Message
	 $errcode = "ErrorYouCantPostWithYourCurrentStatus";
	 DisplayError(ww($errcode));
	 exit (0);
}

$JoinMemberPictRes="no";
if (GetStrParam("JoinMemberPict")=="on") {
  $JoinMemberPictRes="yes";
}

$rr=LoadRow("select id from membersgroups where IdGroup=".$IdGroup." and IdMember=".$IdSender." and CanSendGroupMessage='no' and IacceptMassMailFromThisGroup='yes'");
if (isset($rr->id)) {
	$errcode = "ErrorYouCantPostToThisGroup";
	DisplayError(ww($errcode));
	exit (0);
}

switch (GetParam("action")) {

	case "sendmessage" :
	
		$group=LoadRow("select * from groups where id=".$IdGroup);
		
		if (GetStrParam("IamAwareOfSpamCheckingRules") != "on") { // check if has accepted the vondition of sending
			$Warning = ww("MustAcceptConditionForSending");
			DisplayContactGroup( stripslashes($Title), stripslashes($Message),  $Warning,GetStrParam("JoinMemberPict"));
			exit(0);
		}
		$count=0;
		$Status = "ToSend"; // todo compute a real status
		
		$str="select membersgroups.*,groups.Name from membersgroups,groups where groups.id=membersgroups.IdGroup and IdGroup=".$IdGroup." and IacceptMassMailFromThisGroup='yes'";
	    $SenderMail=GetEmail($IdSender); 
		$qry=sql_query($str);
		while ($rr=mysql_fetch_object($qry)) {
		   $defLanguage=GetDefaultLanguage($rr->IdMember);
		   $groupname=wwinlang("Group_" . $rr->Name,$defLanguage);
		   $count++;
		   $subj = "BW group ".$groupname." : ".stripslashes($Title);
		   $text = stripslashes($Message);
	       $Email=GetEmail($rr->IdMember);
		   bw_mail($Email, $subj, $text, "", $SenderMail, $defLanguage, "html", "", "");
//echo "send to ".$Email." <br>".$subj."<br>".$text."<br>from ".$SenderMail."<br>\n";
		}
		$str="INSERT INTO groupsmessages ( id , created , Title ,Message , IdSender , IdGroup ) VALUES ( NULL , NOW( ) , '".$Title."', '".$Message."', ".$IdSender.",".$IdGroup.")";
		sql_query($str); // store sent message 

		if ($count>0) LogStr("sending ".stripslashes($Title)."<br>".stripslashes($Message)."<br> to ".$count." People","GroupMessage"); 
		
		
		$result = ww("MessageSentToXCount",$count);
		DisplayResult($IdGroup,stripslashes($Title),stripslashes($Message), $result);
		exit (0);
}

DisplayContactGroup($IdGroup,"","", "",GetStrParam("JoinMemberPict"));
?>
