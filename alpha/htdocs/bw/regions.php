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

switch ($action) {

// todo here process the action according to 		
}

// prepare the regions list only for Active members
$str = "select regions.name  as region,
regions.id as IdRegion, count(members.id) as cnt
from members, regions, cities
where  members.idcity = cities.id and cities.idregion = regions.id
and cities.IdCountry=" . GetParam("IdCountry") . " and members.Status='Active' group by regions.id order by regions.name ";

$qry = mysql_query($str);
$TList = array ();
while ($rWhile = mysql_fetch_object($qry)) {
	array_push($TList, $rWhile);
}


require_once "layout/regions.php";
DisplayCountries(getcountryname(GetParam("IdCountry")),GetParam("IdCountry"),$TList); // call the layout with all countries
?>
