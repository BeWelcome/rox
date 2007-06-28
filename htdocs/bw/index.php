<?php
require_once "lib/init.php";
require_once "layout/index.php";

if (GetParam("IndexMicha","no")=="") {
  DisplayIndex();
  exit(0);
} 

//Prepare Member Selection
	$mlastpublic = "select SQL_CACHE members.*,cities.Name as cityname,IdRegion,countries.Name as countryname,membersphotos.FilePath as photo,membersphotos.Comment from cities,countries,memberspublicprofiles,members left join membersphotos on membersphotos.IdMember=members.id and membersphotos.SortOrder=0 where countries.id=cities.IdCountry and cities.id=members.IdCity and status='Active' and memberspublicprofiles.IdMember=members.id GROUP BY members.id order by members.LastLogin desc  limit 1"; 


if (IsLoggedIn()) {
/*  DisplayIndexLogged($_SESSION["Username"]); */
  DisplayIndex();
}
else {
  DisplayIndex();
}
?>
