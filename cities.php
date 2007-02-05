<?php
require_once "lib/init.php";

require_once "layout/error.php";

$action = GetParam("action");
$idcity = GetParam("cityId");

switch ($action) {
	case "logout" :
		Logout("main.php");
		exit (0);
		
// todo here process the action according to 		
}

// prepare the regions list only for Active members
$str = "select cities.name  as city,
cities.id as id, count(members.id) as cnt
from members, cities
where  members.idcity = cities.id and cities.idregion=" . $idcity . " group by id order by cities.name ";

$qry = mysql_query($str);
$TList = array ();
while ($rWhile = mysql_fetch_object($qry)) {
	array_push($TList, $rWhile);
}


require_once "layout/cities.php";
DisplayCountries($TList); // call the layout with all countries
?>
