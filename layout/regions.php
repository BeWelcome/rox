<?php
require_once ("menus.php");

function DisplayCountries($CountryName,$IdCountry,$TList) {
	global $title;
	$title = ww('Regions');
	require_once "header.php";

	Menu1("regions.php", ww('Regions')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);

	DisplayHeaderWithColumns(ww('Regions')); // Display the header

  echo "          <div class=\"info\">\n";
	echo "            <p class=\"navlink\">\n";
	echo "            <a href=countries.php>",ww("countries")," > ","<a href=regions.php?IdCountry=",$IdCountry,">",$CountryName,"</a></p>\n";
	echo "            <ul>\n";

	$iiMax = count($TList);
	for ($ii = 0; $ii < $iiMax; $ii++) {
		echo "              <li>";
		echo "<a href=cities.php?IdRegion=";
		echo $TList[$ii]->IdRegion, ">";
		echo $TList[$ii]->region;
		echo " (",$TList[$ii]->cnt, ")";
		echo "</a>";
		echo "</li>\n";
	}
	echo "            </ul>\n";
	echo "          </div>\n";		

	require_once "footer.php";
}
?>
