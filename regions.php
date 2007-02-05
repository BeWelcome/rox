<?php
require_once "lib/init.php";

require_once "layout/error.php";

$action = GetParam("action");
$idregion = GetParam("countryId");

switch ($action) {
	case "logout" :
		Logout("main.php");
		exit (0);
		
// todo here process the action according to 		
}

// prepare the regions list only for Active members
$str = "select regions.name  as region,
regions.id as id, count(members.id) as cnt
from members, regions, cities
where  members.idcity = cities.id and cities.idregion = regions.id
and regions.idcountry=" . $idregion . " and members.Status='Active' group by id order by regions.name ";

$qry = mysql_query($str);
$TList = array ();
while ($rWhile = mysql_fetch_object($qry)) {
	array_push($TList, $rWhile);
}


require_once "layout/regions.php";
DisplayCountries($TList); // call the layout with all countries
?>
