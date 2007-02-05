<?php
require_once "lib/init.php";
require_once "layout/error.php";
require_once "layout/admincomments.php";

function loaddata($Status, $RestrictToIdMember = "") {

	global $AccepterCommentsScope;
	$TData = array ();

	if (($AccepterCommentsScope == "\"All\"") or ($AccepterCommentsScope == "All") or ($AccepterCommentsScope == "'All'")) {
		$InScope = "";
	} else {
		$InScope = "and countries.id in (" . $AccepterScope . ")";
	}

	$str = "select comments.*,msend.id as IdWriterMember,msend.Username as UsernameWriterMember,mrece.id as IdReceiverMember,mrece.Username as UsernameReceiverMember from members as msend,members as mrece,comments where comments.IdFromMember=msend.id and comments.IdToMember=mrece.id";
	if ($Status != "")
		$str .= " and AdminAction='" . $Status . "'";
	if ($RestrictToIdMember != "") {
		$str .= $RestrictToIdMember;
	}

	//	echo "str=$str\n" ;
	$qry = sql_query($str);
	while ($c = mysql_fetch_object($qry)) {
		array_push($TData, $c);
	}

	return ($TData);

} // end of load data

//------------------------------------------------------------------------------

MustLog(); // need to be log

$IdMember = GetParam("cid");

$countmatch = 0;

$RightLevel = HasRight('Comments'); // Check the rights
if ($RightLevel < 1) {
	echo "This Need the sufficient <b>Comments</b> rights<br>";
	exit (0);
}

$AccepterScope = RightScope('Comments');
if ($AccepterScope != "All") {
	$CommentsScope = str_replace("\"", "'", $CommentsScope);
}

$RestrictToIdMember = "";
if (GetParam("ToIdMember") != "") {
	$RestrictToIdMember = " and ToIdMember=" . IdMember(GetParam("ToIdMember"));
}
if (GetParam("FromIdMember") != "") {
	$RestrictToIdMember = " and FromIdMember=" . IdMember(GetParam("FromIdMember"));
}

$action = GetParam("action");
if ($action == "") {
	$action = "";
}
$lastaction = "";
switch ($action) {
	case "update" :

		// todo :  proceed with length of stay and trust

		$Message = " Updated comment #" . GetParam("IdComment");
		$c = LoadRow("select * from comments where id=" . GetParam("IdComment"));
		$str = "update comments set Quality='" . GetParam("Quality") . "',TextWhere='" . GetParam("TextWhere") . "',TextFree='" . GetParam("TextFree") . "' where id=" . GetParam("IdComment");
		sql_query($str);
		LogStr("Updating comment #" . GetParam("IdComment") . " previous where=" . $c->TextWhere . " previous text=" . $c->TextFree . " previous Quality=" . $c->Quality, "AdminComment");
		DisplayAdminComments(loaddata("", " and comments.id=" . GetParam("IdComment")), $Message); // call the layout
		exit (0);
		break;

	case "AdminAbuserMustCheck" :
		$Message = " Set comment to to be check by Admin Comment";
		$str = "Update comments set AdminAction='AdminAbuserMustCheck' where id=" . Getparam("IdComment");
		sql_query($str);
		LogStr(" Setting to <b>tobe check by Admin Abuser</b> for IdComment #" . Getparam("IdComment"), "AdminComment");
		;
		break;
	case "AdminCommentMustCheck" :
		$Message = " Set comment to to be check by Admin Comment";
		$str = "Update comments set AdminAction='AdminCommentMustCheck' where id=" . Getparam("IdComment");
		sql_query($str);
		LogStr(" Setting to <b>tobe check by Admin Comment</b> for IdComment #" . Getparam("IdComment"), "AdminComment");
		;
		break;
	case "Checked" :
		$Message = " Set comment to to be check by Admin Comment";
		$str = "Update comments set AdminAction='Checked' where id=" . Getparam("IdComment");
		sql_query($str);
		LogStr(" Setting to <b>Checked</b> for IdComment #" . Getparam("IdComment"), "AdminComment");
		;
		break;
	case "editonecomment" :
		$Message = " Editing one comment";
		DisplayAdminComments(loaddata("", " and comments.id=" . GetParam("IdComment")), $Message); // call the layout
		exit (0);
		break;
	case "AdminAbuser" :
		$Message = " Comments needed to be checked by Admin Abuser";
		DisplayAdminComments(loaddata("AdminAbuser", $RestrictToIdMember), $Message); // call the layout
		exit (0);
		break;
	case "All" :
		$Message = " All Comments ";
		DisplayAdminComments(loaddata("", $RestrictToIdMember), $Message); // call the layout
		exit (0);
		break;

	case "ShowOneMember" :
		$RestrictToIdMember = IdMember(GetParam("cid", 0));
		break;
}

$Message = " Comments needed to be checked by AdminComment";
DisplayAdminComments(loaddata("AdminComment", $RestrictToIdMember), $Message); // call the layout
?>