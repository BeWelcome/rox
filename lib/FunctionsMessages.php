<?php


//------------------------------------------------------------------------------
// This library file contains message relative files 
//------------------------------------------------------------------------------

//------------------------------------------------------------------------------
// ComputeSpamCheck read a message in the database and according to specific rules
// set the SpamInfo  

// Rule is :
// if sender has Flag NeverCheckSendMail : mail is always set to ToSend
// if sender has Flag AlwayCheckSendMail : mail is always set to toCheck
// if receiver has preference PreferenceCheckMyMail set to "Yes"  : mail is always set to toCheck
// testing then badwords todo

Function ComputeSpamCheck($IdMess) {
	$Mes=LoadRow("select * from messages where id=".$IdMes) ;
	if (isset ($Mes->id)) {
	
		$SpamInfo = "";

		$CheckerComment=$Mes->$CheckerComment ;
	    if (HasFlag("NeverCheckSendMail","",$Mes->IdSender)) {
		      $Status = 'ToSend';
			  $SpamInfo = "NoSpam";
		      $CheckerComment.="Sent by member with NeverCheckSendMail \n" ;
			  $str = "update messages set Status='".$Status."',CheckerComment='".$CheckerComment."',SpamInfo='" . $SpamInfo . "' where id=" . $Mes->id . " and Status!='Sent'";
			  sql_query($str) ;
			  LogStr("NeverCheckSendMail for message #".$IdMess." from <b>".fUsername($Mes->IdSender)."</b> to <b>".fUsername($Mes->IdReceiver)."</b>","AutoSpamCheck") ;
			  return($Status) ;
		}
	    if (HasFlag("AlwayCheckSendMail ","",$Mes->IdSender)) {
		      $Status = 'ToCheck';
		      $CheckerComment.="Sent by member with AlwayCheckSendMail \n" ;
			  $str = "update messages set Status='".$Status."',CheckerComment='".$CheckerComment."',SpamInfo='" . $SpamInfo . "' where id=" . $Mes->id . " and Status!='Sent'";
			  sql_query($str) ;
			  LogStr("AlwayCheckSendMail for message #".$IdMess." from <b>".fUsername($Mes->IdSender)."</b> to <b>".fUsername($Mes->IdReceiver)."</b>","AutoSpamCheck") ;
			  return($Status) ;
		}
		$Status = 'ToSend';
		$rPrefCheckMyMail = LoadRow("select *  from memberspreferences where IdMember=" . $Mes->IdReceiver . " and IdPreference=4"); // PreferenceCheckMyMail --> IdPref=4
		if ($rPrefCheckMyMail->Value = 'Yes') { // if member has choosen CheckMyMail
		    if ($SpamInfo == "")
			   $SpamInfo = "NoSpam";
			$Status = 'ToCheck';
			$CheckerComment.="Member has ask for checking\n" ;
			$str = "update messages set Status='".$Status."',CheckerComment='".$CheckerComment."',SpamInfo='" . $SpamInfo . "' where id=" . $Mes->id . " and Status!='Sent'";
			sql_query($str);
			LogStr("PreferenceCheckMyMail for message #".$IdMess." from <b>".fUsername($Mes->IdSender)."</b> to <b>".fUsername($Mes->IdReceiver)."</b>","AutoSpamCheck") ;
			return($Status) ;
		}
		if ($SpamInfo == "")
			$SpamInfo = "NoSpam";
		$str = "update messages set Status='".$Status."',CheckerComment='".$CheckerComment."',SpamInfo='" . $SpamInfo . "' where id=" . $Mes->id . " and Status!='Sent'";
		sql_query($str);
	}
} // end of ComputeSpamCheck
?>
