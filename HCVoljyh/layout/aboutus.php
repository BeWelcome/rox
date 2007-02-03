<?php
require_once ("Menus.php");

function DisplayAboutUs() {
	global $title;
	$title = ww('AboutUsPage');
	include "header.php";

	mainmenu("aboutus.php", ww('AboutUsPage'));
	echo "<center><H1> ", ww('AboutUsPage'), "</H1></center>\n";
	echo ww("AboutUsText");
	echo "</center>\n";
	include "footer.php";
}
?>
