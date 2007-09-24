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

$action = GetParam("action");
$IdRegion = GetParam("IdRegion");

switch ($action) {
// todo here process the action according to 		
}

// prepare the regions list only for Active members
$str = "select cities.name  as city,
cities.id as IdCity, count(members.id) as cnt,cities.IdRegion as IdRegion,cities.IdCountry as IdCountry 
from members, cities,regions
where  members.idcity = cities.id and members.Status='Active' and cities.idregion=" . $IdRegion . " and regions.id=cities.IdRegion group by cities.id order by cities.name ";

$qry = mysql_query($str);
$TList = array ();
while ($rWhile = mysql_fetch_object($qry)) {
	array_push($TList, $rWhile);
}

require_once "layout/cities.php";
$where=LoadRow("select countries.id as IdCountry,regions.id as IdRegion,countries.Name as CountryName,regions.Name as RegionName from countries,regions where regions.IdCountry=countries.id and regions.id=".GetParam("IdRegion")); 
DisplayCountries($TList,$where); // call the layout with all countries
?>
