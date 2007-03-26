<?php
require_once ("menus.php");
function DisplayComments($m, $TCom) {
	global $title;
	$title = ww('ViewComments');
	include "header.php";

	Menu1(); // Displays the top menu
	Menu2("member.php?cid=".$m->Username); // even if in viewcomment we can be in the myprofile menu

	// Header of the profile page
	require_once ("profilepage_header.php");

	echo "	<div id=\"columns\">";
	menumember("viewcomments.php?cid=" . $m->id, $m);
	echo "		<div id=\"columns-low\">";
	// MAIN begin 3-column-part
	echo "    <div id=\"main\">";
	ShowActions("<li><a href=\"addcomments.php?cid=" . $m->id . "\">". ww("addcomments"). "</a></li>");
	ShowAds(); // Show the Ads

	// middle column
	echo "      <div id=\"col3\"> \n"; 
	echo "	    <div id=\"col3_content\" class=\"clearfix\"> \n"; 
	echo "          <div id=\"content\"> \n";

	$iiMax = count($TCom);
	$tt = array ();
	$info_styles = array(0 => "<div class=\"info floatbox\">", 1 => "<div class=\"info highlight floatbox\">");
	for ($ii = 0; $ii < $iiMax; $ii++) {
		$color = "black";
		if ($TCom[$ii]->Quality == "Good") {
			$color = "#4e9a06";
		}
		if ($TCom[$ii]->Quality == "Bad") {
			$color = "#cc0000";
		}
		echo $info_styles[($ii%2)];
		echo "<table>\n";
		echo "<tr><td valign=center>";
		echo "<div class=\"comments_photo\">";		
		echo LinkWithPicture($TCom[$ii]->Commenter,$TCom[$ii]->photo);
		echo "</div>";		
		echo "</td>";
		echo "<td valign=center>";
		echo "<ul class=\"comments_text\">";
		echo "<li>";
		echo "<b>", ww("CommentFrom", LinkWithUsername($TCom[$ii]->Commenter)), "</b><br>";
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
			if ($tt[$jj]=="") continue; // Skip blank category comment : todo fix find the reason and fix this anomaly
			echo "&nbsp;&nbsp;&nbsp;<li>", ww("Comment_" . $tt[$jj]), "</li><br>";
		}

		if (HasRight("Comments"))
			echo " <a href=\"admin/admincomments.php?action=editonecomment&IdComment=", $TCom[$ii]->id, "\">edit</a>";
		echo " <a href=\"feedback.php?IdCategory=4\">",ww("ReportCommentProblem"),"</a>";
		echo "</ul>";
		echo "</td>";
		echo "</table>\n";
		echo "</div>"; // Closing the div form infostyle
	}
	
echo "              <div class=\"clear\"></div>\n"; 
echo "          </div>\n"; // end content
echo "        </div>\n"; // end col3_content

	// IE Column Clearing 
echo "        <div id=\"ie_clearing\">&nbsp;</div>\n"; 
	// End: IE Column Clearing 

echo "      </div>\n"; // end col3
	// End: MAIN 3-columns-part
	
echo "    </div>\n"; // end main


	include "footer.php";
}
?>
