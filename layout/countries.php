<?php
require_once ("menus.php");

function DisplayCountries($TList) {
	global $title;
	$title = ww('Countries');
	require_once "header.php";

	Menu1("countries.php", ww('Countries')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);

	DisplayHeaderWithColumns(ww('Countries')); // Display the header
	echo "          <div class=\"info\">\n";
	echo "            <ul class=\"floatbox\">\n";

	$iiMax = count($TList);
	for ($ii = 0; $ii < $iiMax; $ii++) {
		echo "              <li>";
		echo "<a href=regions.php?IdCountry=";
		echo $TList[$ii]->IdCountry, ">";
		echo $TList[$ii]->country;
		echo " (";
		echo $TList[$ii]->cnt, ")";
		echo "</a> ";
		echo "</li>\n";
	}
	echo "            </ul>\n";
	echo "          </div>\n";
  
	require_once "footer.php";
}
?>
