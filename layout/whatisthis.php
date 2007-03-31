<?php
require_once ("menus.php");

function DisplayWhatisthis() {
	global $title;
	$title = ww('WhatisthisPage');
	include "header.php";

	mainmenu("whatisthis.php", ww('MainPage'));
	echo "<center><H1> ", ww('WhatisthisPage'), "</H1></center>\n";
	echo ww("Whatisthistext");
	echo "</center>\n";
	include "footer.php";
}
?>
