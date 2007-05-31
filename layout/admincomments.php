<?php
require_once ("menus.php");

function ShowList($TData) {
	global $_SYSHCVOL;
	$maxTData = count($TData);
	$count = 0;
	$tt = $_SYSHCVOL['LenghtComments'];
	$max = count($tt);
	echo "<div class=\"info\">\n";
	echo "\n<table width=\"95%\">\n";
	for ($iData = 0; $iData < $maxTData; $iData++) {
		$c = $TData[$iData];
		$count++;
		echo "<tr><th colspan=2><div style=\"font-size:20px;\"> comment from ", LinkWithUsername($c->UsernameWriterMember), " to ", LinkWithUsername($c->UsernameReceiverMember), "</div>  <b>", $c->AdminAction, "</b></th>\n";

		echo "<tr><td colspan=2";

		echo "\n<form method=post action=admincomments.php>\n";
		echo "<table valign=center style=\"font-size:12;\">\n";
		$ttLenght = explode(",", $c->Lenght);
		echo "<tr><td>";
		echo "  <table valign=center style=\"font-size:12;\">\n";
		for ($ii = 0; $ii < $max; $ii++) {
			echo "  <tr><td>", ww("Comment_" . $tt[$ii]), "</td>";
			echo "<td><input type=checkbox name=\"Comment_" . $tt[$ii] . "\"";
			if (in_array($tt[$ii], $ttLenght))
				echo " checked ";
			echo "></td>\n";

		}
		echo "  </table>\n";
		echo "</td>";

		echo "<td>";
		if ($c->AdminComment != "Checked")
			echo "<a href=\"".bwlink("admin/admincomments.php?IdComment=". $c->id. "&action=Checked")."\">Checked</a><br><br>\n";
		if (($c->AdminComment != "Checked") and (HasRight("Comments", "AdminComment")))
			echo "<a href=\"".bwlink("admin/admincomments.php?IdComment=". $c->id. "&action=AdminCommentMustCheck")."\">Admin Comment Must Check</a><br><br>\n";
		if (($c->AdminComment != "Checked") and (HasRight("Comments", "AdminAbuser")))
			echo "<a href=\"".bwlink("admin/admincomments.php?IdComment=". $c->id. "&action=AdminAbuserMustCheck")."\">Admin Abuser Must Check</a><br><br>\n";
		if (($c->AdminComment != "Checked") and (HasRight("Comments", "DeleteComment")))
			echo "<a href=\"".bwlink("admin/admincomments.php?IdComment=". $c->id. "&action=del\" onclick=\"return('Confirm delete ?');")."\">del</a><br><br>\n";
		echo "<a href=\"".bwlink("admin/admincomments.php?FromIdMember=" . $c->UsernameWriterMember )."&action=All\">Other comments written by ", $c->UsernameWriterMember, "</a><br><br>\n";
		echo "<a href=\"".bwlink("admin/admincomments.php?ToIdMember=" . $c->UsernameReceiverMember )."&action=All\">Other comments written about ", $c->UsernameReceiverMember, "</a><br><br>\n";
		echo "<a href=\"".bwlink("contactmember.php?cid=". $c->IdWriterMember)."\">contact writer (". $c->UsernameWriterMember.")</a><br><br>";
		echo "<a href=\"".bwlink("contactmember.php?cid=". $c->IdReceiverMember)."\">contact receiver (". $c->UsernameReceiverMember.")</a>\n";
		echo "</td>\n";

		echo "<tr><td colspan=1>where<br><textarea name=TextWhere cols=70 rows=3>", $c->TextWhere, "</textarea></td>\n";

		$QualityStyle = "background-color:lightgreen;";
		if ($c->Quality == "Bad") {
			$QualityStyle = "background-color:black;color:white;";
		}
		if ($c->Quality == "Neutral") {
			$QualityStyle = "background-color:lightgray;";
		}
		echo "<td rowspan=2 valign=center>Quality <select name=Quality style=\"", $QualityStyle, "\">\n";
		echo "<option value=\"Neutral\" ";
		if ($c->Quality == "Neutral")
			echo "selected";
		echo ">";
		echo ww("CommentQuality_Neutral"), "</option>\n";

		echo "<option value=\"Good\"";
		if ($c->Quality == "Good")
			echo " selected ";
		echo ">", ww("CommentQuality_Good"), "</option>\n";

		echo "<option value=\"Bad\"";
		if ($c->Quality == "Bad")
			echo " selected ";
		echo ">", ww("CommentQuality_Bad"), "</option>\n";
		echo "</selected>\n";
		echo "</td>\n";

		echo "<tr><td colspan=2>comment:<br><textarea name=TextFree cols=70 rows=8>", $c->TextFree, "</textarea></td>\n";

		echo "<tr><td align=center colspan=2><input type=hidden value=" . $c->id . " name=IdComment><input type=hidden value=" . $IdMember . " name=cid><input type=hidden name=action value=update><input type=submit value=update></td>\n";

		echo "\n</table>";
		echo "\n</form>";

		echo "</td>";
		echo "<tr><td colspan=2><hr></td>\n";
	}
	echo "<tr><td align=left >Total</td><td align=left>$count</td>";
	echo "\n</table><br>\n";
} // end of ShowList

function DisplayAdminComments($TData, $lastaction = "") {
	global $countmatch;
	global $title;
	$title = "Admin Comments";
	global $AdminCommentsScope;

	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("admincomments.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderShortUserContent($title . " : " . $lastaction);
	echo " your Scope :", $AdminCommentsScope;
	if (HasRight("Comments", "AdminAbuser"))
		echo " <a href=\"".bwlink("admin/admincomments.php?action=AdminAbuser")."\">Comments to check by Admin Abuser</a>";
	echo " <a href=\"".bwlink("admin/admincomments.php?action=All")."\">All Comments </a>";

	echo "<center>";
	ShowList($TData);
	echo "</center>";
	echo "</div>\n";

	require_once "footer.php";
} // end of DisplayAdminAccepter($Taccepted,$Tmailchecking,$Tpending)