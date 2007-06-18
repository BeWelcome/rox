<?php
require_once ("menus.php");

function DisplayCountries($TList,$where) {
	global $title;
	$title = ww('Cities');
	require_once "header.php";

	Menu1("cities.php", ww('Cities')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);

	DisplayHeaderWithColumns(ww('Cities')); // Display the header

  echo "<div class=\"info\">\n";
  echo "<p class=\"navlink\">\n";
	echo "<a href=\"countries.php\">",ww("countries"),"</a> > ";
	echo "<a href=\"regions.php?IdCountry=",$where->IdCountry,"\">",$where->CountryName,"</a> > ";
	echo "<a href=\"cities.php?IdRegion=",$where->IdRegion,"\">",$where->RegionName,"</a> > ";
  echo "</p>\n";	
	echo "<ul>\n";

	$iiMax = count($TList);
	for ($ii = 0; $ii < $iiMax; $ii++) {
		echo "<li>";
		echo $TList[$ii]->city, " <a href=\"findpeople.php?action=Find&IdCity=",$TList[$ii]->IdCity,"\">" ;
		echo "(",$TList[$ii]->cnt, ")" ;
		echo "</a>";
		echo "</li>\n";
	}
	echo "</ul>\n";
	echo "</div>\n";

	require_once "footer.php";
}
?>
