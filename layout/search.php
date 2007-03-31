<?php
require_once ("menus.php");

function DisplaySearch() {
	global $title;
	$title = ww('SearchPage');
	include "header.php";

	mainmenu("search.php", ww('MainPage'));
	echo "<center><H1> page under construction</H1></center>\n";
	echo "</center>\n";
	include "footer.php";
}
?>
