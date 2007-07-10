<?php
require_once ("menus.php");

function DisplayDisclaimer() {
	global $title;
	$title = ww('DisclaimerPage');
	require_once "header.php";
	Menu1("disclaimer.php", ww('DisclaimerPage')); // Displays the top menu
	Menu2("aboutus.php", ww('GetAnswers')); // Displays the second menu

	echo "\n";
	echo "    <div id=\"main\">\n";
	echo "      <div id=\"teaser\">\n";
	echo "        <h1>", $title, " </h1>\n";
	echo "      </div>\n";

	menugetanswers("disclaimer.php" . $menutab, $title);

//	ShowLeftColumn($MenuAction)  ; // Show the Actions
	ShowAds(); // Show the Ads

	// Content with just two columns
	echo "\n";
	echo "      <div id=\"col3\" class=\"twocolumns\">\n";
	echo "        <div id=\"col3_content\" class=\"clearfix\">\n";

	echo "<div class=\"info\">\n";
	echo "<h3>", ww("DisclaimerInfo"),"</h3>";
	echo "<p>",ww("DisclaimerInfoText"),"</p>";
	echo "<h3>", ww("DisclaimerInfo2"),"</h3>";
	echo "<p>",ww("DisclaimerInfoText2"),"</p>";
	echo "</div>\n";	
	
	require_once "footer.php";
}
?>