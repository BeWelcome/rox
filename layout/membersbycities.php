<?php
require_once ("menus.php");

function DisplayCities($TList,$where) {
	global $title;
	$title = ww('MembersByCities');
	require_once "header.php";

	Menu1("MembersByCities.php", ww('MembersByCities')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);

	DisplayHeaderWithColumns(ww('MembersByCities')); // Display the header

  echo "          <div class=\"info\">\n";
  echo "            <p class=\"navlink\">";
	echo "<a href=\"countries.php\">",ww("countries"),"</a> > ";
	echo "<a href=\"regions.php?IdCountry=",$where->IdCountry,"\">",$where->CountryName,"</a> > ";
	echo "<a href=\"cities.php?IdRegion=",$where->IdRegion,"\">",$where->RegionName,"</a> > ";
	echo "<a href=\"membersbycities.php?IdCity=",$where->IdCity,"\">",$where->CityName,"</a><br>";
  echo "</p>\n";

	$iiMax = count($TList);
	echo "            <table border=\"1\" rules=\"rows\">\n";
	for ($ii = 0; $ii < $iiMax; $ii++) {
		$m = $TList[$ii];
		echo "              <tr align=left>\n";
		echo "                <td valign=center align=center>\n";
		if (($m->photo != "") and ($m->photo != "NULL")) {
            echo LinkWithPicture($m->Username,$m->photo);
		}
		echo "</td>\n";
		echo "                <td valign=center>", LinkWithUsername($m->Username), "</td>\n";
		echo "                <td valign=center>", $m->countryname, "</td>\n";
		echo "                <td valign=center>\n";
		echo $m->ProfileSummary;

		echo "</td>\n";
		echo "              </tr>\n";
	}
	echo "              </table>\n";
  echo "            </div>\n";

	require_once "footer.php";
}
?>
