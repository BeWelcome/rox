<?php
require_once ("Menus.php");
function DisplayComments($m, $TCom) {
	global $title;
	$title = ww('ViewComments');
	include "header.php";

	Menu1(); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);
	// Header of the profile page
	require_once ("profilepage_header.php");

	echo "	<div id=\"columns\">";
	menumember("viewcomments.php?cid=" . $m->id, $m->id, $m->NbComment);
	echo "		<div id=\"columns-low\">";
	// MAIN begin 3-column-part
	echo "    <div id=\"main\">";
	ShowActions("<li><a href=\"addcomments.php?cid=" . $m->id . "\">", ww("addcomments"), "</a></li>");
	ShowAds(); // Show the Ads

	// middle column
	echo "      <div id=\"col3\"> \n"; 
	echo "	    <div id=\"col3_content\" class=\"clearfix\"> \n"; 
	echo "          <div id=\"content\"> \n";

	$iiMax = count($TCom);
	$tt = array ();
	for ($ii = 0; $ii < $iiMax; $ii++) {
		$color = "black";
		if ($TCom[$ii]->Quality == "Good") {
			$color = "#808000";
		}
		if ($TCom[$ii]->Quality == "Bad") {
			$color = "red";
		}
		echo "	<div class=\"info floatbox\">";
		echo "<table>\n";
		echo "<tr><td valign=center>";
		echo "<ul class=\"comments_text\">";
		echo "<li>";
		echo "<b>", ww("CommentFrom", $TCom[$ii]->Commenter), "</b><br>";
		echo "<li>";
		echo "</li>";
		echo "<i>", $TCom[$ii]->TextWhere, "</i>";
		echo "<br><font color=$color>", $TCom[$ii]->TextFree, "</font>";
		echo "</li>";
		echo "</ul>";
		echo "</td>";
		$tt = explode(",", $TCom[$ii]->Lenght);
		echo "<td>";
		echo "<ul class=\"comments_tags\">";
		for ($jj = 0; $jj < count($tt); $jj++) {
			echo "&nbsp;&nbsp;&nbsp;<li>", ww("Comment_" . $tt[$jj]), "</li><br>";
		}

		if (HasRight("Comments"))
			echo " <a href=\"admincomments.php?action=editonecomment&IdComment=", $TCom[$ii]->id, "\">edit</a>";
		echo "</ul>";
		echo "</td>";
	}
	echo "</table>\n";
	echo "</div>";
	echo "	</div>";
	echo "				</div>";
	echo "				<div class=\"clear\" />";
	echo "			</div>	";
	echo "			<div class=\"clear\" />	";
	echo "		</div>	";
	echo "		</div>	";
	echo "	</div>	";

	include "footer.php";
}
?>
