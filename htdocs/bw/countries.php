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
