<?php

/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

Foobar is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/


require_once ("menus.php");

function DisplayResponsibles($TData) {
	global $title;
	$title = ww('ResponsiblesPage' . " " . $_POST['Username']);
	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("members.php", ww('ResponsiblesPage')); // Displays the second menu

	DisplayHeaderWithColumns(); // Display the header

	$iiMax = count($TData);
	echo "\n<table border=\"1\" rules=\"rows\">\n";
	for ($ii = 0; $ii < $iiMax; $ii++) {
		$m = $TData[$ii];
		echo "<tr align=left valign=center>";
		echo "<td align=center>";
		if (($m->photo != "") and ($m->photo != "NULL")) {
			echo "<div id=\"topcontent-profile-photo\">\n";
            echo LinkWithPicture($m->Username,$m->photo);
			echo "<br>";
			echo "</div>";
		}
		echo "</td>";
		echo "<td>", LinkWithUsername($m->Username), "</td>";
		echo " <td>", $m->countryname, "</td> ";
		echo "<td>";
		echo $m->Description;
		echo "</td>";
		echo "</tr>\n";
	}
	echo "</table>\n";

	require_once "footer.php";
}
?>
