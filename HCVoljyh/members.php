<?php
include "lib/dbaccess.php";
require_once "lib/FunctionsLogin.php";
require_once "layout/error.php";

switch (GetParam("action")) {
	case "logout" :
		Logout("login.php");
		exit (0);
}

if (IsLogged()) {
	$str = "select SQL_CACHE members.*,cities.Name as cityname,regions.Name as regionname,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment from cities,countries,regions,members left join membersphotos on membersphotos.IdMember=members.id where cities.IdRegion=regions.id and countries.id=regions.IdCountry and cities.id=members.IdCity and status='Active' GROUP BY members.id order by members.LastLogin desc";
} else {
	// Todo there only select profile publics
	$str = "select SQL_CACHE members.*,cities.Name as cityname,regions.Name as regionname,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment from cities,countries,regions,memberspublicprofiles,members left join membersphotos on membersphotos.IdMember=members.id where cities.IdRegion=regions.id and countries.id=regions.IdCountry and cities.id=members.IdCity and status='Active' and memberspublicprofiles.IdMember=members.id GROUP BY members.id order by members.LastLogin desc";
}

$TData = array ();
$qry = mysql_query($str);
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

include "layout/members.php";
DisplayMembers($TData);
?>
