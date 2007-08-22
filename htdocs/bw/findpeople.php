<?php
require_once "lib/init.php";
require_once "lib/findpeople.php";
require_once "layout/findpeople.php";


// rebuild the group list
$str = "select SQL_CACHE * from groups";
$qry = sql_query($str);
$TGroup = array ();
while ($rr = mysql_fetch_object($qry)) {
	array_push($TGroup, $rr);
}

$TList=array() ;

switch (GetParam("action")) {


	case "" : // initial form displayed
		 DisplayFindPeopleForm($TGroup,$TList,-1) ;
		 break ;

	case "Find" : // Compute and Show the results
	case ww("FindPeopleSubmit") : // Compute and Show the results
		 $TList=buildresult() ;
		 DisplayFindPeopleForm($TGroup,$TList,$rCount->cnt) ;
		 break ;
}

?>
