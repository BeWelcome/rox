<?php


//------------------------------------------------------------------------------
// This library file contains message relative files 
//------------------------------------------------------------------------------

//------------------------------------------------------------------------------
// ComputeSpamCheck read a message in the database and according to specific rules
// set the SpamInfo  

// Rule is :

// if sender has Flag NeverCheckSendMail : mail is always set to ToSend, with noSPam
// Test badword for SpamDetection
// if sender has Flag AlwayCheckSendMail : mail is always set to toCheck
// if receiver has preference PreferenceCheckMyMail set to "Yes"  : mail is always set to toCheck
function ComputeSpamCheck($IdMess) {
	$Mes=LoadRow("select * from messages where id=".$IdMess);
	if (isset ($Mes->id)) {
		$CheckerComment=$Mes->CheckerComment;

// Case NeverCheckSendMail
	    if (HasFlag("NeverCheckSendMail","",$Mes->IdSender)) {
		      $Status = 'ToSend';
			  $SpamInfo = "NotSpam";
		      $CheckerComment.="Sent by member with NeverCheckSendMail \n";
			  $str = "update messages set Status='".$Status."',CheckerComment='".$CheckerComment."',SpamInfo='" . $SpamInfo . "' where id=" . $Mes->id . " and Status!='Sent'";
			  sql_query($str);
			  LogStr("NeverCheckSendMail for message #".$IdMess." from <b>".fUsername($Mes->IdSender)."</b> to <b>".fUsername($Mes->IdReceiver)."</b>","AutoSpamCheck");
			  return($Status);
		}
		
		
		
// Test what the Spam mark should be
		$SpamInfo = "NotSpam"; // By default its not a Spam
		$tt=explode(";",wwinlang("MessageBlackWord",0));
		$max=count($tt);
		for ($ii=0;$ii<$max;$ii++) {
			if ((strstr($Mes->Message,$tt[$ii])!="")and($tt[$ii]!="")) {
				$SpamInfo = "SpamBlkWord";
				$CheckerComment.="Has BlackWord <b>".$tt[$ii]."</b>\n";
			}
		}

		$tt=explode(";",wwinlang("MessageBlackWord",GetDefaultLanguage($Mes->IdSender)));
		$max=count($tt);
		for ($ii=0;$ii<$max;$ii++) {
			if ((strstr($Mes->Message,$tt[$ii])!="")and($tt[$ii]!="")) {
				$SpamInfo = "SpamBlkWord";
				$CheckerComment.="Has BlackWord (in sender language)<b>".$tt[$ii]."</b>\n";
			}
		}
// End of Test what the Spam mark should be


// Case AlwayCheckSendMail
	    if (HasFlag("AlwayCheckSendMail","",$Mes->IdSender)) {
		      $Status = 'ToCheck';
		      $CheckerComment.="Sent by member with AlwayCheckSendMail \n";
			  $str = "update messages set Status='".$Status."',CheckerComment='".$CheckerComment."',SpamInfo='" . $SpamInfo . "' where id=" . $Mes->id . " and Status!='Sent'";
			  sql_query($str);
			  LogStr("AlwayCheckSendMail for message #".$IdMess." from <b>".fUsername($Mes->IdSender)."</b> to <b>".fUsername($Mes->IdReceiver)."</b>","AutoSpamCheck");
			  return($Status);
		}

// Case if receiver has preference PreferenceCheckMyMail set to "Yes"  : mail is always set to toCheck
		$rPrefCheckMyMail = LoadRow("select *  from memberspreferences where IdMember=" . $Mes->IdReceiver . " and IdPreference=4"); // PreferenceCheckMyMail --> IdPref=4
		if ($rPrefCheckMyMail->Value == 'Yes') { // if member has choosen CheckMyMail
			$Status = 'ToCheck';
			$CheckerComment.="Member has asked for checking\n";
			$str = "update messages set Status='".$Status."',CheckerComment='".$CheckerComment."',SpamInfo='" . $SpamInfo . "' where id=" . $Mes->id . " and Status!='Sent'";
			sql_query($str);
			LogStr("PreferenceCheckMyMail for message #".$IdMess." from <b>".fUsername($Mes->IdSender)."</b> to <b>".fUsername($Mes->IdReceiver)."</b>","AutoSpamCheck");
			return($Status);
		}
		

// Default case
		$Status = 'ToSend';
		$str = "update messages set Status='".$Status."',CheckerComment='".$CheckerComment."',SpamInfo='" . $SpamInfo . "' where id=" . $Mes->id . " and Status!='Sent'";
		sql_query($str);
		return($Status);


	}
} // end of ComputeSpamCheck
?>
