<?php
require_once ("menus.php");

function DisplayWhatisthis() {
	global $title;
	$title = ww('WhatisthisPage');
	require_once "header.php";

	mainmenu("whatisthis.php", ww('MainPage'));
	echo "<center><H1> ", ww('WhatisthisPage'), "</H1></center>\n";
	echo ww("Whatisthistext");
	echo "</center>\n";
	require_once "footer.php";
}
?>
