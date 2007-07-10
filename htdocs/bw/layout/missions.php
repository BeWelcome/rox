<?php
require_once ("menus.php");

function DisplayMissions() {
	global $title;
	$title = ww('MissionsPage');
	require_once "header.php";
	Menu1("missions.php", ww('MissionsPage')); // Displays the top menu
	Menu2("aboutus.php", ww('GetAnswers')); // Displays the second menu

	echo "\n";
	echo "    <div id=\"main\">\n";
	echo "      <div id=\"teaser\">\n";
	echo "        <h1>", $title, " </h1>\n";
	echo "      </div>\n";

	menugetanswers("missions.php" . $menutab, $title);

//	ShowLeftColumn($MenuAction)  ; // Show the Actions
	ShowAds(); // Show the Ads

	// Content with just two columns
	echo "\n";
	echo "      <div id=\"col3\" class=\"twocolumns\">\n";
	echo "        <div id=\"col3_content\" class=\"clearfix\">\n";

	echo "<div class=\"info\">\n";
	echo "<h3>", ww("OurMission"),"</h3>";
	echo "<q>",ww("OurMissionQuote"),"</q>";
	echo "<p>",ww("OurMissionText"),"</p>";
	echo "<h3>", ww("OurAim"),"</h3>";
	echo "<p>",ww("OurAimText"),"</p>";
	echo "</div>\n";	
	
	require_once "footer.php";
}
?>