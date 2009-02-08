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
require_once "layout/error.php";
require_once "lib/prepare_profile_header.php";
require_once "layout/myvisitors.php";

MustLogIn();

// Find parameters
$IdMember = $_SESSION['IdMember'];
if (IsAdmin()) { // admin can see recent visitors of other profiles
	$IdMember = IdMember(GetStrParam("cid", $_SESSION['IdMember']));
}

$m = prepareProfileHeader($IdMember,"",0); // This is the profile of the member which is concerned by visits

$TData = array ();

// regardless pictures
$str = "SELECT profilesvisits.updated as datevisite,members.Gender,members.HideGender,members.created as MemberSince,regions.Name as RegionName,members.LastLogin,members.Username,BirthDate,HideBirthDate,members.ProfileSummary,cities.Name as cityname,countries.Name as countryname,countries.isoalpha2 as CountryCode,membersphotos.FilePath as photo ";
$str .= " FROM (cities,countries,profilesvisits,members) " ;
$str .= " left join membersphotos on (membersphotos.IdMember=members.id and membersphotos.SortOrder=0) " ;
$str .= " left join regions on (regions.id=cities.IdRegion) " ;
$str .= " where (countries.id=cities.IdCountry and cities.id=members.IdCity and members.id=profilesvisits.IdVisitor and profilesvisits.IdMember=" . $IdMember . " and members.Status='Active') GROUP BY members.Username order by profilesvisits.updated desc limit 50";

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
	
	$rr->MemberSince=strftime('%d/%m/%Y',strtotime($rr->MemberSince)) ;

	// Load Age
	$rr->age = fage($rr->BirthDate, $rr->HideBirthDate);
	array_push($TData, $rr);
}

DisplayMyVisitors($TData, $m);
?>
