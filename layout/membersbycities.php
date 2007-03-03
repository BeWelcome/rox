<?php
require_once ("menus.php");

function DisplayCities($TList,$where) {
	global $title;
	$title = ww('MembersByCities');
	include "header.php";

	Menu1("MembersByCities.php", ww('MembersByCities')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);

	DisplayHeaderWithColumns(ww('MembersByCities')); // Display the header

	echo "<a href=\"countries.php\">",ww("countries"),"</a> > " ;
	echo "<a href=\"regions.php?IdCountry=",$where->IdCountry,"\">",$where->CountryName,"</a> > " ;
	echo "<a href=\"cities.php?IdRegion=",$where->IdRegion,"\">",$where->RegionName,"</a> > " ;
	echo "<a href=\"membersbycities.php?IdCity=",$where->IdCity,"\">",$where->CityName,"</a><br>" ;

	$iiMax = count($TList);
	echo "<table border=\"1\" rules=\"rows\">";
	for ($ii = 0; $ii < $iiMax; $ii++) {
		$m = $TList[$ii];
		echo "<tr align=left>";
		echo "<td valign=center align=center>";
		if (($m->photo != "") and ($m->photo != "NULL")) {
			echo "<div id=\"topcontent-profile-photo\">\n";
            echo LinkWithPicture($m->Username,$m->photo) ;
			echo "<br>" ;
			echo "</div>";
		}
		echo "</td>";
		echo "<td valign=center>", LinkWithUsername($m->Username), "</td>";
		echo " <td valign=center>", $m->countryname, "</td> ";
		echo "<td valign=center>";
		echo $m->ProfileSummary;

		echo "</td>";
		echo "</tr>";
	}
	echo "</table>";


	include "footer.php";
}
?>
