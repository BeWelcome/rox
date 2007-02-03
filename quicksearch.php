<?php
include "lib/dbaccess.php";

$TList = array ();

switch (GetParam("action")) {

	case "quicksearch" :
		// prepare the result list (build the $TList array

		// search for username 
		$str = "select Username,ProfileSummary from members where Status=\"Active\" and Username like '%" . addslashes(GetParam("searchtext")) . "%'";
		$qry = mysql_query($str);
		while ($rr = mysql_fetch_object($qry)) {
			array_push($TList, $rr);
		}
		// search for organizations 
		$str = "select Username,Organizations as result,ProfileSummary  from members where Status=\"Active\" and Organizations like '%" . addslashes(GetParam("searchtext")) . "%'";
		$qry = mysql_query($str);
		while ($rr = mysql_fetch_object($qry)) {
			array_push($TList, $rr);
		}

		// search in MembersTrads  
		$str = "select Username,memberstrads.Sentence as sresult,ProfileSummary from members,memberstrads where memberstrads.IdOwner=members.id and Status=\"Active\" and memberstrads.Sentence like '%" . addslashes(GetParam("searchtext")) . "%'";
		$qry = mysql_query($str);
		while ($rr = mysql_fetch_object($qry)) {
			array_push($TList, $rr);
		}

}

require_once "layout/quicksearch.php";
DisplayResults($TList, GetParam("searchtext")); // call the layout with all countries
?>
