<?php
include "lib/dbaccess.php";
require_once "lib/FunctionsLogin.php";
require_once "layout/error.php";
require_once "layout/viewcomments.php";

$IdMember = GetParam("cid", $_SESSION['IdMember']);
$photorank = 0; // Alway use picture 0 of view comment 

switch (GetParam("action")) {
	case "logout" :
		Logout("main.php");
		exit (0);
}

// Try to load the member
if (is_numeric($IdMember)) {
	$str = "select * from members where id=" . $IdMember . $wherestatus;
} else {
	$str = "select * from members where Username='" . $IdMember . "'" . $wherestatus;
}

$m = LoadRow($str);

if (!isset ($m->id)) {
	$errcode = "ErrorNoSuchMember";
	DisplayError(ww($errcode, $IdMember));
	//		die("ErrorMessage=".$ErrorMessage) ;
	exit (0);
}

$IdMember = $m->id; // to be sure to have a numeric ID

$profilewarning = "";
if ($m->Status != "Active") {
	$profilewarning = "WARNING the status of " . $m->Username . " is set to " . $m->Status;
}

// Load photo data
$photo = "";
$phototext = "";
$str = "select * from membersphotos where IdMember=" . $IdMember . " and SortOrder=" . $photorank;
$rr = LoadRow($str);
if (!isset ($rr->FilePath) and ($photorank > 0)) {
	$rr = LoadRow("select * from membersphotos where IdMember=" . $IdMember . " and SortOrder=0");
}
if (isset ($rr->FilePath)) {
	$photo = $rr->FilePath;
	$phototext = FindTrad($rr->Comment);
	$photorank = $rr->SortOrder;
}
$m->photo = $photo;
$m->photorank = $photorank;
$m->phototext = $phototext;

// Load geography
if ($m->IdCity > 0) {
	$rWhere = LoadRow("select cities.Name as cityname,regions.Name as regionname,countries.Name as countryname from cities,countries,regions where cities.IdRegion=regions.id and countries.id=regions.IdCountry and cities.id=" . $m->IdCity);
	$m->cityname = $rWhere->cityname;
	$m->regionname = $rWhere->regionname;
	$m->countryname = $rWhere->countryname;
}

// Load nbcomments nbtrust
$m->NbTrust = 0;
$m->NbComment = 0;
$rr = LoadRow("select count(*) as cnt from comments where IdToMember=" . $m->id . " and Quality='Good'");
if (isset ($rr->cnt))
	$m->NbTrust = $rr->cnt;
$rr = LoadRow("select count(*) as cnt from comments where IdToMember=" . $m->id);
if (isset ($rr->cnt))
	$m->NbComment = $rr->cnt;

if ($m->LastLogin == "11/30/99 00:00:00")
	$m->LastLogin = ww("NeverLog");
else
	$m->LastLogin = localdate($m->LastLogin);

// Load Age
$m->age = fage($m->BirthDate, $m->HideBirthDate);

// Load full name
$m->FullName = fFullName($m);

// Try to load the Comments, prepare the layout data
$rWho = LoadRow("select * from members where id=" . $IdMember);
$str = "select comments.*,members.Username as Commenter from comments,members where IdToMember=" . $IdMember . " and members.id=comments.IdFromMember";
$qry = mysql_query($str);
$TCom = array ();
while ($rWhile = mysql_fetch_object($qry)) {
	array_push($TCom, $rWhile);
}

DisplayComments($m, $TCom); // call the layout
?>