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

function DisplayCountries($TList) {
	global $title;
	$title = ww('MembersByCountries');
	require_once "header.php";

	Menu1("membersbycountries.php", ww('MembersByCountries')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);

	DisplayHeaderWithColumns(ww('MembersByCountries')); // Display the header

	echo "<ul>\n";

	$iiMax = count($TList);
	for ($ii = 0; $ii < $iiMax; $ii++) {
		echo "<li>";
		echo $TList[$ii]->CountryName, ">";
		echo $TList[$ii]->RegionName, ">";
		echo $TList[$ii]->CityName, " ";
		echo LinkWithUsername($TList[$ii]->Username);
		echo "</li>\n";
	}
	echo "</ul>\n";

	require_once "footer.php";
}
?>
