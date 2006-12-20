<?php


//------------------------------------------------------------------------------
// This library file contains message relative files 
//------------------------------------------------------------------------------



//------------------------------------------------------------------------------
// ComputeSpamCheck read a message in the database and according to specific rules
// set the SpamInfo  
Function ComputeSpamCheck($IdMes) {
  $rr=LoadRow("select messages.* from messages where id=".$IdMes) ;
	if (isset($rr->id)) {
    $Status='ToSend' ;
		$SpamInfo="" ;
	  // to do : implement spamchecking verification to update SpamInfo
	  // to do : implement spamchecking verification

		$rPrefCheckMyMail=LoadRow("select *  from memberspreferences where IdMember=".$rr->IdReceiver." and IdPreference=4") ; // PreferenceCheckMyMail --> IdPref=4
		if ($rPrefCheckMyMail->Value='Yes') { // if member has choosen CheckMyMail
		  $Status='ToCheck' ;
		}
		if ($SpamInfo=="") $SpamInfo="NoSpam" ; 
	  $str="update messages set Status=$Status,SpamInfo='".$SpamInfo."' where id=".$IdMes." and Status!='Sent'" ; ;
		sql_query($str) ;
	}
} // end of ComputeSpamCheck


?>
