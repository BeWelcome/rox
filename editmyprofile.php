<?php
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



// test if is logged, if not logged and forward to the current page
// exeption for the people at confirm signup state
if ((!IsLoggedIn()) and (GetParam("action") != "confirmsignup") and (GetParam("action") != "update")) {
	Logout($_SERVER['PHP_SELF']);
	exit (0);
}

if (!isset ($_SESSION['IdMember'])) {
	$errcode = "ErrorMustBeIndentified";
	DisplayError(ww($errcode));
	exit (0);
}
// Find parameters
$IdMember = $_SESSION['IdMember'];


$CanTranslate=CanTranslate(GetParam("cid", $_SESSION['IdMember']));
$ReadCrypted = "AdminReadCrypted"; // Usually member read crypted is used
if ((IsAdmin())or($CanTranslate)) { // admin or CanTranslate can alter other profiles 
	$IdMember = GetParam("cid", $_SESSION['IdMember']);
	$ReadCrypted = "AdminReadCrypted"; // In this case the AdminReadCrypted will be used
}

// Try to load groups and caracteristics where the member belong to
$str = "select membersgroups.IacceptMassMailFromThisGroup as IacceptMassMailFromThisGroup,membersgroups.id as id,membersgroups.Comment as Comment,groups.Name as Name from groups,membersgroups where membersgroups.IdGroup=groups.id and membersgroups.Status='In' and membersgroups.IdMember=" . $IdMember;
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
		   LogStr("Removing relation (",FindTrad($rr->Comment),") with ".$username,"del relation");
		}
		break;
		
	case "update" :

		$m = LoadRow("select * from members where id=" . $IdMember);
		
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
			$MaxGuest = 0;
			$profilewarning = ww("MaxGuestNumericOnly");
		} else {
			$MaxGuest = GetParam(MaxGuest);
		}

		$str = "update members set HideBirthDate='" . $HideBirthDate . "'";
		$str .= ",HideGender='" . $HideGender . "'";
		$str .= ",MotivationForHospitality=" . ReplaceInMTrad(GetStrParam(MotivationForHospitality), $m->MotivationForHospitality, $IdMember);
		$str .= ",ProfileSummary=" . ReplaceInMTrad(GetStrParam(ProfileSummary), $m->ProfileSummary, $IdMember);
		$str .= ",WebSite='" . GetStrParam("WebSite") . "'";
		$str .= ",Accomodation='" . GetStrParam(Accomodation) . "'";
		$str .= ",Organizations=" . ReplaceInMTrad(GetStrParam(Organizations), $m->Organizations, $IdMember);
		$str .= ",Occupation=" . ReplaceInMTrad(GetStrParam(Occupation), $m->Occupation, $IdMember);
		$str .= ",ILiveWith=" . ReplaceInMTrad(GetStrParam(ILiveWith), $m->ILiveWith, $IdMember);
		$str .= ",MaxGuest=" . $MaxGuest;
		$str .= ",MaxLenghtOfStay=" . ReplaceInMTrad(GetStrParam(MaxLenghtOfStay), $m->MaxLenghtOfStay, $IdMember);
		$str .= ",AdditionalAccomodationInfo=" . ReplaceInMTrad(GetStrParam(AdditionalAccomodationInfo), $m->AdditionalAccomodationInfo, $IdMember);
		$str .= ",Restrictions='" . $Restrictions . "'";
		$str .= ",OtherRestrictions=" . ReplaceInMTrad(GetStrParam(OtherRestrictions), $m->OtherRestrictions, $IdMember);
		
		if (!$CanTranslate) { // a volunteer translator will not be allowed to update crypted data		
		    $str .= ",HomePhoneNumber=" . ReplaceInCrypted(GetStrParam(HomePhoneNumber), $m->HomePhoneNumber, $IdMember, ShallICrypt("HomePhoneNumber"));
			$str .= ",CellPhoneNumber=" . ReplaceInCrypted(GetStrParam(CellPhoneNumber), $m->CellPhoneNumber, $IdMember, ShallICrypt("CellPhoneNumber"));
			$str .= ",WorkPhoneNumber=" . ReplaceInCrypted(GetStrParam(WorkPhoneNumber), $m->WorkPhoneNumber, $IdMember, ShallICrypt("WorkPhoneNumber"));
			$str .= ",chat_SKYPE=" . ReplaceInCrypted(GetStrParam(chat_SKYPE), $m->chat_SKYPE, $IdMember, ShallICrypt("chat_SKYPE"));
			$str .= ",chat_MSN=" . ReplaceInCrypted(GetStrParam(chat_MSN), $m->chat_MSN, $IdMember, ShallICrypt("chat_MSN"));
			$str .= ",chat_AOL=" . ReplaceInCrypted(GetParam(chat_AOL), $m->chat_AOL, $IdMember, ShallICrypt("chat_AOL"));
			$str .= ",chat_YAHOO=" . ReplaceInCrypted(GetStrParam(chat_YAHOO), $m->chat_YAHOO, $IdMember, ShallICrypt("chat_YAHOO"));
			$str .= ",chat_ICQ=" . ReplaceInCrypted(GetStrParam(chat_ICQ), $m->chat_ICQ, $IdMember, ShallICrypt("chat_ICQ"));
			$str .= ",chat_Others=" . ReplaceInCrypted(GetStrParam(chat_Others), $m->chat_Others, $IdMember, ShallICrypt("chat_Others"));
		}

		$str .= " where id=" . $IdMember;
		sql_query($str);

		if (!$CanTranslate) { // a volunteer translator will not be allowed to update crypted data		
		    // Only update hide/unhide for identity fields
		    ReplaceInCrypted(addslashes($ReadCrypted($m->FirstName)), $m->FirstName, $IdMember, ShallICrypt("FirstName"));
			ReplaceInCrypted(addslashes($ReadCrypted($m->SecondName)), $m->SecondName, $IdMember, ShallICrypt("SecondName"));
			ReplaceInCrypted(addslashes($ReadCrypted($m->LastName)), $m->LastName, $IdMember, ShallICrypt("LastName"));
			
			ReplaceInCrypted(addslashes($ReadCrypted($rAdresse->Zip)),$rAdresse->Zip,$IdMember,ShallICrypt("Zip"));
			ReplaceInCrypted(addslashes($ReadCrypted($rAdresse->HouseNumber)),$rAdresse->HouseNumber,$IdMember,ShallICrypt("Address"));
			ReplaceInCrypted(addslashes($ReadCrypted($rAdresse->StreetName)),$rAdresse->StreetName,$IdMember,ShallICrypt("Address"));


			// if email has changed
			if (GetParam("Email") != $ReadCrypted($m->Email)) {
			   ReplaceInCrypted(GetStrParam("Email"), $m->Email, $IdMember, true);
			   LogStr("Email updated (previous was " . $ReadCrypted($m->Email) . ")", "Email Update");
			}
		}


		// updates groups
		$max = count($TGroups);
		for ($ii = 0; $ii < $max; $ii++) {
			$ss = addslashes($_POST["Group_" . $TGroups[$ii]->Name]);
			//				 echo "replace $ss<br> for \$TGroups[",$ii,"]->Comment=",$TGroups[$ii]->Comment," \$IdMember=",$IdMember,"<br> "; continue;

			$IdTrad = ReplaceInMTrad($ss, $TGroups[$ii]->Comment, $IdMember);
			if ((GetParam("AcceptMessage_".$TGroups[$ii]->Name)=="on") or (GetStrParam("AcceptMessage_".$TGroups[$ii]->Name)=="yes")) $AcceptMess="yes";
			else  $AcceptMess="no";

			//				echo "replace $ss<br> for \$IdTrad=",$IdTrad,"<br>�;;
			if (($IdTrad != $TGroups[$ii]->Comment) or ($TGroups[$ii]->IacceptMassMailFromThisGroup!=$AcceptMess)){ // if has changed
				MakeRevision($TGroups[$ii]->id, "membersgroups"); // create revision
				sql_query("update membersgroups set Comment=" . $IdTrad . ",IacceptMassMailFromThisGroup='".$AcceptMess."' where id=" . $TGroups[$ii]->id);
			}
		}

		
// 	Update relations 
		$Relations = array ();
		$str = "select SQL_CACHE specialrelations.*,members.Username as Username,members.Gender as Gender,members.HideGender as HideGender from specialrelations,members where IdOwner=".$IdMember." and specialrelations.Confirmed='Yes' and members.id=specialrelations.IdRelation and members.Status='Active'";
		$qry = mysql_query($str);
		while ($rr = mysql_fetch_object($qry)) {
			$rr->Comment=FindTrad($rr->Comment);
			array_push($Relations, $rr);
		}
		$max = count($Relations);
		for ($ii = 0; $ii < $max; $ii++) {
			$ss = addslashes($_POST["RelationComment_" . $Relations[$ii]->id]);

			$IdTrad = ReplaceInMTrad($ss, $Relations[$ii]->Comment, $IdMember);
			//				echo "replace $ss<br> for \$IdTrad=",$IdTrad,"<br>�;;
			if ($IdTrad != $Relations[$ii]->Comment) { // if has changed
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
		if (GetParam("memberslanguageslevel_newIdLanguage") != "") {
			$str = "insert into memberslanguageslevel (IdLanguage,Level,IdMember) values(" . GetStrParam("memberslanguageslevel_newIdLanguage") . ",'" . GetParam("memberslanguageslevel_newLevel") . $rr->id . "'," . $IdMember . ")";
			sql_query($str);
		}

		if ($IdMember == $_SESSION['IdMember'])
			LogStr("Profil update by member himself", "Profil update");
		else
			LogStr("update of another profil", "Profil update");

// now go to member profile
		header("Location: "."member.php?cid=".$m->Username,true); 
		exit(0);
		break;
	case "logout" :
		Logout("main.php");
		exit (0);
}

$m = prepare_profile_header($IdMember," and (Status='Active' or Status='Pending')"); // pending members can edit their profile 

die("444") ;
// Try to load specialrelations and caracteristics belong to
$Relations = array ();
$str = "select SQL_CACHE specialrelations.*,members.Username as Username,members.Gender as Gender,members.HideGender as HideGender from specialrelations,members where IdOwner=".$IdMember." and specialrelations.Confirmed='Yes' and members.id=specialrelations.IdRelation and members.Status='Active'";
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


if ($m->Status == "Pending") {
	$profilewarning = ww("YouCanCompleteProfAndWait", $m->Username);
}
elseif ($m->Status != "Active") {
	$profilewarning .= "WARNING the status of " . $m->Username . " is set to " . $m->Status;
}

$m->MyRestrictions = explode(",", $m->Restrictions);
$m->TabRestrictions = sql_get_set("members", "Restrictions");
include "layout/editmyprofile.php";
DisplayEditMyProfile($m, $profilewarning, $TGroups,$CanTranslate);
?>
