<?php
require_once "lib/init.php";
require_once "layout/error.php";
require_once "lib/prepare_profile_header.php";
require_once "layout/myvisitors.php";

MustLogIn();

// Find parameters
$IdMember = $_SESSION['IdMember'];
if (IsAdmin()) { // admin can alter other profiles
	$IdMember = IdMember(GetStrParam("cid", $_SESSION['IdMember']));
}

$m = prepareProfileHeader($IdMember,"",0); // This is the profile of the member which is concerned by visits

switch (GetParam("action")) {
	case "del" : // todo
		break;
}

$TData = array ();

// this is with picture only
$str = "select recentvisits.created as datevisite,members.Username,members.ProfileSummary,cities.Name as cityname,regions.Name as regionname,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment";
$str .= " from cities,countries,regions,recentvisits,members,membersphotos where membersphotos.IdMember=members.id and membersphotos.SortOrder=0 and cities.IdRegion=regions.id and countries.id=cities.IdCountry and cities.id=members.IdCity and status='Active' and members.id=recentvisits.IdVisitor and recentvisits.IdMember=" . $IdMember . " and members.status='Active' GROUP BY members.id order by recentvisits.created desc";

// regardless pictures
$str = "select recentvisits.created as datevisite,members.Username,members.ProfileSummary,cities.Name as cityname,regions.Name as regionname,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment";
$str .= " from (cities,countries,regions,recentvisits,members) left join membersphotos on membersphotos.IdMember=members.id and membersphotos.SortOrder=0 where cities.IdRegion=regions.id and countries.id=cities.IdCountry and cities.id=members.IdCity and status='Active' and members.id=recentvisits.IdVisitor and recentvisits.IdMember=" . $IdMember . " and members.status='Active' GROUP BY members.id order by recentvisits.created desc";
$qry = sql_query($str);
while ($rr = mysql_fetch_object($qry)) {
	if ($rr->Comment > 0) {
		$rr->phototext = FindTrad($rr->Comment);
	} else {
		$rr->phototext = "no comment";
	}
	if ($rr->ProfileSummary > 0) {
		$rr->ProfileSummary = FindTrad($rr->ProfileSummary);
	} else {
		$rr->ProfileSummary = "";
	}
	array_push($TData, $rr);
}

DisplayMyVisitors($TData,$m);
?>
