<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/
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



//	echo "str=$str<br />";
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

// compute totmembers
$rr=LoadRow("select SQL_CACHE count(*) as cnt from members where (Status='Active' or Status='InActive')") ;
$TotMember=$rr->cnt ;

require_once "layout/whoisonline.php";
DisplayWhoIsOnline($TData,$TGuest,$TotMember,$TotMemberSinceMidnight);
?>
