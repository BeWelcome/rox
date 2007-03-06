<?php
require_once "lib/init.php";
require_once "layout/error.php";
require_once "layout/viewcomments.php";
require_once "prepare_profile_header.php";

$IdMember = GetParam("cid", $_SESSION['IdMember']);
$photorank = 0; // Alway use picture 0 of view comment 

switch (GetParam("action")) {
}

if (!IsPublic($IdMember))
	MustLogIn();

$m = prepare_profile_header($IdMember,$wherestatus) ; 

// Try to load the Comments, prepare the layout data
$rWho = LoadRow("select * from members where id=" . $IdMember);
$str = "select comments.*,members.Username as Commenter from comments,members where IdToMember=" . $IdMember . " and members.id=comments.IdFromMember";
$qry = mysql_query($str);
$TCom = array ();
while ($rr = mysql_fetch_object($qry)) {
	$photo=LoadRow("select SQL_CACHE * from membersphotos where IdMember=" . $rr->IdFromMember . " and SortOrder=0");
	if (isset($photo->FilePath)) $rr->photo=$photo->FilePath ; 
	array_push($TCom, $rr);
}

DisplayComments($m, $TCom); // call the layout
?>