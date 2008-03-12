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

function DisplayWhoIsOnLine($TData,$TGuest,$TotMember=0,$TotMemberSinceMidnight=0) {
	global $title;
	$title = ww('WhoIsOnLinePage');
	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"], ww('WhoIsOnLinePage')); // Displays the second menu

	DisplayHeaderShortUserContent($title); // Display the header	
		
	echo "        <div class=\"info\">\n";

	$iiMax = count($TData);
  echo "<p>",ww("WeAreTotNumber",$TotMember),"<p></br>" ;
	echo "          <table class=\"memberlist\">";
	for ($ii = 0; $ii < $iiMax; $ii++) {
		$m = $TData[$ii];
		echo "<tr align=left>";
		echo "<td valign=center align=center>";
		if (($m->photo != "") and ($m->photo != "NULL")) {
			echo "<div id=\"topcontent-profile-photo\">\n";
		    echo LinkWithPicture($m->Username,$m->photo);
//			echo "<a href=\"", $m->photo, "\" title=\"", str_replace("\r\n", " ", $m->phototext), "\">\n<img src=\"" . $m->photo . "\" height=\"100px\" ></a>\n<br>";
			echo "</div>";
		}
		echo "</td>";
		echo "<td valign=center>",LinkWithUsername($m->Username), "</td>";
		echo " <td valign=center>", $m->countryname, "</td> ";
		echo "<td valign=center>";
		//    echo $m->ProfileSummary;
		if (IsAdmin() and ($_SERVER['SERVER_NAME'] != "www.bewelcome.org")) { // on production server this is not visible
			echo $m->NbSec," sec";
		}

		echo "</td>";
		echo "<td valign=center>";
		//    echo $m->ProfileSummary;
		if (IsAdmin() and ($_SERVER['SERVER_NAME'] != "www.bewelcome.org")) { // on production server this is not visible
			echo $m->lastactivity;
		}

		echo "</td>";
		echo "</tr>";
	} // end of for ii
	echo "</table>";
	
	if (IsAdmin()) {
		 $iiMax = count($TGuest);
		 echo "          <br><table class=\"memberlist\">";
		 echo "<tr><th colspan=2>Guest activity in last ".$_SYSHCVOL['WhoIsOnlineDelayInMinutes']." minutes </th></tr>\n" ;
		 for ($ii = 0; $ii < $iiMax; $ii++) {
		 		 $m = $TGuest[$ii];
				 echo "<tr align=left>";
				 echo "<td valign=center>";
				 echo $m->NbSec;
				 echo " sec</td>";
				 echo "<td valign=center>";
				 echo "<a href=\"/admin/adminlogs.php?ip=".$m->appearance."\">".$m->appearance."</a>";
				 echo "</td>";
				 echo "<td valign=center>";
				 echo $m->lastactivity;
				 echo "</td>";
				 echo "</tr>";
			} // end of for ii
			echo "</table>";
	
	}

	if (!IsLoggedIn()) {
		 echo "<br>",ww("OnlinePrivateProfilesAreNotDisplayed") ;
	}
	echo "\n         </div>\n"; // Class info 

	require_once "footer.php";
	;
}
?>
