<?php
require_once ("menus.php");
function DisplayAddComments($TCom, $Username, $IdMember) {
	global $title;
	global $_SYSHCVOL;
	$title = ww('AddComments');

	include "header.php";


	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("addcomments.php.php", ww('AddComments')); // Displays the second menu

	DisplayHeaderWithColumns(ww('commentsfor', $Username)); // Display the header

	echo "\n<center>\n";

	// Display the previous comment if any
	$ttLenght = array ();
	if (isset ($TCom->Quality)) { // if there allready a comment display it
		echo "<table valign=center style=\"font-size:12;\">";
		echo "<tr><th colspan=3>", LinkWithUsername($Username), "</th>";
		$color = "black";
		if ($TCom->Quality == "Good") {
			$color = "#808000";
		}
		if ($TCom->Quality == "Bad") {
			$color = "red";
		}
		echo "<tr><td>";
		echo "<b>", $TCom->Commenter, "</b><br>";
		echo "<i>", $TCom->TextWhere, "</i>";
		echo "<br><font color=$color>", $TCom->TextFree, "</font>";
		echo "</td>";
		$ttLenght = explode(",", $TCom->Lenght);
		echo "<td width=\"30%\">";
		for ($jj = 0; $jj < count($ttLenght); $jj++) {
			if ($ttLenght[$jj]=="") continue ; // Skip blank category comment : todo fix find the reason and fix this anomaly
			echo ww("Comment_" . $ttLenght[$jj]), "<br>";
		}

		echo "</td>";
		echo "</table>\n";
	}

	// Display the form to propose to add a comment	
//	echo "<br><br><form method=\"post\" name=\"addcomment\" OnSubmit=\"return(VerifSubmit());\">\n";
	echo "<br><br><form method=\"post\" name=\"addcomment\" OnSubmit=\"return DoVerifSubmit('addcomment');\">\n";
	echo "<table valign=center style=\"font-size:12;\">";
	echo "<tr><td>", ww("CommentQuality"),"<br>",ww("RuleForNeverMetComment"),"</td><td>";

	echo "<select name=Quality>\n";
	echo "<option value=\"Neutral\" selected >"; // by default
	echo ww("CommentQuality_Neutral"), "</option>\n";

	echo "<option value=\"Good\"";
	if ($TCom->Quality == "Good")
		echo " selected ";
	echo ">", ww("CommentQuality_Good"), "</option>\n";

	echo "<option value=\"Bad\"";
	if ($TCom->Quality == "Bad")
		echo " selected ";
	echo ">", ww("CommentQuality_Bad"), "</option>\n";
	echo "</selected>";
	echo "</td>";
	echo "<td>", ww("CommentQualityDescription", $Username, $Username, $Username), "</td>";

	$tt = $_SYSHCVOL['LenghtComments'];
	$max = count($tt);
	echo "<tr><td>", ww("CommentLength"), "</td><td>";
	echo "<table valign=center style=\"font-size:12;\">";
	for ($ii = 0; $ii < $max; $ii++) {
		echo "<tr><td>", ww("Comment_" . $tt[$ii]), "</td>";
		echo "<td><input type=checkbox name=\"Comment_" . $tt[$ii] . "\"";
		if (in_array($tt[$ii], $ttLenght))
			echo " checked ";
		echo ">\n</td>\n";

	}
	echo "</table></td>";

	echo "<td>", ww("CommentLengthDescription", $Username, $Username, $Username), "</td>";
	echo "<tr><td colspan=3></td>";
	echo "<tr><td>", ww("CommentsWhere"), "</td><td><textarea name=TextWhere cols=40 rows=3></textarea></td><td>", ww("CommentsWhereDescription", $Username), "</td>";
	echo "<tr><td>", ww("CommentsCommenter"), "</td><td><textarea name=Commenter cols=40 rows=8></textarea></td><td>", ww("CommentsCommenterDescription", $Username), "</td>";

	echo "<tr><td align=center colspan=3><input type=hidden value=" . $IdMember . " name=cid>" ;
	echo "<input type=hidden name=action value=add>" ;
 	echo "<input type=submit name=valide value=submit ></td>";

	echo "\n</table>";
	echo "\n</form>\n";

	echo "<SCRIPT  TYPE=\"text/javascript\">\n" ;
	echo "function DoVerifSubmit(nameform) {\n" ;
	echo "nevermet=document.forms[nameform].elements['Comment_NeverMetInRealLife'].checked ;\n" ;
echo "	if ((document.forms[nameform].elements['Quality'].value!='Negative') && (nevermet)) {\n" ;
echo "	   alert('",addslashes(ww("RuleForNeverMetComment")),"') ;\n" ;
echo "	   return (false) ;\n" ;
echo "	}\n" ;
echo "	return(true) ;\n" ;
	echo "}\n" ;
	echo "</SCRIPT>\n" ;


	echo "</center>\n";

	include "footer.php";
}
?>
