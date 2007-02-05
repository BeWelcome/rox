<?php
require_once ("Menus.php");

function DisplayCities($TList) {
	global $title;
	$title = ww('MembersByCities');
	include "header.php";

	Menu1("MembersByCities.php", ww('MembersByCities')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);

	DisplayHeaderWithColumns(ww('MembersByCities')); // Display the header

	echo "<ul>\n";

	$iiMax = count($TList);
	for ($ii = 0; $ii < $iiMax; $ii++) {
		echo "<li>";
		echo "<a href=countries.php?IdCountry=",$TList[$ii]->IdCountry,">",$TList[$ii]->CountryName, "</a> > ";
		echo "<a href=regions.php?IdRegion=",$TList[$ii]->IdRegion,">",$TList[$ii]->RegionName, "</a> > ";
		echo "<a href=regions.php?IdCity=",$TList[$ii]->IdCity,">",$TList[$ii]->CityName, "</a> > ";
		echo LinkWithUsername($TList[$ii]->Username);
		echo "</li>\n";
	}
	echo "</ul>\n";

	include "footer.php";
}
?>
