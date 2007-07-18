<?php
require_once "lib/init.php";
require_once "lib/FunctionsLogin.php";
require_once "layout/error.php";

switch (GetParam("action")) {

}

if (IsLoggedIn()) {
	$str = "select now()-online.updated as NbSec ,members.*,cities.Name as cityname,cities.IdRegion as IdRegion,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment,online.updated as lastdateaction,lastactivity from cities,countries,online,members left join membersphotos on membersphotos.IdMember=members.id where countries.id=cities.IdCountry and cities.id=members.IdCity and members.Status='Active' and online.IdMember=members.id and online.updated>DATE_SUB(now(),interval " . $_SYSHCVOL['WhoIsOnlineDelayInMinutes'] . " minute) GROUP BY members.id order by members.LastLogin desc";
} else {
	$str = "select members.*,cities.Name as cityname,cities.IdRegion as IdRegion,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment,online.updated as lastdateaction,lastactivity from cities,countries,online,memberspublicprofiles,members left join membersphotos on membersphotos.IdMember=members.id where countries.id=cities.IdCountry and cities.id=members.IdCity and members.Status='Active' and online.IdMember=members.id and online.updated>DATE_SUB(now(),interval " . $_SYSHCVOL['WhoIsOnlineDelayInMinutes'] . " minute) and online.IdMember=members.id and memberspublicprofiles.IdMember=members.id GROUP BY members.id order by members.LastLogin desc";
}

$TData = array ();
$qry = mysql_query($str);



//	echo "str=$str<br>";
while ($rr = mysql_fetch_object($qry)) {

// If no picture provide dummy pict instead
	if ($rr->photo=="") {
		$rr->photo =DummyPict($rr->Gender,$rr->HideGender) ;
		$rr->phototext = "no picture provided";
	}

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
	if ($rr->IdRegion>0) { // let consider that in some case members can have a city without region 
	   $rregion=LoadRow("select Name from regions where id=".$rr->IdRegion) ;
	   $rr->regionname=$rregion->Name ;
	}
	else {
		 $rr->regionname=ww("NoRegionDefined") ;
	}

	array_push($TData, $rr);
}

$TGuest=array() ;
if (IsAdmin()) {
	$str = "select appearance,lastactivity,now()-updated as NbSec from guestsonline where guestsonline.updated>DATE_SUB(now(),interval " . $_SYSHCVOL['WhoIsOnlineDelayInMinutes'] . " minute) order by guestsonline.updated  desc";
	$qry = mysql_query($str);
	while ($rr = mysql_fetch_object($qry)) {
		array_push($TGuest, $rr);
  }
}

require_once "layout/whoisonline.php";
DisplayWhoIsOnline($TData,$TGuest);
?>
