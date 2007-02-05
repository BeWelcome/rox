<?php
require_once ("Menus.php");

function DisplayCountries($TList) {
	global $title;
	$title = ww('Regions');
	include "header.php";

	Menu1("regions.php", ww('Regions')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);

	DisplayHeaderWithColumns(ww('Regions')); // Display the header

	echo "<ul>\n";

	$iiMax = count($TList);
	for ($ii = 0; $ii < $iiMax; $ii++) {
		echo "<li>";
		echo "<a href=cities.php?cityId=";
		echo $TList[$ii]->id, ">";
		echo $TList[$ii]->region;
		echo "</a>";
		echo " (";
		echo $TList[$ii]->cnt, ")";
		echo "</a>";
		echo "</li>\n";
	}
	echo "</ul>\n";

	include "footer.php";
}
?>
