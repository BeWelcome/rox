<?php
require_once ("Menus.php");

function DisplayCountries($TList) {
	global $title;
	$title = ww('Cities');
	include "header.php";

	Menu1("cities.php", ww('Cities')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);

	DisplayHeaderWithColumns(ww('Cities')); // Display the header

	echo "<ul>\n";

	$iiMax = count($TList);
	for ($ii = 0; $ii < $iiMax; $ii++) {
		echo "<li>";
		echo $TList[$ii]->city," <a href=membersbycities.php?IdCity=";
		echo $TList[$ii]->id, ">";
		echo  "(";
		echo $TList[$ii]->cnt, ")";
		echo "</a>";
		echo "</li>\n";
	}
	echo "</ul>\n";

	include "footer.php";
}
?>
