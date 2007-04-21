<?php
require_once "lib/init.php";

require_once "layout/error.php";

$action = GetParam("action");

switch ($action) {
	case "logout" :
		Logout("main.php");
		exit (0);
		
// todo here process the action according to 		
}

// prepare the countries list only for Active members
$str = "select countries.name as country, 
countries.id as IdCountry, count(members.id) as cnt,cities.IdRegion as IdRegion
from members, cities, countries
where  members.IdCity = cities.id 
and cities.IdCountry=countries.id  and members.Status='Active' 
group by countries.id order by countries.name ";

$qry = mysql_query($str);
$TList = array ();
while ($rWhile = mysql_fetch_object($qry)) {
	$rWhile->region=getregionname($rWhile->IdRegion)
	array_push($TList, $rWhile);
}

require_once "layout/countries.php";
DisplayCountries($TList); // call the layout with all countries
?>
