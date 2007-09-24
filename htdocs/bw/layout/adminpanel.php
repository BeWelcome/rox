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
function DisplayPannel($TData, $Message = "") {
	global $title;
	global $PannelScope;
	if ($title == "")
		$title = "Admin Pannel";
	require_once "header.php";
	Menu1("", "Admin pannel"); // Displays the top menu

	Menu2("adminpannel.php", $title); // Displays the second menu

	DisplayHeaderShortUserContent($Message);

	echo "Your Scope is for <b>", $PannelScope, "</b><br>";

	$max = count($TData);
	echo "<form method=post>\n";
	echo "<table>\n";
	echo "<tr><th colspan=2>key</th><th colspan=2>value</th><th>comment</th>\n";
	for ($ii = 0; $ii < $max; $ii++) {
		$rr = $TData[$ii];
		//	  echo "<tr><td>",$ii,"</td><td>",$rr->SYSHCvol_key ,$rr->SYSHCvol_value,$rr->SYSHCvol_comment,"</td>\n";

		echo "<tr>\n";
		echo "<td><textarea name=SYSHCvol_key_" . $ii . " rows=1 cols=50>", $rr->SYSHCvol_key . "</textarea></td><td>=</td>\n";
		echo "<td><textarea name=SYSHCvol_value_" . $ii . " rows=1 cols=50>", $rr->SYSHCvol_value . "</textarea><td> //</td></td>\n";
		echo "<td><textarea name=SYSHCvol_comment_" . $ii . " rows=1 cols=50>", $rr->SYSHCvol_comment . "</textarea></td>\n";
		echo "<td></td>\n";

	}
	echo "</table>\n";
	echo "<input type=submit id=submit name=action value=\"SaveToDB\"> &nbsp;&nbsp;&nbsp;";
	echo "<input type=submit id=submit name=action value=\"LoadFromDB\"> &nbsp;&nbsp;&nbsp;";
	echo "<input type=submit id=submit name=action value=\"LoadFromFile\"> &nbsp;&nbsp;&nbsp;";
	echo "<input type=submit id=submit name=action value=\"Generate\"> &nbsp;&nbsp;&nbsp;";

	echo "</form>\n";
	echo "<hr />";
	for ($ii = 0; $ii < $max; $ii++) {
		$rr = $TData[$ii];
		echo "<div style=\"font-size=10px;color=green;\">";
		if ($rr->SYSHCvol_key != "")
			echo $rr->SYSHCvol_key, "=";
		echo $rr->SYSHCvol_value, " //", $rr->SYSHCvol_comment;
		echo "</div>\n";
	}

	require_once "footer.php";
} // end of DisplayPannel
?>