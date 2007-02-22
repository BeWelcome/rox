<?php
require_once "lib/init.php";
require_once "layout/error.php";
require_once "prepare_profile_header.php";
include "layout/mytranslators.php";

MustLogIn() ;

// Find parameters
$IdMember = $_SESSION['IdMember'];
if (IsAdmin()) { // admin can alter other profiles
	$IdMember = GetParam("cid", $_SESSION['IdMember']);
}

$m = prepare_profile_header($IdMember,"",0) ; // This is the profile of the contact which is going to be used

switch (GetParam("action")) {
	case "del" : // todo
		break;
	case "add" : // todo
		break;
}

$TData = array ();
$str = "select intermembertranslations.*,members.Username,members.ProfileSummary,cities.Name as cityname,regions.Name as regionname,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment";
$str .= " from intermembertranslations,cities,countries,regions,recentvisits,members left join membersphotos on membersphotos.IdMember=members.id and membersphotos.SortOrder=0 where cities.IdRegion=regions.id and countries.id=regions.IdCountry and cities.id=members.IdCity and status='Active' and members.id=intermembertranslations.IdTranslator and intermembertranslations.IdMember=" . $IdMember . " and members.status='Active' GROUP BY members.id order by intermembertranslations.updated desc";
$qry = sql_query($str);
while ($rr = mysql_fetch_object($qry)) {
	if ($rr->ProfileSummary > 0) {
		$rr->ProfileSummary = FindTrad($rr->ProfileSummary);
	} else {
		$rr->ProfileSummary = "";
	}
	array_push($TData, $rr);
}

DisplayMyTranslators($TData,$m);
?>
