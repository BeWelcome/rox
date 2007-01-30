<?php
include "lib/dbaccess.php";
require_once "layout/error.php";
require_once "prepare_profile_header.php";

// Find parameters
$IdMember = IdMember(GetParam("cid", ""));

if ($IdMember == 0) {
	$errcode = "ErrorWithParameters";
	DisplayError(ww("ErrorWithParameters", "\$IdMember is not defined"));
	exit (0);
}

$photorank=GetParam("photorank",0) ;
switch (GetParam("action")) {
	case "previouspicture" :
		$photorank--;
		if ($photorank <= 0) {
	  	    $rr=LoadRow("select SQL_CACHE * from membersphotos where IdMember=" . $IdMember . " order by SortOrder desc limit 1");
			$photorank = $rr->SortOrder;
		break;
	case "nextpicture" :
		$photorank++;
		break;
	case "logout" :
		Logout("main.php");
		exit (0);
}

$m = prepare_profile_header($IdMember,$wherestatus,$photorank) ; 

// Try to load groups and caracteristics where the member belong to
$TGroups = array ();
$str = "select SQL_CACHE membersgroups.Comment as Comment,groups.Name as Name,groups.id as IdGroup from groups,membersgroups where membersgroups.IdGroup=groups.id and membersgroups.Status='In' and membersgroups.IdMember=" . $m->id;
$qry = mysql_query($str);
$TGroups = array ();
while ($rr = mysql_fetch_object($qry)) {
	array_push($TGroups, $rr);
}

// Load phone
if ($m->HomePhoneNumber > 0) {
	$m->DisplayHomePhoneNumber = PublicReadCrypted($m->HomePhoneNumber, ww("Hidden"));
}
if ($m->CellPhoneNumber > 0) {
	$m->DisplayCellPhoneNumber = PublicReadCrypted($m->CellPhoneNumber, ww("Hidden"));
}
if ($m->WorkPhoneNumber > 0) {
	$m->DisplayWorkPhoneNumber = PublicReadCrypted($m->WorkPhoneNumber, ww("Hidden"));
}

if ($m->Restrictions == "") {
	$m->TabRestrictions = array ();
} else {
	$m->TabRestrictions = explode(",", $m->Restrictions);
}

if ($m->OtherRestrictions > 0)
	$m->OtherRestrictions = FindTrad($m->OtherRestrictions);
else
	$m->OtherRestrictions = "";

// Load the language the members nows
$TLanguages = array ();
$str = "select SQL_CACHE memberslanguageslevel.IdLanguage as IdLanguage,languages.Name as Name,memberslanguageslevel.Level from memberslanguageslevel,languages where memberslanguageslevel.IdMember=" . $m->id . " and memberslanguageslevel.IdLanguage=languages.id";
$qry = mysql_query($str);
while ($rr = mysql_fetch_object($qry)) {
	array_push($TLanguages, $rr);
}
$m->TLanguages = $TLanguages;

// Make some translation to have blankstring in case records are empty
$m->ILiveWith = FindTrad($m->ILiveWith);
$m->MaxLenghtOfStay = FindTrad($m->MaxLenghtOfStay);
$m->MotivationForHospitality = FindTrad($m->MotivationForHospitality);
$m->Offer = FindTrad($m->Offer);
$m->Organizations = FindTrad($m->Organizations);
$m->AdditionalAccomodationInfo = FindTrad($m->AdditionalAccomodationInfo);
$m->InformationToGuest = FindTrad($m->InformationToGuest);

// see if the visit of the profile need to be logged
if (($IdMember != $_SESSION["IdMember"]) and ($_SESSION["IdMember"] != 1) and (IsLogged())) { // don't log admin visits or visit on self profile
	$str = "insert into recentvisits(IdMember,IdVisitor) values(" . $m->id . "," . $_SESSION["IdMember"] . ")";
	sql_query($str);
}

include "layout/member.php";
DisplayMember($m, $m->profilewarning, $TGroups);
?>
