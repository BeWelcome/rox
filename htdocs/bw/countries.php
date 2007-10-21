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

$action = GetParam("action");

switch ($action) {
	case "logout" :
		Logout();
		exit (0);
		
// todo here process the action according to 		
}

// prepare the countries list only for Active members
$str = "SELECT countries.name AS country, 
countries.id AS IdCountry, COUNT(members.id) AS cnt,cities.IdRegion AS IdRegion
FROM members, cities, countries
WHERE  members.IdCity = cities.id 
AND cities.IdCountry=countries.id  AND members.Status='Active' 
GROUP BY countries.id ORDER BY countries.name ";

$qry = sql_query($str);
$TList = array ();
while ($rWhile = mysql_fetch_object($qry)) {
	$rWhile->region=getregionname($rWhile->IdRegion) ;
	array_push($TList, $rWhile);
}

require_once "layout/countries.php";
DisplayCountries($TList); // call the layout with all countries
?>
