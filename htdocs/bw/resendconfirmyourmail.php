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
require_once "lib/FunctionsLogin.php";
require_once "layout/error.php";
require_once "layout/DisplayResendConfirmYourMail.php";


// Find parameters

if (HasRight("Accepter")) { // accepter can force a specific username to receive its confirmmail again
   $Username=GetStrParam("Username") ; // accepter can force a specific username to receive its confirmmail again
	$ReadCrypted = "AdminReadCrypted"; // In this case the AdminReadCrypted will be used
	$IsVolunteerAtWork = true;
} else {
	$IsVolunteerAtWork = false;
	$ReadCrypted = "AdminReadCrypted"; // In this case the MemberReadCrypted will be used (only owner can decrypt)
}


if (empty($Username)) { // If there is no username it mean that the member himself is trying to have a reconfirmation mail
	$Username=fUsername($_SESSION["IdMember"]) ;
}


$Error = "";

$Username = strtolower($Username);
$rr = LoadRow("select * from members where Username='" . $Username . "' and Status='MailToConfirm'");
		
if (empty($rr->id)) {
   die("No Such username <b>".$Username."</b> with mailtoconfirm") ;
}

$Email = GetEmail($rr->id);
$MemberIdLanguage = GetDefaultLanguage($rr->id);


$FirstName = $ReadCrypted ($rr->FirstName);
$SecondName = $ReadCrypted ($rr->SecondName);
$LastName = $ReadCrypted ($rr->LastName);
$key = CreateKey($Username, $LastName, $rr->id, "registration"); // compute a nearly unique key for cross checking

$subj = ww("SignupSubjRegistration", $_SYSHCVOL['SiteName']);
$urltoconfirm = "http://" . $_SYSHCVOL['MainDir'] . "main.php?action=confirmsignup&username=$Username&key=$key&id=" . abs(crc32(time())); // compute the link for confirming registration
$text = ww("SignupTextRegistrationAgain", $FirstName, $SecondName, $LastName, $_SYSHCVOL['SiteName'],$rr->created, $urltoconfirm, $urltoconfirm, $urltoconfirm."&StopBoringMe=1");
$defLanguage = $MemberIdLanguage;
bw_mail($Email, $subj, $text, "", $_SYSHCVOL['SignupSenderMail'],$defLanguage, "html", "", "");
LogStr("Requesting again for confimation mail for <b>".$Username."</b> ","resendconfirmyourmail");

DisplayResendConfirmYourMail($rr->id,$Email);
exit (0);

?>