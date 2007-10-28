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
require_once "lib/init.php";
require_once "layout/error.php";

$TextWhere = GetStrParam("TextWhere");
$TextFree = GetStrParam("Commenter");
$Quality = GetStrParam("Quality");

$max = count($_SYSHCVOL['LenghtComments']);
$tt = $_SYSHCVOL['LenghtComments'];
$LenghtComments = "";
for ($ii = 0; $ii < $max; $ii++) {
	$var = $tt[$ii];
	if (isset ($_POST["Comment_" . $var])) {
		if ($LenghtComments != "")
			$LenghtComments = $LenghtComments . ",";
		$LenghtComments = $LenghtComments . $var;
	}
}

MustLogIn(); // member must login*

if (!CheckStatus("Active")) { // only Active member can send a Message
	 $errcode = "ErrorYouCantCommentWithYourCurrentStatus";
	 DisplayError(ww($errcode));
	 exit (0);
}

$IdMember = GetParam("cid", 0);
if ($IdMember==$_SESSION['IdMember']) {
	$errcode = "ErrorNoCommentOnYourSelf";
	DisplayError(ww($errcode, $IdMember));
	exit (0);
}
switch (GetParam("action")) {
	case "add" :
		$str = "select * from comments where IdToMember=" . $IdMember . " and IdFromMember=" . $_SESSION["IdMember"]; // if there is already a comment find it, we will be do an append
		$qry = sql_query($str);
		$TCom = mysql_fetch_object($qry);
		$newdate = "<font color=gray><font size=1>comment date " . date("F j, Y, g:i a") . " (UTC)</font></font><br />";

		$AdminAction = "NothingNeeded";
		if ($Quality == "Bad") {
			$AdminAction = "AdminCommentMustCheck";
		}
		if (!isset ($TCom->id)) {
			$TextWhere = $newdate . $TextWhere;
			$str = "insert into comments(IdToMember,IdFromMember,Lenght,Quality,TextWhere,TextFree,AdminAction,created) values (" . $IdMember . "," . $_SESSION['IdMember'] . ",'" . $LenghtComments . "','" . $Quality . "','" . $TextWhere . "','" . $TextFree . "','" . $AdminAction . "',now())";
			$qry = sql_query($str) or bw_error($str);
		    $TCom->id=mysql_insert_id() ;
		} else {
			$TextFree = $TCom->TextFree . "<hr />" . $newdate . $TextWhere . "<br />" . $TextFree;
			$str = "update comments set AdminAction='" . $AdminAction . "',IdToMember=" . $IdMember . ",IdFromMember=" . $_SESSION['IdMember'] . ",Lenght='" . $LenghtComments . "',Quality='" . $Quality . "',TextFree='" . $TextFree . "' where id=" . $TCom->id;
			$qry = sql_query($str) or bw_error($str);
		}

		$m = LoadRow("select * from members where id=" . $IdMember);
		$mCommenter = LoadRow("select Username from members where id=" . $_SESSION['IdMember']);

		$defLanguage = GetDefaultLanguage($IdMember);
		$subj = wwinlang("NewCommentSubjFrom", $defLanguage, $mCommenter->Username);
		$text = wwinlang("NewCommentTextFrom", $defLanguage, $mCommenter->Username, ww("CommentQuality_" . $Quality), GetStrParam("TextWhere"), GetStrParam("TextFree"));
		bw_mail(GetEmail($IdMember), $subj, $text, "", $_SYSHCVOL['CommentNotificationSenderMail'], $defLanguage, "html", "", "");

		if ($Quality == "Bad") {
// notify OTRS
			$subj = "Bad comment from  " .$mCommenter->Username.  " about " . fUsername($IdMember) ;
			$text = "Please check the comments. A bad comment was posted by " . $mCommenter->Username.  " about " . fUsername($IdMember) . "\n";
			$text .= $mCommenter->Username . "\n" . ww("CommentQuality_" . $Quality) . "\n" . GetStrParam("TextWhere") . "\n" . GetStrParam("TextFree");
			bw_mail($_SYSHCVOL['CommentNotificationSenderMail'], $subj, $text, "", $_SYSHCVOL['CommentNotificationSenderMail'], $defLanguage, "no", "", "");
		}

		LogStr("Adding a comment quality <b>" . $Quality . "</b> on " . $m->Username, "Comment");

		break;
}

// Try to load the Comments, prepare the layout data
// Try to load the member
if (is_numeric($IdMember)) {
	$str = "select * from members where id=" . $IdMember . " and Status='Active'";
} else {
	$str = "select * from members where Username='" . $IdMember . "' and Status='Active'";
}

$m = LoadRow($str);

if (!isset ($m->id)) {
	$errcode = "ErrorNoSuchMember";
	DisplayError(ww($errcode, $IdMember));
	exit (0);
}

$IdMember = $m->id; // to be sure to have a numeric ID

// Load previous comments of the same commenter if any	
$str = "select comments.*,members.Username as Commenter from comments,members where IdToMember=" . $IdMember . " and members.id=IdFromMember and members.id=" . $_SESSION["IdMember"];
$qry = sql_query($str);
$TCom = mysql_fetch_object($qry);

require_once "layout/addcomments.php";
DisplayAddComments($TCom, $m->Username, $IdMember); // call the layout
?>
