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
$words = new MOD_words();
?>

<div id="teaser" class="clearfix">
<div id="teaser_index"> 

<div id="teaser_l1"> 
<?php	
	// Random teaser content generation
	$chKey = rand(2,8); //case 1 is deactivated as showing random user pics doesn't work yet
	
	switch ($chKey) {
		case 1:
			echo "		<div class=\"subcolumns\">\n"; 
			// Display the last created members with a picture
			$m=$mlastpublic ;
			echo "			  <div class=\"c75l\">\n"; 
				echo "<h1>", $words->get('IndexPageWord2a'),"</h1>\n"; // Needs to be something like "Go, travel the world!"
				echo "			  <div class=\"c50l\">\n"; 
				echo "			    <div class=\"subl\">\n"; 
				echo "<h2>", $words->get('IndexPageWord1a'),"</h2>\n"; // Needs to be something like "Some are tired of discovering the world only in front of their TV:"
				echo "			    </div>\n"; 
				echo "			  </div>\n"; 
				echo "			  <div class=\"c50l\">\n"; 
				echo "			  <div class=\"c50l\">\n"; 
					echo "			    <div class=\"subl\">\n"; 
					echo "				<p class=\"floatbox UserpicFloated\">";
					echo LinkWithPicture($m->Username,$m->photo), LinkWithUsername($m->Username),"<br />",$m->countryname ;
					echo "				</p>\n"; 
					echo "			    </div>\n"; 
				echo "			    </div>\n"; 
				echo "			  <div class=\"c50r\">\n"; 
					echo "			    <div class=\"subr\">\n"; 
					echo "				<p class=\"floatbox UserpicFloated\">";
					echo LinkWithPicture($m->Username,$m->photo), LinkWithUsername($m->Username),"<br />",$m->countryname ;
					echo "				</p>\n"; 
					echo "			    </div>\n"; 
				echo "			    </div>\n";  
				echo "			  </div>\n"; 
			echo "			  </div>\n"; 
			
			echo "			  <div class=\"c25l\">\n"; 
			echo "			    <div class=\"subl\">\n"; 
			echo "				<p class=\"floatbox\">";
			echo "				</p>\n"; 
			echo "			    </div>\n"; 
			echo "			  </div>\n"; 
			echo "		</div>\n"; 
			
			break;
		case 2:
			echo "<h2>", $words->get('IndexPageWord1'),"</h2>\n";
			echo "<h1>", $words->get('IndexPageWord2'),"</h1>\n";
			break;
		case 3:
			echo "<h2>", $words->get('IndexPageWord1b'),"</h2>\n";
			echo "<h1>", $words->get('IndexPageWord2'),"</h1>\n";
			break;
		case 4:
			echo "<h1><span>\"", $words->get('slogan_Pathsaremadebywalking'),"\"</span></h1>\n";
			echo "<h2>Frank Kafka (1883 - 1924)</h2>\n";
			break;
		case 5:
			echo "<h1><span>\"", $words->get('slogan_Theworldisabook'),"\"</span></h1>\n";
			echo "<h2>Saint Augustin (354 - 430)</h2>\n";
			break;
		case 6:
			echo "<h1><span>\"", $words->get('slogan_Donttellme'),"\"</span></h1>\n";
			echo "<h2>Muhammad (570 - 632)</h2>\n";
			break;
		case 7:
			echo "<h1><span>\"", $words->get('slogan_Travellingislikeflirting'),"\"</span></h1>\n";
			echo "<h2>Advertisement</h2>\n";
			break;
		case 8:
			echo "<h1><span>\"Meeting people is what makes life worth living.\"</span></h1>\n";
			echo "<h2>Guy de Maupassant</h2>\n";
			// "Es sind die Begegnungen mit Menschen, die das Leben lebenswert machen." / "Meeting people is what makes life worth living.
			break;
	}
?>
</div>
<!--<div id="teaser_r"> 
</div>-->
</div>
</div>