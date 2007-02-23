<?php
require_once "lib/init.php";

$action = GetParam("action");

switch ($action) {
}

// prepare the countries list
$str = "select members.id as IdMember,members.Username as Username,countries.id as id,countries.Name as CountryName,regions.Name as RegionName,cities.Name as CityName,members.ProfileSummary  from countries,members,cities,regions where members.IdCity=cities.id and members.Status='Active' and cities.IdRegion=regions.id and countries.id=regions.IdCountry and cities.id=".GetParam("IdCity")."  and members.Status='Active' order by countries.id,regions.id,cities.id ";
$qry = mysql_query($str);
$TList = array ();
while ($rWhile = mysql_fetch_object($qry)) {
	if (!IsLoggedIn()) {
	   if (!IsPublic($rWhile->IdMember)) {
	   	  $rWhile->Username="not public profile" ;
	   } 
	}
	$rWhile->ProfileSummary=FindTrad($rWhile->ProfileSummary,true) ;
   $photo=LoadRow("select SQL_CACHE * from membersphotos where IdMember=" . $rWhile->IdMember . " and SortOrder=0");
	if (isset($photo->FilePath)) $rWhile->photo=$photo->FilePath ; 
	array_push($TList, $rWhile);
}

require_once "layout/membersbycities.php";
$where=LoadRow("select cities.Name as CityName,cities.id as IdCity,countries.Name as CountryName,regions.Name as RegionName from countries,regions,cities where cities.id=".GetParam("IdCity")." and regions.IdCountry=countries.id and regions.id=cities.IdRegion") ; 
DisplayCities($TList,$where); // call the layout with all countries
?>
