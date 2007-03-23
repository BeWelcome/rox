<?php
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
$str = "select members.Username as Username,countries.id as id,countries.Name as CountryName,regions.Name as RegionName,cities.Name as CityName  from countries,members,cities,regions where members.IdCity=cities.id and members.Status='Active' and cities.IdRegion=regions.id and countries.id=cities.IdCountry order by countries.id,regions.id,cities.id ";
$qry = mysql_query($str);
$TList = array ();
while ($rWhile = mysql_fetch_object($qry)) {
	array_push($TList, $rWhile);
}

require_once "layout/membersbycountries.php";
DisplayCountries($TList); // call the layout with all countries
?>
