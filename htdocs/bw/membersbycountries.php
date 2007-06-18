<?php
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
		Logout("main.php");
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
