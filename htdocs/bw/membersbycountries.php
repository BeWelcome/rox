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
// Some informatin about geography
// The way that cities are associated to regions
// update cities set cities.IdRegion =
//    (select id from regions where regions.feature_code='ADM1' and regions.admin1_code=cities.admin1_code and regions.IdCountry=cities.IdCountry) 
//    where cities.IdRegion=0 LIMIT 10000


require_once "lib/init.php";
require_once "lib/FunctionsLogin.php";
require_once "layout/error.php";

$action = GetParam("action");

switch ($action) {
	case "logout" :
		Logout();
		exit (0);
}

// prepare the countries list
$str = "select members.Username as Username,countries.id as id,countries.Name as CountryName,regions.Name as RegionName,cities.Name as CityName  from (countries,members,cities) left join regions on (cities.IdRegion=regions.id) where members.IdCity=cities.id and members.Status='Active' and countries.id=cities.IdCountry order by countries.id,regions.id,cities.id ";
$qry = mysql_query($str);
$TList = array ();
while ($rWhile = mysql_fetch_object($qry)) {
	array_push($TList, $rWhile);
}

require_once "layout/membersbycountries.php";
DisplayCountries($TList); // call the layout with all countries
?>
