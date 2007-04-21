<?php
require_once ("menus.php");

// Display links
function DisplayLinks() {
	global $title;
	$title = ww('LinksPage');
	include "header.php";

	Menu1("", ww('LinksPage')); // Displays the top menu
	Menu2($_SERVER["PHP_SELF"]); // Displays the second menu

	DisplayHeaderWithColumns(ww("LinksPage")); // Display the header
	
	echo ww("LinksPageExplanation") ;	
	
	echo "<ul>\n";

	echo "<li>" ;
	echo "<a href=\"http://www.forum-voyages-vacances.com\" target=\"_blank\">Forum du voyage</a>" ;
	echo "</li>\n" ;

	echo "</ul>\n";

	include "footer.php";
} // end of DisplayLinks

?>
