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
function DisplayLogin($nextlink = "") {
	global $title;
	$title = ww('LoginPage');
	require_once "header.php";

	Menu1("login.php", ww('login')); // Displays the top menu

	Menu2("");

	DisplayHeaderShortUserContent(); // Display the header

  echo "        <div class=\"info\">\n";
	echo "          <form method=POST action=login.php>\n";
	echo "          <table>\n";
	echo "            <tr>\n";
	echo "              <td colspan=2>",  "</td>\n";
	echo "                <input type=hidden name=action value=login>\n";
	echo "                <input type=hidden name=nextlink value=\"" . $nextlink . "\">\n";
	echo "              </tr>\n";
	echo "            <tr>\n";
	echo "              <td>", ww("username"), "</td>\n";
	echo "              <td><input name=Username type=text value='", GetStrParam("Username"), "'></td>\n";
	echo "            <tr>\n";
	echo "              <td>", ww("password"), "</td>\n";
	echo "              <td><input type=password name=password></td>\n";
	echo "            </tr>\n";
	echo "            <tr>\n";
	echo "              <td colspan=2 align=center><input type=submit id=submit value='submit'></td>\n";
	echo "            </tr>\n";
	echo "          </table>\n";	
	echo "          </form>\n";
  echo "\n";
	echo "          <p>";
	echo ww("NotYetMember");
	echo "<br />";
	echo ww("SignupLink");
	echo "</p>\n";
	echo "</div>\n";

	require_once "footer.php";
	return;
}
?>
