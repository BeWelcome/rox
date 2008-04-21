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
require_once "layout/signup.php";

function CheckUsername($name)
{
//	$allowedotherchars = " .-_()!?+{}[]~<>";
	$allowedotherchars = "_";
	
	if (strcmp(trim($name),$name)!=0) return false;
	if (strlen($name) < 4) return false;
	
	for ($c=0;$c<strlen($name);$c++)
		if (!ctype_alnum($name[$c])&&
			!strstr($allowedotherchars,$name[$c]))
			return false;
			
	return true;
}

if (IsLoggedIn()) { // Logout the member if one was previously logged on 
	Logout();
}

// Find parameters

if (isset ($_POST['Username'])) { // If return from form
	$Username = GetStrParam("Username");
	$SecondName = GetStrParam("SecondName");
	$FirstName = GetStrParam("FirstName");
	$LastName = GetStrParam("LastName");
	$CityName = GetStrParam("CityName");

	$HouseNumber = GetStrParam("HouseNumber","");
	$StreetName = GetStrParam("StreetName");
	$Zip = GetStrParam("Zip");

	$Email = GetStrParam("Email");
	$EmailCheck = GetStrParam("EmailCheck");

	$IdCountry = GetParam("IdCountry");
	$IdCity = GetParam("IdCity");
	$Gender = GetStrParam("Gender");
	$password = GetStrParam("password");
	$secpassword = GetStrParam("secpassword");
	$BirthDate = GetStrParam("BirthDate", "");
	$Feedback = GetStrParam("SignupFeedback");
	$ProfileSummary = GetStrParam("ProfileSummary");

	if (GetStrParam("HideBirthDate") == "on") {
		$HideBirthDate = "Yes";
	} else {
		$HideBirthDate = "No";
	}

	if (GetStrParam("HideGender") == "on") {
		$HideGender = "Yes";
	} else {
		$HideGender = "No";
	}

}

$IdMember = GetParam("cid", "");

$defLanguage = 0 ;
if (isset($_SESSION["IdLanguage"])) {
	 $defLanguage=$_SESSION["IdLanguage"] ;
}
$SignupError = "";
switch (GetParam("action")) {
	case "SignupFirstStep" : // Member has signup then check parameters

		$rr = LoadRow("select Username from members where Username='" . $Username . "'");
		$Username = strtolower($Username);

		if (!CheckUsername($Username))
			$SignupError .= ww("SignupErrorWrongUsername") . "<br />";

		if (!isset ($_POST['Terms']))
			$SignupError .= ww("SignupMustacceptTerms") . "<br />";

		if (isset ($rr->Username)) {
			$SignupError .= ww("SignupErrorUsernameAlreadyTaken", $Username) . "<br />";
			$Username = "";
		}

		if (!CheckEmail($Email))
			$SignupError .= ww('SignupErrorInvalidEmail') . "<br />";
		if ($Email != $EmailCheck)
			$SignupError .= ww('SignupErrorEmailCheck') . "<br />";
		if ((($password != $secpassword) or ($password == "")) or (strlen($password) < 8))
			$SignupError .= ww('SignupErrorPasswordCheck') . "<br />";
		if ((strlen($FirstName) <= 1) or (strlen($LastName) <= 1)) {
			$SignupError .= ww('SignupErrorFullNameRequired') . "<br />";
		}

// Check if there is a member with mailtoconfirm  status allready using this email
		$str="select id as IdMember,Email,Username from members where (Status='MailToConfirm') order by id asc"  ;
		$qry= sql_query($str);
		while ($rr=mysql_fetch_object($qry)) {
			  if (AdminReadCrypted ($rr->Email)=="$Email") {
			  	 $SignupError .= ww('SignupErrorNeedToConfirmMail',$Email) . "<br />";
				 break ;
			  }
		} 
		
// Check if there is a member with  pending  status allready using this email
		$str="select id as IdMember,Email,Username from members where (Status='Pending') order by id asc"  ;
		$qry= sql_query($str);
		while ($rr=mysql_fetch_object($qry)) {
			  if (AdminReadCrypted ($rr->Email)=="$Email") {
			  	 $SignupError .= ww('SignupErrorWaitPending',$rr->Username) . "<br />";
				 break ;
			  }
		} 

// Check if there is a member with  needmore status allready using this email
		$str="select id as IdMember,Email,Username from members where (Status='NeedMore') order by id asc"  ;
		$qry= sql_query($str);
		while ($rr=mysql_fetch_object($qry)) {
			  if (AdminReadCrypted ($rr->Email)=="$Email") {
			  	 $SignupError .= ww('SignupErrorNeedMoreState',$Email) . "<br />";
				 break ;
			  }
		} 

		if (!CheckEmail($Email))
			$SignupError .= ww('SignupErrorInvalidEmail') . "<br />";


		if ($IdCountry <= 0) {
			$IdCity = 0;
			$SignupError .= ww('SignupErrorProvideCountry') . "<br />";
		}
		if ($IdCity <= 0) {
			$str="select SQL_CALC_FOUND_ROWS Name,id as IdCity from cities where IdCountry=".$IdCountry." and Name='".GetStrParam("CityName","")."'" ;
			$qry= sql_query($str);
			$rCity=mysql_fetch_object($qry) ;
			$rcount=LoadRow("SELECT FOUND_ROWS() as cnt") ;
			
			if ($rcount->cnt==1) { // if in fact there is only one city wich match we can use it, no need to rely on Javascript
				 $IdCity=$rCity->IdCity ;
				 LogStr("JavaScript didnt give IdCity, but we find a unique one [".GetStrParam("CityName","")."] which match for this city, it is assumed to be the good one","Signup") ;
			}
			else {
					 $SignupError .= ww('SignupErrorProvideCity') . "<br />";
			}
		}
		if (strlen($StreetName) <= 1) {
			$SignupError .= ww('SignupErrorProvideStreetName') . "<br />";
		}
		if (strlen($Zip) < 1) {
			$SignupError .= ww('SignupErrorProvideZip') . "<br />";
		}
		if (strlen($HouseNumber) < 1) {
			$SignupError .= ww('SignupErrorProvideHouseNumber') . "<br />";
		}
		if (strlen($Gender) < 1) {
			$SignupError .= ww('SignupErrorProvideGender', ww('IdontSay')) . "<br />";
		}

		// todo check if BirthDate is valid
		$BirthDate=str_replace("/","-",$BirthDate) ; // allow for "/" instead of  "-"
		$ttdate = explode("-", $BirthDate);
		$DB_BirthDate = $ttdate[2] . "-" . $ttdate[1] . "-" . $ttdate[0]; // resort BirthDate
		if (($BirthDate == "") or ((int)$ttdate[2]==0) or (!checkdate($ttdate[1], $ttdate[0], $ttdate[2]))) {
			$SignupError .= ww('SignupErrorBirthDate') . "<br />";
		}
		elseif (fage_value($DB_BirthDate) < $_SYSHCVOL['AgeMinForApplying']) {
			//			  echo "fage_value(",$DB_BirthDate,")=",fage_value($DB_BirthDate),"<br />";
			$SignupError .= ww('SignupErrorBirthDateToLow', $_SYSHCVOL['AgeMinForApplying']) . "<br />";
		}

		//		  DisplaySignupEmailStep();

		if ($SignupError != "") {
		    DisplaySignupFirstStep($Username, stripslashes($FirstName), stripslashes($SecondName), stripslashes($LastName), $Email, $EmailCheck, $IdCountry, $IdCity, stripslashes($HouseNumber), stripslashes($StreetName), $Zip, stripslashes($ProfileSummary),  stripslashes($Feedback), $Gender, $password, $secpassword, $SignupError, $BirthDate, $HideBirthDate, $HideGender,stripslashes($CityName));
				LogStr("Signup error : member  <b>".$Username."</b> is trying to signup in city [".$CityName."] using language (".LanguageName($defLanguage).")<br>error[<font color=red>".$SignupError."</font>]<br>feedback[<b>".stripslashes($Feedback)."</b>]","Signup");

			exit (0);
		}

		// Create member
		$str = "insert into members(Username,IdCity,Gender,created,Password,BirthDate,HideBirthDate) Values(\"" . $Username . "\"," . $IdCity . ",'" . $Gender . "'," . "now(),password('" . $password . "'),'" . $DB_BirthDate . "','" . $HideBirthDate . "')";

		//		echo "str=$str<br />";
		sql_query($str);
		$_SESSION['IdMember'] = mysql_insert_id();

		// todo discuss with Marco the real value to insert there			
		// For Travelbook compatibility, also insert in user table
		$str = "INSERT INTO `user` ( `id` , `auth_id` , `handle` , `email` , `pw` , `active` , `lastlogin` , `location` )
		            VALUES (" . $_SESSION['IdMember'] . ", NULL , '".$Username."', '" . $Email . "', '', '1', NULL , " . $IdCity . ")";
		sql_query($str);

		// Now that we have a IdMember, insert the email			
		$str = "update members set Email=" . NewInsertInCrypted($Email,"members.Email", $_SESSION['IdMember'],$_SESSION['IdMember'], "always") . " where id=" . $_SESSION['IdMember'];
		sql_query($str);

		$key = CreateKey($Username, $LastName, $_SESSION['IdMember'], "registration"); // compute a nearly unique key for cross checking
		$str = "insert into addresses(IdMember,IdCity,HouseNumber,StreetName,Zip,created,Explanation) Values(" . $_SESSION['IdMember'] . "," . $IdCity . ",0,0,0,now(),\"Signup addresse\")";
		sql_query($str);
		$IdAddress= mysql_insert_id();
		$str = "update addresses set HouseNumber=".NewInsertInCrypted($HouseNumber,"addresses.HouseNumber",$IdAddress) . 
		",StreetName=" .NewInsertInCrypted($StreetName,"addresses.StreetName", $IdAddress).",Zip=".NewInsertInCrypted($Zip,"addresses.Zip", $IdAddress)." where id=".$IdAddress;
		sql_query($str);
		$str = "update members set FirstName=" . NewInsertInCrypted($FirstName,"members.FirstName", $_SESSION['IdMember']) . ",SecondName=" . NewInsertInCrypted($SecondName,"members.SecondName", $_SESSION['IdMember']) . ",LastName=" . NewInsertInCrypted($LastName,"members.LastName", $_SESSION['IdMember']) . ",ProfileSummary=" . NewInsertInMTrad($ProfileSummary,"members.ProfileSummary",$_SESSION['IdMember']) . " where id=" . $_SESSION['IdMember'];
		sql_query($str);

	  LogStr("member  <b>".$Username."</b> is signuping with success in city [".$CityName."]  using language (".LanguageName($defLanguage)."<br>feedback[<b>".stripslashes($Feedback)."</b>] IdMember=#".$_SESSION['IdMember'],"Signup");
		if ($Feedback == "") $Feedback=$Feedback."\n"; 
		// check if this email already exist
		$cryptedemail=LoadRow("select AdminCryptedValue from members,".$_SYSHCVOL['Crypted']."cryptedfields where members.id=".$_SYSHCVOL['Crypted']."cryptedfields.IdMember and members.Email=".$_SYSHCVOL['Crypted']."cryptedfields.id and members.id=".$_SESSION['IdMember']); 
		$str="select Username,members.Status,members.id as IdAllreadyMember from members,".$_SYSHCVOL['Crypted']."cryptedfields where AdminCryptedValue='".$cryptedemail->AdminCryptedValue."' and members.id=".$_SYSHCVOL['Crypted']."cryptedfields.IdMember and members.id!=".$_SESSION['IdMember'];
		$qry=sql_query($str);
		while ($rr=mysql_fetch_object($qry)) {
			  if ($rr->IdAllreadyMember== $_SESSION['IdMember']) continue;
			  $Feedback.="<font color=red>Same Email as ".LinkWithUserName($rr->Username,$rr->Status)."</font>\n";
			  LogStr("Signup with same email than <b>".$rr->Username."</b> ","Signup");
		} 
		// end of check if email already exist

		// Checking of previous cookie was already there
		if (isset ($_COOKIE['MyBWusername'])) {
			  $Feedback.="<font color=red>Registration computer was already used by  ".LinkWithUserName($_COOKIE['MyBWusername'])."</font>\n";
			  LogStr("Signup on a computer previously used by  <b>".$_COOKIE['MyBWusername']."</b> ","Signup");
		} 		
		// End of previous cookie was already there
		
		if ($Feedback != "") {
			// feedbackcategory 3 = FeedbackAtSignup
			$str = "insert into feedbacks(created,Discussion,IdFeedbackCategory,IdVolunteer,Status,IdLanguage,IdMember) values(now(),'" . $Feedback . "',3,0,'closed by member'," . $_SESSION['IdLanguage'] . "," . $_SESSION['IdMember'] . ")";
			sql_query($str);
		}
		
		// Retrieve the Newmember
		$NewMember=LoadRow("select * from members where Username='".$Username."'" ) ;

		$subj = ww("SignupSubjRegistration", $_SYSHCVOL['SiteName']);
		$urltoconfirm = $_SYSHCVOL['SiteName'] . $_SYSHCVOL['MainDir'] . "main.php?action=confirmsignup&username=$Username&key=$key&id=" . abs(crc32(time())); // compute the link for confirming registration
		$text = ww("SignupTextRegistration", $FirstName, $SecondName, $LastName, $_SYSHCVOL['SiteName'], $urltoconfirm, $urltoconfirm);
		bw_mail($Email, $subj, $text, "", $_SYSHCVOL['SignupSenderMail'], $defLanguage, "html", "", "");

		// Notify volunteers that a new signupers come in
		$subj = "New member " . $Username . " from " . getcountryname($IdCountry) . " has signup with ".$_SERVER['SERVER_NAME'];
		$text = " New signuper is " . $FirstName . " " . $LastName . "\n";
		$text .= "country=" .getcountryname($IdCountry)." city=".getcityname($IdCity)."\n";
		$text .= " Signuper email is "  . $Email . "\n";
		$text .= "using language " . LanguageName($_SESSION['IdLanguage']) . "\n";
		$text .=  fage($NewMember->BirthDate) . "\n";
		$text .= stripslashes(GetStrParam("ProfileSummary"));
		$text .= "<br /><a href=\"http://".$_SYSHCVOL['SiteName'].$_SYSHCVOL['MainDir']."admin/adminaccepter.php\">go to accepting</a>\n";
		bw_mail($_SYSHCVOL['MailToNotifyWhenNewMemberSignup'], $subj, $text, "", $_SYSHCVOL['SignupSenderMail'], 0, "html", "", "");

		DisplaySignupResult(ww("SignupResutlTextConfimation", $Username, $Email));
		exit (0);
	case "change_country" :
	case ww('SubmitChooseRegion') :
		DisplaySignupFirstStep($Username, stripslashes($FirstName), stripslashes($SecondName), stripslashes($LastName), $Email, $EmailCheck, $IdCountry, $IdCity, stripslashes($HouseNumber), stripslashes($StreetName), $Zip, stripslashes($ProfileSummary),  stripslashes($Feedback), $Gender, $password, $secpassword, $SignupError, $BirthDate, $HideBirthDate, $HideGender,stripslashes($CityName));
		exit (0);
	case "change_region" :
	case ww('SubmitChooseCity') :
		DisplaySignupFirstStep($Username, stripslashes($FirstName), stripslashes($SecondName), stripslashes($LastName), $Email, $EmailCheck, $IdCountry, $IdCity, stripslashes($HouseNumber), stripslashes($StreetName), $Zip, stripslashes($ProfileSummary),  stripslashes($Feedback), $Gender, $password, $secpassword, $SignupError, $BirthDate, $HideBirthDate, $HideGender,stripslashes($CityName));
		exit (0);
}

DisplaySignupFirstStep();
?>