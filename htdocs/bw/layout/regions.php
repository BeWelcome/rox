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
		echo "</a>";
//		echo " <a href=\"findpeople.php?IdRegion=",$TList[$ii]->IdRegion,"\">" ;
		echo "(",$TList[$ii]->cnt, ")" ;
//		echo "</a>";
		echo "</li>\n";
		echo "</a>";
		echo "</li>\n";
	}
	echo "            </ul>\n";
	echo "          </div>\n";		

	require_once "footer.php";
}
?>
