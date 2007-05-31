<?php
require_once ("menus.php");
require_once ("profilepage_header.php");

function DisplayComments($m, $TCom) {
	global $title;
	$title = ww('ViewComments');
	require_once "header.php";

	Menu1(); // Displays the top menu
	Menu2("member.php?cid=".$m->Username); // even if in viewcomment we can be in the myprofile menu

	// Header of the profile page
	DisplayProfilePageHeader( $m );

	menumember("viewcomments.php?cid=" . $m->id, $m);

	ShowActions("<li><a href=\"addcomments.php?cid=" . $m->id . "\">". ww("addcomments"). "</a></li>");
	ShowAds(); // Show the Ads

	// middle column
	echo "    <div id=\"col3\"> \n"; 
	echo "      <div id=\"col3_content\" class=\"clearfix\"> \n"; 

	$iiMax = count($TCom);
	$tt = array ();
	$info_styles = array(0 => "        <div class=\"info\">\n", 1 => "        <div class=\"info highlight\">\n");
	for ($ii = 0; $ii < $iiMax; $ii++) {
		$color = "black";
		if ($TCom[$ii]->Quality == "Good") {
			$color = "#4e9a06";
		}
		if ($TCom[$ii]->Quality == "Bad") {
			$color = "#cc0000";
		}
		echo $info_styles[($ii%2)];
		echo "            <div class=\"subcolumns\">\n";
		echo "              <div class=\"c75l\">\n";
		echo "                <div class=\"subcl\">\n";
    // i used this image only for layout tests (matthias)		
		echo "                  <img src=\"./images/et.gif\" width=\"40\" class=\"framed float_left\" />\n";
		echo "                  <p><strong>", ww("CommentFrom", LinkWithUsername($TCom[$ii]->Commenter)), "</strong></p>\n";
 		echo "                  <p><em>", $TCom[$ii]->TextWhere, "</em></p>";
		echo "                  <p><font color=$color>", $TCom[$ii]->TextFree, "</font></p>";
		$tt = explode(",", $TCom[$ii]->Lenght);
		echo "                </div>\n"; // end subcl
		echo "              </div>\n"; // end c75l
		echo "              <div class=\"c25r\">\n";
		echo "              <div class=\"subcl\">\n";		
		echo "                <ul class=\"linklist\">\n";
		for ($jj = 0; $jj < count($tt); $jj++) {
			if ($tt[$jj]=="") continue; // Skip blank category comment : todo fix find the reason and fix this anomaly
			echo "                  <li>",$m->Username, " ", ww("Comment_" . $tt[$jj]), "</li>\n";
		}
    echo "                </ul>\n";
    echo "                <ul class=\"linklist\">\n";
		if (HasRight("Comments"))
			echo "                    <li><a href=\"admin/admincomments.php?action=editonecomment&IdComment=", $TCom[$ii]->id, "\">edit</a></li>\n";
		if ($m->id==$_SESSION["IdMember"]) echo "<li><a href=\"feedback.php?IdCategory=4\">",ww("ReportCommentProblem"),"</a></li>\n"; // propose owner of comment to report about the comment
		echo "                    </ul>\n";
    echo "                  </div>\n"; // end subcl
    echo "                </div>\n"; // end c25r
    echo "              </div>\n"; // end subcolumns
    echo "            </div>\n"; // end info
		//echo LinkWithPicture($TCom[$ii]->Commenter,$TCom[$ii]->photo);
	}
	
echo "              </div>\n"; 



	require_once "footer.php";
}
?>
