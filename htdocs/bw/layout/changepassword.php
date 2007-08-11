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

function DisplayChangePasswordForm($CurrentError) {
	global $title;
	$title = ww('ChangePasswordPage');
	require_once "header.php";

	Menu1("", ww('ChangePasswordPage')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);

	echo "\n<div id=\"main\">\n";
	echo "  <div id=\"teaser\">";
	echo "					<h3>", ww("ChangePasswordPage"), "</h3>\n";
	echo "\n  </div>\n";
	echo "</div>\n";

	ShowActions(); // Show the actions
	ShowAds(); // Show the Ads

	echo "		<div id=\"col3\">\n";
	echo "			<div id=\"col3_content\">\n";
	echo "				<div class=\"info\">\n";

	echo "<center>";
	if ($CurrentError != "") {
		echo $CurrentError;
	}
	echo "<table>\n<form method=post>\n";
	echo "  <input type=hidden name=action value=changepassword>\n";
	echo "<tr><td>", ww("OldPassword"), "</td><td><input type=password name=OldPassword></td>\n";
	echo "<tr><td>", ww("NewPassword"), "</td><td><input type=password name=NewPassword></td>\n";
	echo "<tr><td>", ww("SignupCheckPassword"), "</td><td><input type=password name=SecPassword></td>\n";
	echo "<tr><td colspan=2 align=center><input type=submit id=submit name=submit value=submit></td>\n";
	echo "</form>\n</table></center>\n";

	echo "\n         </div>\n"; // Class info 

	require_once "footer.php";
}
?>
