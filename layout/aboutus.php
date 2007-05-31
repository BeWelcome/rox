<?php
require_once ("menus.php");

function DisplayAboutUs() {
	global $title;
	$title = ww('AboutUsPage');
	require_once "header.php";
	Menu1("aboutus.php", ww('AboutUsPage')); // Displays the top menu
	Menu2($_SERVER["PHP_SELF"]); // Displays the second menu

	DisplayHeaderWithColumns(ww("AboutUsPage")); // Display the header
  
  echo "<div class=\"info\">\n";
	echo "<h1> ", ww('AboutUsPage'), "</h1>\n";
	echo ww("AboutUsText");
	echo "<\n";
	echo "</div>\n";
	require_once "footer.php";
}
?>
