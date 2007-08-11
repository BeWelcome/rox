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
$str = "select profilesvisits.updated as datevisite,members.Username,members.ProfileSummary,cities.Name as cityname,regions.Name as regionname,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment";
$str .= " from cities,countries,regions,profilesvisits,members,membersphotos where membersphotos.IdMember=members.id and membersphotos.SortOrder=0 and cities.IdRegion=regions.id and countries.id=cities.IdCountry and cities.id=members.IdCity and status='Active' and members.id=profilesvisits.IdVisitor and profilesvisits.IdMember=" . $IdMember . " and members.status='Active' GROUP BY members.id order by profilesvisits.updated desc";

// regardless pictures
$str = "select profilesvisits.updated as datevisite,members.Username,members.ProfileSummary,cities.Name as cityname,countries.Name as countryname,membersphotos.FilePath as photo ";
$str .= " from (cities,countries,profilesvisits,members) left join membersphotos on (membersphotos.IdMember=members.id and membersphotos.SortOrder=0) where (countries.id=cities.IdCountry and cities.id=members.IdCity and members.id=profilesvisits.IdVisitor and profilesvisits.IdMember=" . $IdMember . " and members.Status='Active') GROUP BY members.Username order by profilesvisits.updated desc limit 40";

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
