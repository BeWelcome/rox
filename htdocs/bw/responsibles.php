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

switch (GetParam("action")) {

}
$limitcount=Getparam("limitcount",200);
if (IsLoggedIn()) {
	$str = "select SQL_CACHE rightsvolunteers.Comment as Description,members.*,cities.Name as cityname,regions.Name as regionname,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment from (cities,countries,regions,members,rightsvolunteers)  left join membersphotos on (membersphotos.IdMember=members.id and membersphotos.SortOrder=0) where cities.IdRegion=regions.id and countries.id=cities.IdCountry and cities.id=members.IdCity and status='Active' and rightsvolunteers.IdMember=members.id and rightsvolunteers.IdRight=19 and rightsvolunteers.level>0 limit ".$limitcount;
} else {
	$str = "select SQL_CACHE rightsvolunteers.Comment as Description,members.*,cities.Name as cityname,regions.Name as regionname,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment from (cities,countries,regions,memberspublicprofiles,members,rightsvolunteers)  left join membersphotos on (membersphotos.IdMember=members.id and membersphotos.SortOrder=0) where cities.IdRegion=regions.id and countries.id=cities.IdCountry and cities.id=members.IdCity and status='Active' and memberspublicprofiles.IdMember=members.id and rightsvolunteers.IdMember=members.id and rightsvolunteers.IdRight=19 and rightsvolunteers.level>0 limit ".$limitcount; 
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

require_once "layout/responsibles.php";
DisplayResponsibles($TData);
?>
