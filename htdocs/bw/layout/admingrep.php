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
function DisplayGrepForm($s1 = "", $s2 = "", $stringnot = "", $scope, $RightLevel, $previousres = "") {
	global $countmatch;
	global $title;
	$title = "AdminGrep";
	require_once "header.php";
	Menu1("", $title); // Displays the top menu

	Menu2("admin/admingrep.php", $title); // Displays the second menu

	DisplayHeaderShortUserContent($title);

	if ($previousres != "") {
		echo "<table bgcolor=gray width=100%>";
		echo "<tr><th bgcolor=silver>Looking in (<b>$repertoire</b>$scope) for <b><font color=blue>", stripslashes($s1), "</font></b>";
		if ($s2 != "")
			echo " and for <b><font color=blue>", stripslashes($s2), "</font></b>";
		if ($stringnot != "")
			echo " and NOT <b><font color=blue>", stripslashes($stringnot), "</font></b>";
		echo "</th>\n";

		echo "<tr><td>";
		echo $previousres;
		echo "</td>";
		echo "<tr><td>match : ";
		echo $countmatch;
		echo "</td>";
		echo "</table>";
	}

	echo "\n<form method=post><center><table bgcolor=silver><tr bgcolor=gray><th colspan=2>parameters</th>";
	//  echo "\n<tr><td>directory (leave empty)</td><td><input type=text name=repertoire value=\"$repertoire\" size=30></td>";
	if ($RightLevel >= 5) {
		echo "\n<tr><td>File Scope</td><td><input type=text name=scope value=\"$scope\" size=60></td>";
	} else {
		echo "\n<tr><td>File Scope</td><td><input type=text readonly name=scope value=\"$scope\" size=60></td>";
	}
	echo "\n<tr><td>string to find</td><td><input type=text name=s1 value=\"", stripslashes(htmlentities($s1)), "\" size=30></td>";
	echo "\n<tr><td>and 2nd string to find</td><td><input type=text name=s2 value=\"", stripslashes(htmlentities($s2)), "\" size=30></td>";
	echo "\n<tr><td>and string not to have</td><td><input type=text name=stringnot value=\"", stripslashes(htmlentities($stringnot)), "\" size=30></td>";
	echo "<input type=hidden name=action value=\"grep\" >";
	echo "\n<tr><td colspan=2 align=center><input type=submit id=submit name=submit value=\"find\" size=2></td>";

	echo "\n</table></center></form>";

	require_once "footer.php";
} // end of DisplayGrepForm

function showfile($fname, $searchstr, $nbligne, $searchstr2, $searchnot) {
	if ($fname == "")
		return;
	global $countmatch;
	if ($searchstr == "")
		return ("");
	$res = "";
	$ff = fopen($fname, "r");
	$NameIsShow = false;
	$sbefore = "";
	$iligne = 0;
	$ss = null;
	if ($ff) {
		//	  echo "looking in $fname for $searchstr<br>";
		while (!feof($ff)) {
			$sbefore = $ss;
			$ss = fgets($ff);
			$iligne++;
			if ($ss == "")
				continue;
			if ((stristr($ss, $searchstr)) and (($searchstr2 == "") or (($searchstr2 != "") and (stristr($ss, $searchstr2)))) and (($searchnot == "") or (($searchnot != "") and (!stristr($ss, $searchnot))))) {
				if (!$NameIsShow) {
					$NameIsShow = true;
					$res .= "\n<table bgcolor=#ffffcc style=\"color:blue;font-size:12;\" border=1 class=s width=100%>\n<tr><td align=left>File <b>$fname</b></td>\n<tr><td>";
				}
				$res .= sprintf("<font color=green>%04d </font>", $iligne);
				$ss = ereg_replace("<", "&#60", $ss);
				$ss = ereg_replace(">", "&#62", $ss);
				$res .= $ss . "<br>";
				$countmatch++;
			}
		} // end of while
		if ($NameIsShow)
			$res .= "</td></table>\n";
	} else {
		$res .= "<table><tr><td><font color=red>impossible to open <b>$fname</b></font></td></table>";
	}
	return ($res);
} // Fin de showfile
?>
