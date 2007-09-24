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
// $iMes contain eventually the previous messaeg number
function DisplayContactGroup($IdGroup,$Title="", $Message = "", $Warning = "",$JoinMemberPict="") {
	global $title;
	$title = ww('ContactGroupPage');
	require_once "header.php";

	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("contactgroup.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderShortUserContent($title);


	echo "     <div id=\"columns-middle\">\n";
	if ($Warning != "") {
		echo "<br><br><table width=50%><tr><td><h4><font color=red>";
		echo $Warning;
		echo "</font></h4></td></table>\n";
	}

	echo "<form method=post>";
	echo "<input type=hidden name=action value=sendmessage>";
	echo "<input type=hidden name=IdGroup value=$IdGroup>";
	echo "<table width=70%>\n";
	echo "<tr><td colspan=3 align=center>", ww("YourMessageForGroup", LinkWithGroup($IdGroup)), "<br>";
	echo "<textarea name=Title rows=1 cols=80>", $Title, "</textarea><br>";
	echo "<textarea name=Message rows=15 cols=80>", $Message, "</textarea></td>";
	echo "<tr><td colspan=2>", ww("IamAwareOfSpamCheckingRules"), "</td><td width=20%>", ww("IAgree"), " <input type=checkbox name=IamAwareOfSpamCheckingRules><br>";
	echo ww("JoinMyPicture")," <input type=checkbox name=JoinMemberPict ";
	if ($JoinMemberPict=="on") echo "checked";
	echo ">";
	echo "</td>";
	echo "<tr>";
	echo "<td align=center colspan=3 align=center><input type=submit id=submit name=submit value=submit></td>";
	echo "</table>\n";
	echo "</form>";
	echo "     </div>\n";

	require_once "footer.php";

}

function DisplayResult($Group,$Title,$Message, $Result = "") {
	global $title;
	$title = ww('ContactGroupPage', $m->Username);
	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("contactgroup.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderShortUserContent($title);

	echo "<center>";
	echo "<H1>Contact ", LinkWithGroup($Group), "</H1>\n";

	echo "<br><br><table width=50%>";
	echo "<tr><td><i>",$Title,"</i></td>";
	echo "<tr><td>",$Message,"</td>";
	echo "<tr><td><h4>";
	echo $Result;
	echo "</h4></td></table>\n";

	require_once "footer.php";

} // end of display result
?>
