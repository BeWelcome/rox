<?php
require_once "lib/init.php";
require_once "layout/quicksearch.php";

$TList = array ();

if (strlen(rtrim(ltrim(GetStrParam("searchtext"))))<=1) { // if void search don't search !
	DisplayResults($TList, GetStrParam("searchtext")); // call the layout with no results
	exit(0) ;
} 

switch (GetParam("action")) {

	case "quicksearch" :
		// prepare the result list (build the $TList array)

		// search for username or organization  
		$str = "select id,Username,Organizations as result,ProfileSummary from members where Status=\"Active\" and (Username like '%" . addslashes(GetStrParam("searchtext")) . "%' or Organizations like '%" . addslashes(GetStrParam("searchtext")) . "%')";
		$qry = mysql_query($str);
		while ($rr = mysql_fetch_object($qry)) {
			$cc=LoadRow ("select countries.Name as CountryName,cities.Name as CityName  from countries,members,cities where members.IdCity=cities.id and countries.id=cities.IdCountry and members.id=".$rr->id);
			$rr->CountryName=$cc->CountryName ;
			array_push($TList, $rr);
		}

		// search in MembersTrads  
		$str = "select members.id as id,Username,memberstrads.Sentence as sresult,ProfileSummary from members,memberstrads where memberstrads.IdOwner=members.id and Status=\"Active\" and memberstrads.Sentence like '%" . addslashes(GetStrParam("searchtext")) . "%' order by Username";
		$qry = mysql_query($str);
		while ($rr = mysql_fetch_object($qry)) {
			$cc=LoadRow ("select countries.Name as CountryName,cities.Name as CityName  from countries,members,cities where members.IdCity=cities.id and countries.id=cities.IdCountry and members.id=".$rr->id);
			$rr->CountryName=$cc->CountryName ;
			array_push($TList, $rr);
		}
}

DisplayResults($TList, GetStrParam("searchtext")); // call the layout with all countries
?>
