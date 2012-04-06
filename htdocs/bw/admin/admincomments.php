<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/
chdir("..") ;
require_once "lib/init.php";
require_once "layout/error.php";
require_once "layout/admincomments.php";

function loaddata($Status, $RestrictToIdMember = "") {

	global $AccepterCommentsScope;
	$TData = array ();

	if (($AccepterCommentsScope == "\"All\"") or ($AccepterCommentsScope == "All") or ($AccepterCommentsScope == "'All'")) {
		$InScope = "";
	} else {
	
		$InScope = "and countries.id in (" . $AccepterCommentsScope . ")";
	}

	$str = "select comments.*,msend.id as IdWriterMember,msend.Username as UsernameWriterMember,mrece.id as IdReceiverMember,mrece.Username as UsernameReceiverMember from members as msend,members as mrece,comments where comments.IdFromMember=msend.id and comments.IdToMember=mrece.id";
	if ($Status != "")
		$str .= " and AdminAction='" . $Status . "'";
	if ($RestrictToIdMember != "") {
		$str .= $RestrictToIdMember;
	}

	//	echo "str=$str\n";
	$qry = sql_query($str);
	while ($c = mysql_fetch_object($qry)) {
		array_push($TData, $c);
	}

	return ($TData);

} // end of load data

//------------------------------------------------------------------------------

MustLogIn(); // need to be log

$IdMember = GetParam("cid");

$countmatch = 0;

$RightLevel = HasRight('Comments'); // Check the rights
if ($RightLevel < 1) {
	echo "For this you need the <b>Comments</b> rights<br>";
	exit (0);
}

$AccepterScope = RightScope('Comments');
if ($AccepterScope != "All") {
	$CommentsScope = str_replace("\"", "'", $CommentsScope);
}

$RestrictToIdMember = "";
if (GetStrParam("ToIdMember") != "") {
	$RestrictToIdMember = " and IdToMember=" . IdMember(GetStrParam("ToIdMember"));
}
if (GetStrParam("FromIdMember") != "") {
	$RestrictToIdMember = " and IdFromMember=" . IdMember(GetStrParam("FromIdMember"));
}

$action = GetParam("action");
if ($action == "") {
	$action = "";
}
$lastaction = "";
switch ($action) {
    case "update" :
        $Message = " Updated comment #" . GetParam("IdComment");
        $c = LoadRow("select * from comments where id=" . GetParam("IdComment"));

        // Build string for length database field
        $lengthArray = array();
        foreach($_SYSHCVOL['LenghtComments'] as $checkbox) {
            if (GetParam("Comment_" . $checkbox)) {
                $lengthArray[] = $checkbox;
            }
        }
        $length = implode(",", $lengthArray);

        $quality = GetStrParam("Quality");
        $textWhere = GetStrParam("TextWhere");
        $textFree = GetStrParam("TextFree");
        $id = GetParam("IdComment");
        $str = "
            UPDATE
                comments
            SET
                Lenght='$length',
                Quality='$quality',
                TextWhere='$textWhere',
                TextFree='$textFree'
            WHERE
                id=$id
        ";
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

	case "del" :

		if (!(HasRight("Comments", "DeleteComment"))) {
		   $Message=" You haven't right for deleting comments" ;
		   DisplayAdminComments(loaddata("", " and comments.id=" . GetParam("IdComment")), $Message); // call the layout
		   exit (0);
		   break ;
		}
		$Message = " Delete comment #" . GetParam("IdComment");
		$c = LoadRow("select * from comments where id=" . GetParam("IdComment"));
		if (!isset($c->id)) {
		   $Message=" No such coment" ;
		   DisplayAdminComments(loaddata("", " and comments.id=" . GetParam("IdComment")), $Message); // call the layout
		   exit (0);
		   break ;
		}
		$str = "delete from comments  where id=" . GetParam("IdComment");
		sql_query($str);
		LogStr("Deleting comment #" . GetParam("IdComment") . " previous where=" . $c->TextWhere . " previous text=" . $c->TextFree . " previous Quality=" . $c->Quality, "AdminComment");
		DisplayAdminComments(loaddata("", " and comments.IdToMember=".$c->IdToMember ), $Message); // call the layout
		exit (0);
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
DisplayAdminComments(loaddata("AdminCommentMustCheck", $RestrictToIdMember), $Message); // call the layout
?>