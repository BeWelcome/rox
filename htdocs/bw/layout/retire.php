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
function DisplayForm($m) {
	global $title;
	$title = ww('RetirePage');
	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("retire.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderShortUserContent("retire.php","",""); // Display the header

	echo "<div class=\"info\">\n";

	echo "<form method=post action=retire.php>\n";
	echo "<input type=hidden name=action value=retire>\n";
	echo "<p>",ww("retire_explanation",$m->FullName),"</p>\n";
	echo "<p>",ww("retire_membercanexplain"),"<p><textarea name=reason cols=60 rows=5></textarea><br>\n" ;
	echo "<p><input type=checkbox name=Complete_retire ";
	echo " onclick=\"return confirm ('".ww("retire_WarningConfirmWithdraw")."');\"> ",ww("retire_fulltickbox")," </p>\n";
	echo "<p align=center><input type=submit  onclick=\"return confirm('".ww("retire_WarningConfirmRetire")."');\"></p>\n";
	echo "</div>\n";

	require_once "footer.php";

} // DisplayForm


function DisplayResults($m,$Message) {
	global $title;
	$title = ww('RetirePage');
	require_once "header.php";

	Menu1("retire.php", ww('MainPage')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);

	DisplayHeaderWithColumns(ww("RetirePage")); // Display the header
	
	echo "<div class=\"info\">\n";
   echo "<p>",$Message,"</p>";
	echo "</div>\n";
	require_once "footer.php";
} // end of DisplayResults


?>
