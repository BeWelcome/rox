<?php
require_once "lib/init.php";
require_once "layout/error.php";

switch (GetParam("action")) {

}
$limitcount=Getparam("limitcount",200) ;
if (IsLoggedIn()) {
	$str = "select SQL_CACHE rightsvolunteers.Comment as Description,members.*,cities.Name as cityname,regions.Name as regionname,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment from (cities,countries,regions,members,rightsvolunteers)  left join membersphotos on (membersphotos.IdMember=members.id and membersphotos.SortOrder=0) where cities.IdRegion=regions.id and countries.id=regions.IdCountry and cities.id=members.IdCity and status='Active' and rightsvolunteers.IdMember=members.id and rightsvolunteers.IdRight=19 and rightsvolunteers.level>0 limit ".$limitcount ;
} else {
	$str = "select SQL_CACHE rightsvolunteers.Comment as Description,members.*,cities.Name as cityname,regions.Name as regionname,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment from (cities,countries,regions,memberspublicprofiles,members,rightsvolunteers)  left join membersphotos on (membersphotos.IdMember=members.id and membersphotos.SortOrder=0) where cities.IdRegion=regions.id and countries.id=regions.IdCountry and cities.id=members.IdCity and status='Active' and memberspublicprofiles.IdMember=members.id and rightsvolunteers.IdMember=members.id and rightsvolunteers.IdRight=19 and rightsvolunteers.level>0 limit ".$limitcount ; 
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

include "layout/responsibles.php";
DisplayResponsibles($TData);
?>
