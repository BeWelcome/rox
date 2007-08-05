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

// This function provide a pagination
function _Pagination($maxpos) {
    $curpos=GetParam("start_rec",0) ; // find current pos (0 if not)
		$width=GetParam("limitcount",10); // Number of records per page
		$PageName=$_SERVER["PHP_SELF"] ;
//		echo "width=",$width,"<br>" ;
//		echo "curpos=",$curpos,"<br>" ;
//		echo "maxpos=",$maxpos,"<br>" ;
		echo "\n<center>" ;
		for ($ii=0;$ii<$maxpos;$ii=$ii+$width) {
				$i1=$ii ;
				$i2=min($ii+$width,$maxpos) ;
				if (($curpos>=$i1) and ($curpos<$i2)) { // mark in bold if it is the current position
					 echo "<b>" ;
				}
				echo "<a href=\"",$PageName,"?start_rec=",$i1,"\">",$i1+1,"..",$i2,"</a> " ;
				if (($curpos>=$i1) and ($curpos<$i2)) { // end of mark in bold if it is the current position
					 echo "</b>" ;
				}
		}
		echo "</center>\n" ;
} // end of function Pagination

function DisplayMembers($TData,$maxpos) {
	global $title;
	$title = ww('MembersPage' . " " . $_POST['Username']);
	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("members.php", ww('MembersPage')); // Displays the second menu

	DisplayHeaderWithColumns(); // Display the header

	$iiMax = count($TData);
	echo "      <div class=\"info\">\n";
	echo "        <table border=\"0\" rules=\"rows\">\n";
	for ($ii = 0; $ii < $iiMax; $ii++) {
		$m = $TData[$ii];
		$info_styles = array(0 => "        <tr class=\"blank\" align=left valign=center>", 1 => "<tr class=\"highlight\" align=left valign=center>");
		echo $info_styles[($ii%2)];
		echo "<td class=\"memberlist\" align=center>";
		if (($m->photo != "") and ($m->photo != "NULL")) {
            echo LinkWithPicture($m->Username,$m->photo);
		}
		echo "</td>";
		echo "<td class=\"memberlist\">", LinkWithUsername($m->Username), "</td>";
		echo "<td>", $m->countryname, "</td> ";
		echo "<td class=\"memberlist\">";
		echo $m->ProfileSummary;
		echo "</td>";
		echo "</tr>\n";
	}
	echo "</table>\n";
	echo "</div>\n";

	_Pagination($maxpos) ;
	
	require_once "footer.php";
}
?>
