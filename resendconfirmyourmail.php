<?php
require_once "lib/init.php";
require_once "lib/FunctionsLogin.php";
require_once "layout/error.php";
require_once "layout/DisplayResendConfirmYourMail.php";


// Find parameters

if (HasRight("Accepter")) $UserName=GetStrParam("UserName") ; // accepter can force a specific username to receive its confirmmail again

if (empty($UserName)) {
	$Username=fUsername($_SESSION["IdMember"]) ;
}


$Error = "";

$Username = strtolower($Username);
$rr = LoadRow("select * from members where Username='" . $Username . "' and Status='MailToConfirm'");
		
if (empty($rr->id)) {
   die("No Such username <b>".$Username."</b> with mailtoconfirm") ;
}

$Email = GetEmail($rr->Id);
$MemberIdLanguage = GetDefaultLanguage($rr->Id);



// Checking of previous cookie was already there
if (isset ($_COOKIE['MyBWusername'])) {
  LogStr("Signup on a computer previously used by  <b>".$_COOKIE['MyBWusername']."</b> ","resendconfirmyourmail");
} 		
// End of previous cookie was already there
		
$subj = ww("SignupSubjRegistration", $_SYSHCVOL['SiteName']);
$urltoconfirm = $_SYSHCVOL['SiteName'] . $_SYSHCVOL['MainDir'] . "main.php?action=confirmsignup&username=$Username&key=$key&id=" . abs(crc32(time())); // compute the link for confirming registration
$text = ww("SignupTextRegistrationAgain", $FirstName, $SecondName, $LastName, $_SYSHCVOL['SiteName'], $urltoconfirm, $urltoconfirm);
$defLanguage = $_SESSION['IdLanguage'];
bw_mail($Email, $subj, $text, "", $_SYSHCVOL['SignupSenderMail'], $defLanguage, "html", "", "");
LogStr("Requesting again for confimation mail for <b>".$Username."</b> ","resendconfirmyourmail");

DisplayResendConfirmYourMail($rr->id,$Email);
exit (0);

?>