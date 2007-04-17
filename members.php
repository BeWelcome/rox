<?php
require_once "lib/init.php";
require_once "layout/error.php";

switch (GetParam("action")) {

}

$limitcount=GetParam("limitcount",10); // Number of records per page
$start_rec=GetParam("start_rec",0); // Number of records per page

if (IsLoggedIn()) {
	$str = "select SQL_CACHE members.*,cities.Name as cityname,IdRegion,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment from cities,countries,members left join membersphotos on membersphotos.IdMember=members.id and membersphotos.SortOrder=0 where countries.id=cities.IdCountry and cities.id=members.IdCity and status='Active' GROUP BY members.id order by members.LastLogin desc  limit $start_rec,".$limitcount;
	$rtot=LoadRow("select SQL_CACHE count(*) as cnt from members where status='Active'");
} else { // if not logged in, only use public profile
	$str = "select SQL_CACHE members.*,cities.Name as cityname,IdRegion,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment from cities,countries,memberspublicprofiles,members left join membersphotos on membersphotos.IdMember=members.id and membersphotos.SortOrder=0 where countries.id=cities.IdCountry and cities.id=members.IdCity and status='Active' and memberspublicprofiles.IdMember=members.id GROUP BY members.id order by members.LastLogin desc  limit $start_rec,".$limitcount; 
	$rtot=LoadRow("select SQL_CACHE count(*) as cnt from  members,memberspublicprofiles where status='Active' and memberspublicprofiles.IdMember=members.id");
}

$TData = array ();
$qry = mysql_query($str);

// MAU counting the max to reach TODO probable bug to fix (need additional query ?)
$maxpos=$rtot->cnt ;

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

   $rr->regionname=getregionname($rr->IdRegion) ;
	
	array_push($TData, $rr);
}

include "layout/members.php";
DisplayMembers($TData,$maxpos);
?>
