<?php
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
where  members.idcity = cities.id and cities.idregion=" . $IdRegion . " and regions.id=cities.IdRegion group by cities.id order by cities.name ";

$qry = mysql_query($str);
$TList = array ();
while ($rWhile = mysql_fetch_object($qry)) {
	array_push($TList, $rWhile);
}

require_once "layout/cities.php";
$where=LoadRow("select countries.id as IdCountry,regions.id as IdRegion,countries.Name as CountryName,regions.Name as RegionName from countries,regions where regions.IdCountry=countries.id and regions.id=".GetParam("IdRegion")); 
DisplayCountries($TList,$where); // call the layout with all countries
?>
