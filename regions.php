<?php
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
