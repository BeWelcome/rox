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
	$title = ww('Countries');
	require_once "header.php";

	Menu1("countries.php", ww('Countries')); // Displays the top menu

	Menu2("findpeople.php", ww('findpeoplePage')); // Displays the second menu

	echo "\n";
	echo "    <div id=\"main\">\n";
	echo "      <div id=\"teaser_bg\">\n";
	echo "      <div id=\"teaser\">\n";
	echo "        <h1>", $title, " </h1>\n";
	echo "      </div>\n";
	
	menufindmembers("countries.php" . $menutab, $title);
	echo "      </div>\n";
	ShowLeftColumn($ActionList,VolMenu())  ; // Show the Actions
	ShowAds(); // Show the Ads
	
	// middle column
	echo "\n";
	echo "      <div id=\"col3\"> \n"; 
	echo "        <div id=\"col3_content\" class=\"clearfix\"> \n"; 

	echo "          <div class=\"info\">\n";
	echo "            <ul class=\"floatbox\">\n";

	echo "<ul>\n";

	$iiMax = count($TList);
	for ($ii = 0; $ii < $iiMax; $ii++) {
		echo "              <li>";
		echo "<a href=regions.php?IdCountry=";
		echo $TList[$ii]->IdCountry, ">";
		echo $TList[$ii]->country;
		echo "</a> ";
		echo " <a href=\"findpeople.php?action=Find&IdCountry=",$TList[$ii]->IdCountry,"\">(";
		echo $TList[$ii]->cnt, ")</a>";
		echo "</li>\n";
	}
	echo "            </ul>\n";
	echo "          </div>\n";
  
	require_once "footer.php";
}
?>
