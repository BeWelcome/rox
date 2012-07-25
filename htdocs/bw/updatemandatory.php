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
require_once "layout/updatemandatory.php";
?>
<?php

if (!IsLoggedIn("Pending,NeedMore")) {
	MustLogIn();
}

// Find parameters
$IdMember = $_SESSION['IdMember'];

if ((HasRight("Accepter")) or ((HasRight("SafetyTeam"))) and (GetStrParam("cid") != "")) { // Accepter or SafetyTeam can alter these data
	$IdMember = IdMember(GetStrParam("cid", $_SESSION['IdMember']));
	$ReadCrypted = "AdminReadCrypted"; // In this case the AdminReadCrypted will be used
	// Restriction an accepter can only see/update mandatory data of someone in his Scope country
	$AccepterScope = RightScope('Accepter');
	$AccepterScope = str_replace("'", "\"", $AccepterScope); // To be sure than nobody used ' instead of " (todo : this test will be to remoev some day)
	if (($AccepterScope != "\"All\"")and($IdMember!=$_SESSION['IdMember'])) {
	   $rr=LoadRow("select IdCountry,countries.Name as CountryName,Username from members,cities,countries where cities.id=members.IdCity and cities.IdCountry=countries.id and members.id=".$IdMember) ;
	   if (isset($rr->IdCountry)) {
	   	  $tt=explode(",",$AccepterScope) ;
		  	if ((!in_array($rr->IdCountry,$tt)) and (!in_array("\"".$rr->CountryName."\"",$tt))) {
					 $ss=$AccepterScope ;
					 for ($ii=0;$ii<sizeof($tt);$ii++) {
					 		 if (is_numeric($tt[$ii])) {
							 		$ss=$ss.",".getcountryname($tt[$ii]) ;
							 }
					 }				 
		  	 	 die ("sorry Your accepter Scope is only for ".$ss." This member is in ".$rr->CountryName) ;
		  	} 
	   }
	}
	$StrLog="Viewing member [<b>".fUsername($IdMember)."</b>] data with right [".$AccepterScope."]" ;
	if (HasRight("SafetyTeam")) {
		 		$StrLog=$StrLog." <b>With SafetyTeam Right</b>" ;
	}
	LogStr($StrLog,"updatemandatory") ; 
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
		/*
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
        */
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
		   	  $IdCity=$m->IdCity ; // or try with the previous one
		   }
		}
        /*
		if (empty($IdCity)) { 
			$MessageError .= ww('SignupErrorProvideCity') . "<br />";
		}
		*/


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
		if (($IsVolunteerAtWork)or($m->Status=='NeedMore')or($m->Status=='Pending')) {
			// todo store previous values
			if ($IdAddress!=0) { // if the member already has an address
				$str = "update addresses set IdCity=" . $IdCity . ",HouseNumber=" . NewReplaceInCrypted($HouseNumber,"addresses.HouseNumber",$IdAddress,$rr->HouseNumber, $m->id) . ",StreetName=" . NewReplaceInCrypted($StreetName,"addresses.StreetName",$IdAddress, $rr->StreetName, $m->id) . ",Zip=" . NewReplaceInCrypted($Zip,"addresses.Zip",$IdAddress, $rr->Zip, $m->id) . " where id=" . $IdAddress;
				sql_query($str);
			} else {
				$str = "insert into addresses(IdMember,IdCity,HouseNumber,StreetName,Zip,created,Explanation) Values(" . $_SESSION['IdMember'] . "," . $IdCity . "," . NewInsertInCrypted("addresses.HouseNumber",0,$HouseNumber) . "," . NewInsertInCrypted("addresses.StreetNamer",0,$StreetName) . "," . NewInsertInCrypted("addresses.Zip",0,$Zip) . ",now(),\"Address created by volunteer\")";
				sql_query($str);
			    $IdAddress=mysql_insert_id();
				LogStr("Doing a mandatoryupdate on <b>" . $Username . "</b> creating address", "updatemandatory");
			}
			$m->FirstName = NewReplaceInCrypted($FirstName,"members.FirstName",$m->id, $m->FirstName, $m->id,IsCryptedValue($m->FirstName));
			$m->SecondName = NewReplaceInCrypted($SecondName,"members.SecondName",$m->id, $m->SecondName, $m->id,IsCryptedValue($m->SecondName));
			$m->LastName = NewReplaceInCrypted(stripslashes($LastName),"members.LastName",$m->id, $m->LastName, $m->id,IsCryptedValue($m->LastName));

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
				   $slog .= "<br /><i>" . stripslashes(GetStrParam("Comment")) . "</i>";
				}
				LogStr($slog, "updatemandatory");
				DisplayUpdateMandatoryDone(ww('UpdateAfterNeedmoreConfirmed', $m->Username));
				exit (0);
			}
			

			if (GetStrParam("Comment") != "") {
				$slog .= "<br /><i>" . stripslashes(GetStrParam("Comment")) . "</i>";
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
			$text .= "<a href=\"https:/".$_SYSHCVOL['MainDir']."admin/adminmandatory.php\">go to update</a>\n";
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
