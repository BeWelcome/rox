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
/**
  * display the content of the record of the database for a location
 * @author JeanYves
 */
		
// get current request
$request = PRequest::get()->request;

/**
 * Get texts from table "words" to speak to the user.
 * @see /modules/i18n/lib/words.lib.php
 */
$words = new MOD_words();
?>


<h2>Location records description</h2>
<p>
<?php
    if (count($data)<=0) {
		echo "sorry no record found" ;
	}
	else {
		echo "<table align=\"left\"><tr><th>id</th><th>name</th><th>bw geo Type</th><th>Class</th><th>admincode</th><th>usage</th><th>Other names</th></tr>\n" ;
		foreach ($data as $loc) {
			echo "<tr>" ;
			echo "<td>",$loc->geonameId,"</td>" ;
			echo "<td>",$loc->name,"</td>" ;
			echo "<td>",$loc->TypeLocation,"</td>" ;
			echo "<td>",$loc->fclass." ".$loc->fcode,"</td>" ;
			echo "<td>",$loc->fk_admincode,"</td>" ;
			echo "<td>" ;
			foreach ($loc->usage  as $usage) {
				if ($usage->typeId==1) {
					echo "members ";
				}
				elseif ($usage->typeId==2) {
					echo "blogs ";
				}
				elseif ($usage->typeId==3) {
					echo "galleries ";
				}
				else {
					echo $usage->typeId ;
				}
				echo " - " ;
				echo $usage->count," user(s)<br />" ;
			}
			echo "</td>" ;
			echo "<td>" ;
			foreach ($loc->alternate_names  as $alternate_names) {
				echo $alternate_names->alternateName," (",$alternate_names->isoLanguage,")<br />" ;
				
				if ($usage->typeId==1) {
					echo "members ";
				}
				elseif ($usage->typeId==2) {
					echo "blogs ";
				}
				elseif ($usage->typeId==3) {
					echo "galleries ";
				}
				else {
					echo $usage->typeId ;
				}
				echo " - " ;
				echo $usage->count," user(s)<br />" ;
			}
			echo "</td>" ;
			echo "</tr>" ;
		}
		echo "</table>\n"  ;
	}
?>
</p>