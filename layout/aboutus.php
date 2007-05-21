<?php
require_once ("menus.php");

function DisplayAboutUs() {
	global $title;
	$title = ww('AboutUsPage')." - ".ww('HospitalityExchange');
	include "header.php";
	Menu1("aboutus.php", ww('AboutUsPage')); // Displays the top menu
	Menu2($_SERVER["PHP_SELF"]); // Displays the second menu

	DisplayHeaderWithColumns(ww("AboutUsPage")); // Display the header

	echo "<center><H1> ", ww('AboutUsPage'), "</H1></center>\n";
	echo ww("AboutUsText");
	echo "</center>\n";
	include "footer.php";
}
?>
