<?php
require_once ("menus.php");

function DisplaySearch() {
	global $title;
	$title = ww('SearchPage');
	require_once "header.php";

	mainmenu("search.php", ww('MainPage'));
	echo "<center><H1> page under construction</H1></center>\n";
	echo "</center>\n";
	require_once "footer.php";
}
?>
