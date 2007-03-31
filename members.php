<?php
require_once "lib/init.php";
require_once "layout/error.php";

switch (GetParam("action")) {

}
$limitcount=Getparam("limitcount",200);
if (IsLoggedIn()) {
	$str = "select SQL_CACHE members.*,cities.Name as cityname,regions.Name as regionname,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment from cities,countries,regions,members left join membersphotos on membersphotos.IdMember=members.id and membersphotos.SortOrder=0 where cities.IdRegion=regions.id and countries.id=cities.IdCountry and cities.id=members.IdCity and status='Active' GROUP BY members.id order by members.LastLogin desc  limit ".$limitcount;
} else {
	$str = "select SQL_CACHE members.*,cities.Name as cityname,regions.Name as regionname,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment from cities,countries,regions,memberspublicprofiles,members left join membersphotos on membersphotos.IdMember=members.id and membersphotos.SortOrder=0 where cities.IdRegion=regions.id and countries.id=cities.IdCountry and cities.id=members.IdCity and status='Active' and memberspublicprofiles.IdMember=members.id GROUP BY members.id order by members.LastLogin desc  limit ".$limitcount; 
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
		$rr->ProfileSummary = FindTrad($rr->ProfileSummary,true);
	} else {
		$rr->ProfileSummary = "";
	}

	array_push($TData, $rr);
}

include "layout/members.php";
DisplayMembers($TData);
?>
