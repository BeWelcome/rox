<?php
require_once "lib/init.php";
require_once "lib/FunctionsLogin.php";
require_once "layout/error.php";
require_once "layout/updatemandatory.php";
?>
<?php

MustLogIn();

// Find parameters
$IdMember = $_SESSION['IdMember'];

if ((HasRight("Accepter")) and (GetParam("cid") != "")) { // Accepter can alter these data
	$IdMember = IdMember(GetParam("cid", $_SESSION['IdMember']));
	$ReadCrypted = "AdminReadCrypted"; // In this case the AdminReadCrypted will be used
	$IsVolunteerAtWork = true;
} else {
	$IsVolunteerAtWork = false;
	$ReadCrypted = "AdminReadCrypted"; // In this case the MemberReadCrypted will be used (only owner can decrypt)
}
$m = LoadRow("select * from members where id=" . $IdMember);

if (isset ($_POST['FirstName'])) { // If return from form
	$Username = $m->Username;
	$SecondName = GetStrParam("SecondName");
	$FirstName = GetStrParam("FirstName");
	$LastName = GetStrParam("LastName");
	$StreetName = GetStrParam("StreetName");
	$Zip = GetStrParam("Zip");
	$HouseNumber = GetStrParam("HouseNumber");
	$IdCountry = GetParam("IdCountry");
	$IdCity = GetParam("IdCity",0);
	$IdRegion = GetParam("IdRegion");
	$Gender = GetStrParam("Gender");
	$BirthDate = GetStrParam("BirthDate");
	$MemberStatus = GetStrParam("Status");
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
} // end if return from form
else {
	$Username = $m->Username;
	$MemberStatus = $m->Status;
	$FirstName = $ReadCrypted ($m->FirstName);
	$SecondName = $ReadCrypted ($m->SecondName);
	$LastName = $ReadCrypted ($m->LastName);

	$StreetName = "";
	$Zip = "";
	$HouseNumber = "";
	$IdCountry = 0;
	$IdCity = 0;
	$IdRegion = 0;
	$rAdresse = LoadRow("select StreetName,Zip,HouseNumber,countries.id as IdCountry,cities.IdRegion as IdRegion,cities.Name as CityName,cities.id as IdCity from addresses,countries,cities where IdMember=" . $IdMember . " and addresses.IdCity=cities.id  and countries.id=cities.IdCountry");
	if (isset ($rAdresse->IdCity)) {
		$IdCountry = $rAdresse->IdCountry;
		$IdCity = $rAdresse->IdCity;
		$IdRegion = $rAdresse->IdRegion;

		$CityName=$rAdresse->CityName;

		$StreetName = $ReadCrypted ($rAdresse->StreetName);
		$Zip = $ReadCrypted ($rAdresse->Zip);
		$HouseNumber = $ReadCrypted ($rAdresse->HouseNumber);
	}

	$Gender = $m->Gender;
	$HideGender = $m->HideGender;

	$ttdate = explode("-", $m->BirthDate);
	$BirthDate = $ttdate[2] . "-" . $ttdate[1] . "-" . $ttdate[0]; // resort BirthDate

	$HideBirthDate = $m->HideBirthDate;
}

$MessageError = "";
switch (GetParam("action")) {
	case "needmore" : // check parameters
	case "updatemandatory" : // check parameters

		$Username = $m->Username; // retrieve Username
		if ($IdCountry <= 0) {
			$IdCity = 0;
			$IdRegion = 0;
			$MessageError .= ww('SignupErrorProvideCountry') . "<br />";
		}
		if ($IdCity <= 0) {
			$MessageError .= ww('SignupErrorProvideCity') . "<br />";
		}
		if (strlen($StreetName) <= 1) {
			$MessageError .= ww('SignupErrorProvideStreetName') . "<br />";
		}
		if (strlen($Zip) < 1) {
			$MessageError .= ww('SignupErrorProvideZip') . "<br />";
		}
		if (strlen($HouseNumber) < 1) {
			$MessageError .= ww('SignupErrorProvideHouseNumber') . "<br />";
		}
		if (strlen($Gender) < 1) {
			$MessageError .= ww('SignupErrorProvideGender', ww('IdontSay')) . "<br />";
		}

		$ttdate = explode("-", $BirthDate);
		$DB_BirthDate = $ttdate[2] . "-" . $ttdate[1] . "-" . $ttdate[0]; // resort BirthDate
		if (!checkdate($ttdate[1], $ttdate[0], $ttdate[2])) {
			$MessageError .= ww('SignupErrorBirthDate') . "<br />";
		}
		elseif (fage_value($DB_BirthDate) < $_SYSHCVOL['AgeMinForApplying']) {
			//			  echo "fage_value(",$DB_BirthDate,")=",fage_value($DB_BirthDate),"<br />";
			$MessageError .= ww('SignupErrorBirthDateToLow', $_SYSHCVOL['AgeMinForApplying']) . "<br />";
		}

		if (empty($IdCity)) { // if there was no city return by the form because of some bug
		   if (!empty($rr->IdCity)) $IdCity=$rr->IdCity ; // try with the one of the address if any
		   else {
		   	  $IdCity=$m->IdCity ; // or try with the prévious one
		   }
		}
		if (empty($IdCity)) { 
			$MessageError .= ww('SignupErrorProvideCity') . "<br />";
		}


		if ($MessageError != "") {
			DisplayUpdateMandatory($Username, $FirstName, $SecondName, $LastName, $IdCountry, $IdRegion, $IdCity, $HouseNumber, $StreetName, $Zip, $Gender, $MessageError, $BirthDate, $HideBirthDate, $HideGender, $MemberStatus,stripslashes(GetStrParam("CityName","")));
			exit (0);
		}

     	$IdAddress=0;
		// in case the update is made by a volunteer
		$rr = LoadRow("select * from addresses where IdMember=" . $m->id." and Rank=0");
		if (isset ($rr->id)) { // if the member already has an address
			$IdAddress=$rr->id;
		}
		if (($IsVolunteerAtWork)or($m->Status=='NeedMore')) {
			// todo store previous values
			if ($IdAddress!=0) { // if the member already has an address
				$str = "update addresses set IdCity=" . $IdCity . ",HouseNumber=" . ReplaceInCrypted($HouseNumber, $rr->HouseNumber, $m->id) . ",StreetName=" . ReplaceInCrypted($StreetName, $rr->StreetName, $m->id) . ",Zip=" . ReplaceInCrypted($Zip, $rr->Zip, $m->id) . " where id=" . $IdAddress;
				sql_query($str);
			} else {
				$str = "insert into addresses(IdMember,IdCity,HouseNumber,StreetName,Zip,created,Explanation) Values(" . $_SESSION['IdMember'] . "," . $IdCity . "," . InsertInCrypted($HouseNumber) . "," . InsertInCrypted($StreetName) . "," . InsertInCrypted($Zip) . ",now(),\"Address created by volunteer\")";
				sql_query($str);
			    $IdAddress=mysql_insert_id();
				LogStr("Doing a mandatoryupdate on <b>" . $Username . "</b> creating address", "updatemandatory");
			}
			$m->FirstName = ReplaceInCrypted($FirstName, $m->FirstName, $m->id,IsCryptedValue($m->FirstName));
			$m->SecondName = ReplaceInCrypted($SecondName, $m->SecondName, $m->id,IsCryptedValue($m->SecondName));
			$m->LastName = ReplaceInCrypted(stripslashes($LastName), $m->LastName, $m->id,IsCryptedValue($m->LastName));

			$str = "update members set FirstName=" . $m->FirstName . ",SecondName=" . $m->SecondName . ",LastName=" . $m->LastName . ",Gender='" . $Gender . "',HideGender='" . $HideGender . "',BirthDate='" . $DB_BirthDate . "',HideBirthDate='" . $HideBirthDate . "',IdCity=" . $IdCity . " where id=" . $m->id;
			sql_query($str);
			$slog = "Doing a mandatoryupdate on <b>" . $Username . "</b>";
			if (($IsVolunteerAtWork) and ($MemberStatus != $m->Status)) {
				$str = "update members set Status='" . $MemberStatus . "' where id=" . $m->id;
				sql_query($str);
				LogStr("Changing Status from " . $m->Status . " to " . $MemberStatus . " for member <b>" . $Username . "</b>", "updatemandatory");
			}
			elseif ($m->Status=='NeedMore') {
				$str = "update members set Status='Pending' where id=" . $m->id;
				sql_query($str);
				$slog=" Completing profile after NeedMore ";
				if (GetStrParam("Comment") != "") {
				   $slog .= "<br /><i>" . GetStrParam("Comment") . "</i>";
				}
				LogStr($slog, "updatemandatory");
				DisplayUpdateMandatoryDone(ww('UpdateAfterNeedmoreConfirmed', $m->Username));
				exit (0);
			}
			

			if (GetStrParam("Comment") != "") {
				$slog .= "<br /><i>" . GetStrParam("Comment") . "</i>";
			}
			LogStr($slog, "updatemandatory");
		} else { // not volunteer action

			$Email = GetEmail();

// a member can only choose to hide or to show his gender / birth date and have it to take action immediately
	  		if (($HideGender!=$m->HideGender) or ($HideBirthDate!=$m->HideBirthDate)) { 
			   $str = "update members set HideGender='" . $HideGender . "',HideBirthDate='" . $HideBirthDate . "' where id=" . $m->id;
			   LogStr("mandatoryupdate changing Hide Gender (".$HideGender."/".$m->HideGender.") or HideBirthDate (".$HideBirthDate."/".$m->HideBirthDate.")", "updatemandatory");
			   sql_query($str);
			}
			
			$str = "insert into pendingmandatory(IdCity,FirstName,SecondName,LastName,HouseNumber,StreetName,Zip,Comment,IdAddress,IdMember) ";
			$str .= " values(" . GetParam("IdCity") . ",'" . GetStrParam("FirstName") . "','" . GetStrParam("SecondName") . "','" . GetStrParam("LastName") . "','" . GetStrParam("HouseNumber") . "','" . GetStrParam("StreetName") . "','" . GetStrParam("Zip") . "','" . GetStrParam("Comment") . "',".$IdAddress.",".$IdMember.")";
			sql_query($str);
			LogStr("Adding a mandatoryupdate request", "updatemandatory");

			$subj = ww("UpdateMantatorySubj", $_SYSHCVOL['SiteName']);
			$text = ww("UpdateMantatoryMailConfirm", $FirstName, $SecondName, $LastName, $_SYSHCVOL['SiteName']);
			$defLanguage = $_SESSION['IdLanguage'];
			bw_mail($Email, $subj, $text, "", $_SYSHCVOL['UpdateMandatorySenderMail'], $defLanguage, "yes", "", "");

			// Notify volunteers that an updater has updated
			$subj = "Update mandatory " . $Username . " from " . getcountryname($IdCountry) . " has updated";
			$text = " updater is " . $FirstName . " " . strtoupper($LastName) . "\n";
			$text .= "using language " . LanguageName($_SESSION['IdLanguage']) . "\n";
			if (GetStrParam("Comment")!="") $text .= "Feedback :<font color=green><b>" . GetStrParam("Comment") . "</font></b>\n";
			else $text .= "No Feedback \n";
			$text .= GetStrParam("ProfileSummary");
			$text .= "<a href=\"http://".$_SYSHCVOL['SiteName'].$_SYSHCVOL['MainDir']."admin/adminmandatory.php\">go to update</a>\n";
			bw_mail($_SYSHCVOL['MailToNotifyWhenNewMemberSignup'], $subj, $text, "", $_SYSHCVOL['UpdateMandatorySenderMail'], 0, "html", "", "");
			DisplayUpdateMandatoryDone(ww('UpdateMantatoryConfirm', $Email));
			exit (0);
		}

	case "change_country" :
	case ww('SubmitChooseRegion') :
		DisplayUpdateMandatory($Username, $FirstName, $SecondName, $LastName, $IdCountry, $IdRegion, $IdCity, $HouseNumber, $StreetName, $Zip, $Gender, $MessageError, $BirthDate, $HideBirthDate, $HideGender, $MemberStatus,stripslashes(GetStrParam("CityName","")));
		exit (0);
	case "change_region" :
	case ww('SubmitChooseCity') :
		DisplayUpdateMandatory($Username, $FirstName, $SecondName, $LastName, $IdCountry, $IdRegion, $IdCity, $HouseNumber, $StreetName, $Zip, $Gender, $MessageError, $BirthDate, $HideBirthDate, $HideGender, $MemberStatus,stripslashes(GetStrParam("CityName","")));
		exit (0);
}
DisplayUpdateMandatory($Username, $FirstName, $SecondName, $LastName, $IdCountry, $IdRegion, $IdCity, $HouseNumber, $StreetName, $Zip, $Gender, $MessageError, $BirthDate, $HideBirthDate, $HideGender, $MemberStatus,$CityName);
?>
