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
require_once "layout/error.php";
require_once "lib/FunctionsLogin.php";
require_once "lib/prepare_profile_header.php";

// Return the crypting criteria according of IsHidden_* field of a checkbox
function ShallICrypt($ss) {
	//  echo "GetParam(IsHidden_$ss)=",GetParam("IsHidden_".$ss),"<br>";
	if (GetStrParam("IsHidden_" . $ss) == "on")
		return ("crypted");
	else
		return ("not crypted");
} // end of ShallICrypt



if (!isset ($_SESSION['IdMember'])) {
	$errcode = "ErrorMustBeIndentified";
	DisplayError(ww($errcode));
	exit (0);
}
// Find parameters
$IdMember = $_SESSION['IdMember'];
$m = LoadRow("select * from members where id=" . $IdMember);


// test if is logged, if not logged and forward to the current page
// exeption for the people at confirm signup state
if ((!IsLoggedIn()) and (GetParam("action") != "confirmsignup") and (GetParam("action") != "update")) {
   if (($m->Status=='Pending') or ($m->Status=='NeedMore')  or ($m->Status=='MailToConfirm')) {
		LogStr("Entering Profil update while at Status=<b>".$m->Status."</b>", "Profil update");
	}
	else {  
		 APP_User::get()->logout();
		 header("Location: " . $_SERVER['PHP_SELF']);
		 exit (0);
	}
}


$CanTranslate=CanTranslate(GetStrParam("cid", $_SESSION['IdMember']));
$ReadCrypted = "AdminReadCrypted"; // Usually member read crypted is used
if ((IsAdmin())or($CanTranslate)) { // admin or CanTranslate can alter other profiles 
	$IdMember = IdMember(GetStrParam("cid", $_SESSION['IdMember']));
	$ReadCrypted = "AdminReadCrypted"; // In this case the AdminReadCrypted will be used
}

// Try to load groups and caracteristics where the member belongs to
$str = "SELECT membersgroups.IacceptMassMailFromThisGroup AS IacceptMassMailFromThisGroup,membersgroups.id AS id,membersgroups.Comment AS Comment,groups.Name AS Name FROM groups,membersgroups WHERE membersgroups.IdGroup=groups.id AND membersgroups.Status='In' AND membersgroups.IdMember=" . $IdMember;
$qry = sql_query($str);
$TGroups = array ();
while ($rr = mysql_fetch_object($qry)) {
	array_push($TGroups, $rr);
}

$profilewarning = ""; // No warning to display

switch (GetParam("action")) {
	case ww("TestThisEmail") :
		// Send a test mail
		$date=date("Y-m-d H:i:s");
		$subj = ww("TestThisEmailSubject", $_SYSHCVOL['SiteName']);
		$text = ww("TestThisEmailText", GetStrParam("Email")). "sent at ".$date;
		bw_mail(GetStrParam("Email"), $subj, $text, "", $_SYSHCVOL['TestMail'], 0, "html", "", "");
		$profilewarning = "Mail sent to " . GetStrParam("Email"). "<br>sent at ".$date;
		break;

	case "delrelation" : // todo the delrelation thing
		$rr=LoadRow("select * from specialrelations where IdOwner=".$IdMember." and IdRelation=".IdMember(GetStrParam("Username")));
		if (isset($rr->id)) {
		   $str="delete from specialrelations where id=".$rr->id;
		   sql_query($str);
		   LogStr("Removing relation (".FindTrad($rr->Comment).") with ".$username,"del relation");
		}
		break;
		
	case "update" :

		$m = LoadRow("select * from members where id=" . $IdMember);

		//variable names should be in English, change into $rAddress
		$rAdresse = LoadRow("select addresses.id as IdAddress,StreetName,Zip,HouseNumber,countries.id as IdCountry,cities.id as IdCity,regions.id as IdRegion from addresses,countries,regions,cities where IdMember=" . $IdMember . " and addresses.IdCity=cities.id and regions.id=cities.IdRegion and countries.id=cities.IdCountry");
		
		MakeRevision($m->id, "members"); // create revision
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

		// Analyse TypicOffer list
		$TypicOffer = sql_get_set("members", "TypicOffer");
		$max = count($TypicOffer);
		$sTypicOffer = "";
		for ($ii = 0; $ii < $max; $ii++) {
			if (GetStrParam("check_" . $TypicOffer[$ii]) == "on") {
				if ($sTypicOffer != "")
					$sTypicOffer .= ",";
				$sTypicOffer .= $TypicOffer[$ii];
			}
		} // end of for $ii
		
		
		// Analyse Restrictions list
		$TabRestrictions = sql_get_set("members", "Restrictions");
		$max = count($TabRestrictions);
		$Restrictions = "";
		for ($ii = 0; $ii < $max; $ii++) {
			if (GetStrParam("check_" . $TabRestrictions[$ii]) == "on") {
				if ($Restrictions != "")
					$Restrictions .= ",";
				$Restrictions .= $TabRestrictions[$ii];
			}
		} // end of for $ii

		if (!is_numeric(GetParam(MaxGuest))) {
			if (is_numeric($m->MaxGuest)){
				$MaxGuest = $m->MaxGuest;
			} else {
				$MaxGuest = 0;
			}
			if (!GetParam(MaxGuest)==""){
				$profilewarning = ww("MaxGuestNumericOnly");
			}
		} else {
			if (GetParam(MaxGuest)>=0){
				$MaxGuest = GetParam(MaxGuest);
			} else {
				if (is_numeric($m->MaxGuest)){
					$MaxGuest = $m->MaxGuest;
				} else {
					$MaxGuest = 0;
				}
				if (!GetParam(MaxGuest)==""){
					$profilewarning = ww("MaxGuestNumericOnly");
				}
			}
		}

		$str = "update members set HideBirthDate='" . $HideBirthDate . "'";
		$str .= ",HideGender='" . $HideGender . "'";
		$str .= ",MotivationForHospitality=" . NewReplaceInMTrad(GetStrParam(MotivationForHospitality),"members.MotivationForHospitality", $IdMember, $m->MotivationForHospitality, $IdMember);
		$str .= ",ProfileSummary=" . NewReplaceInMTrad(GetStrParam(ProfileSummary),"members.ProfileSummary", $IdMember, $m->ProfileSummary, $IdMember);
		$str .= ",WebSite='" . GetStrParam("WebSite") . "'";
		$str .= ",Accomodation='" . GetStrParam(Accomodation) . "'";
		$str .= ",Organizations=" . NewReplaceInMTrad(GetStrParam(Organizations),"members.Organizations", $IdMember, $m->Organizations, $IdMember);
		$str .= ",Occupation=" . NewReplaceInMTrad(GetStrParam(Occupation),"members.Occupation", $IdMember, $m->Occupation, $IdMember);
		$str .= ",ILiveWith=" . NewReplaceInMTrad(GetStrParam(ILiveWith),"members.ILiveWith", $IdMember, $m->ILiveWith, $IdMember);
		$str .= ",MaxGuest=" . $MaxGuest;
		$str .= ",MaxLenghtOfStay=" . NewReplaceInMTrad(GetStrParam(MaxLenghtOfStay),"members.MaxLenghtOfStay", $IdMember, $m->MaxLenghtOfStay, $IdMember);
		$str .= ",AdditionalAccomodationInfo=" . NewReplaceInMTrad(GetStrParam(AdditionalAccomodationInfo),"members.AdditionalAccomodationInfo", $IdMember, $m->AdditionalAccomodationInfo, $IdMember);
		$str .= ",TypicOffer='" . $sTypicOffer . "'";
		$str .= ",Restrictions='" . $Restrictions . "'";
		$str .= ",OtherRestrictions=" . NewReplaceInMTrad(GetStrParam(OtherRestrictions),"members.OtherRestrictions", $IdMember, $m->OtherRestrictions, $IdMember);
		$str .= ",Hobbies=" . NewReplaceInMTrad(GetStrParam(Hobbies),"members.Hobbies", $IdMember, $m->Hobbies, $IdMember);
		$str .= ",Books=" . NewReplaceInMTrad(GetStrParam(Books),"members.Books", $IdMember, $m->Books, $IdMember);
		$str .= ",Music=" . NewReplaceInMTrad(GetStrParam(Music),"members.Music", $IdMember, $m->Music, $IdMember);
		$str .= ",Movies=" . NewReplaceInMTrad(GetStrParam(Movies),"members.Movies", $IdMember, $m->Movies, $IdMember);
		$str .= ",PastTrips=" . NewReplaceInMTrad(GetStrParam(PastTrips),"members.PastTrips", $IdMember, $m->PastTrips, $IdMember);
		$str .= ",PlannedTrips=" . NewReplaceInMTrad(GetStrParam(PlannedTrips),"members.PlannedTrips", $IdMember, $m->PlannedTrips, $IdMember);
		$str .= ",PleaseBring=" . NewReplaceInMTrad(GetStrParam(PleaseBring),"members.PleaseBring", $IdMember, $m->PleaseBring, $IdMember);
		$str .= ",OfferGuests=" . NewReplaceInMTrad(GetStrParam(OfferGuests),"members.OfferGuests", $IdMember, $m->OfferGuests, $IdMember);
		$str .= ",OfferHosts=" . NewReplaceInMTrad(GetStrParam(OfferHosts),"members.OfferHosts", $IdMember, $m->OfferHosts, $IdMember);
       $str .= ",PublicTransport=" . NewReplaceInMTrad(GetStrParam(PublicTransport),"members.PublicTransport", $IdMember, $m->PublicTransport, $IdMember);



    
		
		if (!$CanTranslate) { // a volunteer translator will not be allowed to update crypted data		
		    $str .= ",HomePhoneNumber=" . NewReplaceInCrypted(GetStrParam(HomePhoneNumber),"members.HomePhoneNumber",$IdMember, $m->HomePhoneNumber, $IdMember, ShallICrypt("HomePhoneNumber"));
			$str .= ",CellPhoneNumber=" . NewReplaceInCrypted(GetStrParam(CellPhoneNumber),"members.CellPhoneNumber",$IdMember, $m->CellPhoneNumber, $IdMember, ShallICrypt("CellPhoneNumber"));
			$str .= ",WorkPhoneNumber=" . NewReplaceInCrypted(GetStrParam(WorkPhoneNumber),"members.WorkPhoneNumber",$IdMember, $m->WorkPhoneNumber, $IdMember, ShallICrypt("WorkPhoneNumber"));
			$str .= ",chat_SKYPE=" . NewReplaceInCrypted(GetStrParam(chat_SKYPE),"members.chat_SKYPE",$IdMember, $m->chat_SKYPE, $IdMember, ShallICrypt("chat_SKYPE"));
			$str .= ",chat_MSN=" . NewReplaceInCrypted(GetStrParam(chat_MSN),"members.chat_MSN",$IdMember, $m->chat_MSN, $IdMember, ShallICrypt("chat_MSN"));
			$str .= ",chat_AOL=" . NewReplaceInCrypted(GetStrParam(chat_AOL),"members.chat_AOL",$IdMember, $m->chat_AOL, $IdMember, ShallICrypt("chat_AOL"));
			$str .= ",chat_YAHOO=" . NewReplaceInCrypted(GetStrParam(chat_YAHOO),"members.chat_YAHOO",$IdMember, $m->chat_YAHOO, $IdMember, ShallICrypt("chat_YAHOO"));
			$str .= ",chat_ICQ=" . NewReplaceInCrypted(GetStrParam(chat_ICQ),"members.chat_ICQ",$IdMember, $m->chat_ICQ, $IdMember, ShallICrypt("chat_ICQ"));
			$str .= ",chat_Others=" . NewReplaceInCrypted(GetStrParam(chat_Others),"members.chat_Others",$IdMember, $m->chat_Others, $IdMember, ShallICrypt("chat_Others"));
    		$str .= ",chat_GOOGLE=" . NewReplaceInCrypted(GetStrParam(chat_GOOGLE),"members.chat_GOOGLE",$IdMember,$m->chat_GOOGLE, $IdMember, ShallICrypt("chat_GOOGLE"));		
		}

		$str .= " where id=" . $IdMember;
		sql_query($str);

		if (!$CanTranslate) { // a volunteer translator will not be allowed to update crypted data		
		    // Only update hide/unhide for identity fields
		    NewReplaceInCrypted(addslashes($ReadCrypted($m->FirstName)),"members.FirstName",$IdMember, $m->FirstName, $IdMember, ShallICrypt("FirstName"));
			NewReplaceInCrypted(addslashes($ReadCrypted($m->SecondName)),"members.SecondName",$IdMember, $m->SecondName, $IdMember, ShallICrypt("SecondName"));
			NewReplaceInCrypted(addslashes($ReadCrypted($m->LastName)),"members.LastName",$IdMember, $m->LastName, $IdMember, ShallICrypt("LastName"));
			
			NewReplaceInCrypted(addslashes($ReadCrypted($rAdresse->Zip)),"addresses.Zip",$rAdresse->IdAddress,$rAdresse->Zip,$IdMember,ShallICrypt("Zip"));
			NewReplaceInCrypted(addslashes($ReadCrypted($rAdresse->HouseNumber)),"addresses.HouseNumber",$rAdresse->IdAddress,$rAdresse->HouseNumber,$IdMember,ShallICrypt("Address"));
			NewReplaceInCrypted(addslashes($ReadCrypted($rAdresse->StreetName)),"addresses.StreetName",$rAdresse->IdAddress,$rAdresse->StreetName,$IdMember,ShallICrypt("Address"));


			// if email has changed
			// if email has changed
			if (GetStrParam("Email") != $ReadCrypted($m->Email)) {
			   if (CheckEmail(GetStrParam("Email"))) {
			   	  $MailBefore=$ReadCrypted($m->Email) ;
			   	  NewReplaceInCrypted(GetStrParam("Email"),"members.Email",$IdMember, $m->Email, $IdMember, true);
			   	  LogStr("Email updated (previous was " . $MailBefore . ")", "Email Update");
			   }
			   else {
			   	  LogStr("Bad Email update with value " .GetStrParam("Email"), "Email Update");
			   }
			} // end if EMail has changed
		}


		// updates groups
		$max = count($TGroups);
		for ($ii = 0; $ii < $max; $ii++) {
			$ss = addslashes($_POST["Group_" . $TGroups[$ii]->Name]);
			//				 echo "replace $ss<br> for \$TGroups[",$ii,"]->Comment=",$TGroups[$ii]->Comment," \$IdMember=",$IdMember,"<br> "; continue;

			$IdTrad = NewReplaceInMTrad($ss,"membersgroups.Comment",$TGroups[$ii]->id, $TGroups[$ii]->Comment, $IdMember);
			if ((GetStrParam("AcceptMessage_".$TGroups[$ii]->Name)=="on") or (GetStrParam("AcceptMessage_".$TGroups[$ii]->Name)=="yes")) $AcceptMess="yes";
			else  $AcceptMess="no";

			//				echo "replace $ss<br> for \$IdTrad=",$IdTrad,"<br>�;;
			if (($IdTrad != $TGroups[$ii]->Comment) or ($TGroups[$ii]->IacceptMassMailFromThisGroup!=$AcceptMess)){ // if has changed
				MakeRevision($TGroups[$ii]->id, "membersgroups"); // create revision
				sql_query("update membersgroups set Comment=" . $IdTrad . ",IacceptMassMailFromThisGroup='".$AcceptMess."' where id=" . $TGroups[$ii]->id);
			}
		}

		
// 	Update relations 
		$Relations = array ();
		$str = "select SQL_CACHE specialrelations.*,members.Username as Username,members.Gender as Gender,members.HideGender as HideGender from specialrelations,members where IdOwner=".$IdMember." and specialrelations.Confirmed='Yes' and members.id=specialrelations.IdRelation and (members.Status='Active' or members.Status='ChoiceInactive')";
		$qry = mysql_query($str);
		while ($rr = mysql_fetch_object($qry)) {
			$rr->IdComment=$rr->Comment ;
			$rr->Comment=FindTrad($rr->Comment);
			array_push($Relations, $rr);
		}
		$max = count($Relations);
		for ($ii = 0; $ii < $max; $ii++) {
			$ss = addslashes($_POST["RelationComment_" . $Relations[$ii]->id]);

			$IdTrad = NewReplaceInMTrad($ss,"specialrelations.IdComment", $Relations[$ii]->id, $Relations[$ii]->IdComment, $IdMember);
			//				echo "replace $ss<br> for \$IdTrad=",$IdTrad,"<br>�;;
			if ($IdTrad != $Relations[$ii]->IdComment) { // if has changed
				MakeRevision($Relations[$ii]->id, "specialrelations"); // create revision
				sql_query("update specialrelations set Comment=" . $IdTrad . " where id=" . $Relations[$ii]->id);
			}
		}
		
		// Process languages
		// first  the language the member knows
		$str = "select memberslanguageslevel.IdLanguage as IdLanguage,memberslanguageslevel.id as id,languages.Name as Name,memberslanguageslevel.Level from memberslanguageslevel,languages where memberslanguageslevel.IdMember=" . $IdMember . " and memberslanguageslevel.IdLanguage=languages.id";
		$qry = mysql_query($str);
		while ($rr = mysql_fetch_object($qry)) {
			$str = "update memberslanguageslevel set Level='" . GetStrParam("memberslanguageslevel_level_id_" . $rr->id) . "' where id=" . $rr->id;
			sql_query($str);
		}
		if (GetStrParam("memberslanguageslevel_newIdLanguage") != "") {
			$str = "insert into memberslanguageslevel (IdLanguage,Level,IdMember) values(" . GetStrParam("memberslanguageslevel_newIdLanguage") . ",'" . GetStrParam("memberslanguageslevel_newLevel") . $rr->id . "'," . $IdMember . ")";
			sql_query($str);
		}

		if ($IdMember == $_SESSION['IdMember']) {
			LogStr("Profil update by member himself [Status=<b>".$m->Status."</b>]", "Profil update");
		}
		else {
			LogStr("update of another profil", "Profil update");
		}

// now go to member profile
		if ($profilewarning == ""){
		   if (!(($m->Status == "Pending")and($m->id==$_SESSION['IdMember']))) { // in case member is still pending don't forward to member profile
			  header("Location: "."member.php?cid=".$m->Username,true); 
			  exit(0);
		   }
    		 header("Location: /user/waitingapproval");
    		 exit (0);
		}
		break;
	case "logout" :
		Logout();
		exit (0);
}

$m = prepareProfileHeader($IdMember," and (Status='Active' or Status='Pending' or Status='MailToConfirm' or Status='NeedMore')"); // pending members can edit their profile

// Try to load specialrelations and caracteristics belong to
$Relations = array ();
$str = "select SQL_CACHE specialrelations.*,members.Username as Username,members.Gender as Gender,members.HideGender as HideGender from specialrelations,members where IdOwner=".$IdMember." and specialrelations.Confirmed='Yes' and members.id=specialrelations.IdRelation and (members.Status='Active' or members.Status='ChoiceInactive')";
$qry = mysql_query($str);
while ($rr = mysql_fetch_object($qry)) {
	$rr->Comment=FindTrad($rr->Comment);
   $photo=LoadRow("select SQL_CACHE * from membersphotos where IdMember=" . $rr->IdRelation . " and SortOrder=0");
	if (isset($photo->FilePath)) $rr->photo=$photo->FilePath; 
	array_push($Relations, $rr);
}
$m->Relations=$Relations;

// Load the language the member knows
$TLanguages = array ();
$str = "select memberslanguageslevel.IdLanguage as IdLanguage,memberslanguageslevel.id as id,languages.Name as Name,memberslanguageslevel.Level from memberslanguageslevel,languages where memberslanguageslevel.IdMember=" . $IdMember . " and memberslanguageslevel.IdLanguage=languages.id";
$qry = mysql_query($str);
while ($rr = mysql_fetch_object($qry)) {
	array_push($TLanguages, $rr);
}
$m->TLanguages = $TLanguages;

// Load the language the member does'nt know
$m->TOtherLanguages = array ();
$str = "select languages.Name as Name,languages.id as id from languages where id not in (select IdLanguage from memberslanguageslevel where memberslanguageslevel.IdMember=" . $IdMember . ")";
$qry = mysql_query($str);
while ($rr = mysql_fetch_object($qry)) {
	array_push($m->TOtherLanguages, $rr);
}

// Load the address (for display only) + hide unhide option
$rAdresse = LoadRow("select addresses.id as IdAddress,StreetName,Zip,HouseNumber,countries.id as IdCountry,cities.id as IdCity,regions.id as IdRegion from addresses,countries,regions,cities where IdMember=" . $IdMember . " and addresses.IdCity=cities.id and regions.id=cities.IdRegion and countries.id=cities.IdCountry");

$m->Address=" no address, problem";	
if (isset ($rAdresse->IdCity)) {
		$m->rAddress=$rAdresse; // We need to have this record for the hidden address toggle
		$IdCountry = $rAdresse->IdCountry;
		$IdCity = $rAdresse->IdCity;
		$IdRegion = $rAdresse->IdRegion;

		$m->Address=$ReadCrypted ($rAdresse->HouseNumber)." ".$ReadCrypted ($rAdresse->StreetName);
		$m->Zip=$ReadCrypted ($rAdresse->Zip);
}


if (($m->Status == "Pending")and($m->id==$_SESSION['IdMember'])) {
	$profilewarning = ww("YouCanCompleteProfAndWait", $m->Username);
}
elseif ($m->Status != "Active") {
	$profilewarning .= "WARNING the status of " . $m->Username . " is set to " . $m->Status;
}

$m->MyTypicOffer = explode(",", $m->TypicOffer);
$m->TabRestrictions = sql_get_set("members", "Restrictions");
$m->TabTypicOffer = sql_get_set("members", "TypicOffer");
include "layout/editmyprofile.php";
DisplayEditMyProfile($m, $profilewarning, $TGroups,$CanTranslate);
?>
