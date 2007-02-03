<?php
include "lib/dbaccess.php";
require_once "layout/error.php";
require_once "layout/viewcomments.php";
require_once "prepare_profile_header.php";

$IdMember = GetParam("cid", $_SESSION['IdMember']);
$photorank = 0; // Alway use picture 0 of view comment 

switch (GetParam("action")) {
}

$m = prepare_profile_header($IdMember,$wherestatus) ; 

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