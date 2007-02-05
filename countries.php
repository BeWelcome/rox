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
$str = "select countries.name as country, regions.name  as region,
countries.id as id, count(members.id) as cnt
from members, regions, cities, countries
where  members.idcity = cities.id and cities.idregion = regions.id
and regions.idcountry=countries.id  and members.Status='Active' 
group by id order by countries.name ";

$qry = mysql_query($str);
$TList = array ();
while ($rWhile = mysql_fetch_object($qry)) {
	array_push($TList, $rWhile);
}

require_once "layout/countries.php";
DisplayCountries($TList); // call the layout with all countries
?>
