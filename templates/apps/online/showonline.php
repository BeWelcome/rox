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
// get current request
$request = PRequest::get()->request;

if (!isset($vars['errors']) || !is_array($vars['errors'])) {
    $vars['errors'] = array();
}

$words = new MOD_words();
	echo "        <div class=\"info\">\n";

	$iiMax = count($TMembers);
	
   echo "<p>",$words->getFormatted("WeAreTotNumber",$TotMembers),"<p></br>" ;
	echo "          <table class=\"memberlist\">";
	for ($ii = 0; $ii < $iiMax; $ii++) {
		$m = $TMembers[$ii];
		echo "<tr align=left" ;
		if  ($ii%2) {
			echo " bgcolor=\"#ffffcc\"" ;
		}
		else {
			echo " bgcolor=\"#ffcccc\"" ;
		}
		echo ">";
		echo "<td valign=center align=center>";
		echo "<div class=\"forumsavatar\">" ;
       echo "<img class=\"framed\" ",MOD_layoutbits::smallUserPic_username($m->Username), " alt=\"avatar\" height=\"56\" width=\"56\" style=\"height:auto; width:auto;\"/>" ;
		echo "</div>" ;
		echo "</td>";
		echo "<td valign=center><a href=\"bw/member.php?cid=".$m->Username."\">".$m->Username."</a>", "</td>";
		echo " <td valign=center>", $m->countryname, "</td> ";
		echo "<td valign=center>";
		echo $words->mTrad($m->ProfileSummary);
		echo "</td>";
		echo "<td valign=center>";
		if (IsAdmin()) {
			echo $m->NbSec," sec ";
		}

		//    echo $m->ProfileSummary;
		if (IsAdmin()) {
			echo $m->lastactivity;
		}

		echo "</td>";
		echo "</tr>";
	} // end of for ii
	echo "</table>";
	
	if (IsAdmin()) {
		 $iiMax = count($TGuests);
		 echo "          <br><table class=\"memberlist\">";
		 echo "<tr><th colspan=2>Guest activity in last ".$_SYSHCVOL['WhoIsOnlineDelayInMinutes']." minutes </th></tr>\n" ;
		 for ($ii = 0; $ii < $iiMax; $ii++) {
		 		 $m = $TGuests[$ii];
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

	if (!APP_User::login()) {
		 echo "<br>",$words->getFormatted("OnlinePrivateProfilesAreNotDisplayed") ;
	}
	echo "\n         </div>\n"; // Class info 
?>
