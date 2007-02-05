<?php
require_once ("Menus.php");

function DisplayCountries($TList) {
	global $title;
	$title = ww('Countries');
	include "header.php";

	Menu1("countries.php", ww('Countries')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);

	DisplayHeaderWithColumns(ww('Countries')); // Display the header

	echo "<ul>\n";

	$iiMax = count($TList);
	for ($ii = 0; $ii < $iiMax; $ii++) {
		echo "<li>";
		echo "<a href=regions.php?IdCountry=";
		echo $TList[$ii]->IdCountry, ">";
		echo $TList[$ii]->country;
		echo "</a> ";
		echo "(";
		echo $TList[$ii]->cnt, ")";
		echo "</li>\n";
	}
	echo "</ul>\n";

	include "footer.php";
}
?>
