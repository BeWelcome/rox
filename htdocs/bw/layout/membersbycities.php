<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/


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
	echo "            <table class=\"memberlist\" border=\"0\" rules=\"rows\">\n";
	for ($ii = 0; $ii < $iiMax; $ii++) {
		$m = $TList[$ii];
	  $info_styles = array(0 => "        <tr class=\"blank\" align=left valign=center>", 1 => "<tr class=\"highlight\" align=left valign=center>");
		echo $info_styles[($ii%2)];
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
