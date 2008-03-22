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

function DisplayResults($TList, $searchtext = "") {

    $words = new MOD_words();

	$iiMax = count($TList);

	echo "          <div class=\"info\">\n";
	echo "            <table border=\"0\">\n";
	
	if ($iiMax>0) { // only display results if they are found entries
		 echo "              <tr valign=\"center\">\n";
		 echo "                <th align=\"left\">".$words->getFormatted("Username")."</th>\n";
		 echo "                <th>".$words->getFormatted("ProfileSummary")."</th>\n";
		 echo "                <th>".$words->getFormatted("quicksearchresults", $searchtext)."</th>\n";
		 echo "              </tr>\n";
	}
        $info_styles = array(0 => "<tr class=\"blank\" align=\"left\" valign=\"center\">", 1 => "<tr class=\"highlight\" align=\"left\" valign=\"center\">");
	for ($ii = 0; $ii < $iiMax; $ii++) {
    	static $ii2 = 0;
		if (($ii==0) or ($TList[$ii]->Username!=$TList[$ii-1]->Username)) {  // don't display list with everytime the same username
        	 echo $info_styles[($ii2++%2)]; // this display the <tr>
			 echo "                <td class=\"memberlist\" align=left>\n" ;
             echo "                  ".$TList[$ii]->photo.'<a href="bw/member.php?cid='.$TList[$ii]->Username.'">'.$TList[$ii]->Username.'</a>';
             echo "                  <br />".$TList[$ii]->CityName.'<br />'.$TList[$ii]->CountryName.'<br />';
			 echo "\n";
			 echo "                </td>\n";
             echo "                <td>";
    		 echo $TList[$ii]->ProfileSummary;
    		 echo "                </td>\n";
		}
		else {
             echo "          <tr>\n";
			 echo "                <td></td>\n";
             echo "                <td></td>\n";
		}
		echo "                <td>", $TList[$ii]->result;
		echo "</td>\n";
		echo "               </tr>\n";
		
	}
	echo "            </table>\n";
	
	if ($iiMax==0) {
		echo "<p>",$words->getFormatted("SorryNoresults", $searchtext,"</p>");
	}
	echo "<hr />\n";
	echo '<p><a href="searchmembers/mapon">'.$words->getFormatted('TryMapSearch').'</a></p>';
}
DisplayResults($TList, $searchtext); // call the layout with all countries
?>

