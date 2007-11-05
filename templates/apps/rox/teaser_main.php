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
$imagePathMember = MOD_user::getImage()
?>

     <div id="teaser" class="clearfix teaser_main">
	 
<?php	

	echo "        <h2>" . $words->getFormatted('HelloUsername', $_SESSION['Username']) . "</h2>\n";
	
	echo "        <div id=\"teaser_l\">\n"; 
	echo "				<img src=\"" . $imagePathMember . "\" id=\"MainUserpic\" alt=\"ProfilePicture\"/>\n";	
	echo "        </div>\n"; 
	
	echo "        <div id=\"teaser_r\">\n"; 
	
	echo "			<div class=\"subcolumns\">\n";
	echo "				<div class=\"c38l\">\n";
	echo "    				<div class=\"subcl\">\n";
	echo "          	<p class=\"floatbox\"><a href=\"bw/mymessages.php\"><img src=\"images/icons/icons1616/icon_contactmember.png\" alt=\"Messages\"/>", $words->get('MainPageNewMessages'),"</a></p>\n";
	echo "          	<p class=\"floatbox\"><a href=\"bw/viewcomments.php\"><img src=\"images/icons/icons1616/icon_addcomments.png\" alt=\"Comments\"/>", $words->get('MainPageNewComments'),"</a></p>\n";
	echo "          	<p class=\"floatbox\"><a href=\"bw/myvisitors.php\"><img src=\"images/icons/icons1616/icon_myvisitors.png\" alt=\"Visitors\"/>", $words->get('MainPageNewVisitors'),"</a></p>\n";	
	echo "        			</div>\n";
	echo "      		</div>\n";
	echo "				<div class=\"c62r\">\n";
	echo "					<div class=\"subcr\">\n";		
	/* 
	**   deactivated for now
	echo "						<div id=\"mapsearch\">\n";
	echo "						<form>\n";
	echo "					          <fieldset> \n";
	echo "					          <input type=\"text\" name=\"searchtext\" size=\"10\" maxlength=\"30\" id=\"text-field\" />\n";
	echo "					          <input type=\"hidden\" name=\"action\" value=\"mapsearch\" />\n";
	echo "					          <input type=\"button\" value=\"Search\" class=\"button\" id=\"submit-button\" /><br />\n";
	echo "							  Search the map\n";
	echo "					        </fieldset>\n";
	echo "						</form>\n";    
	echo "						</div>\n";	
	*/				
	/* Instead we use this temporary solution */	
	echo "						<div id=\"mapsearch\">\n";
	echo "						<form>\n";
	echo "					          <fieldset> \n";
	echo "					          	<h2 style=\"margin-top: 20px; background-color: #ff8800; -moz-opacity:0.1; opacity:0.7; \"><a href=\"searchmembers/index\" style=\"-moz-opacity:1; opacity:1; \">", $words->get('FindMembers'),"</a></h1>\n";
	echo "					        </fieldset>\n";
	echo "						</form>\n";    
	echo "						</div>\n";			
	echo "					</div>\n";
	echo "				</div>\n";
	echo "			</div>\n";
	
	echo "        </div>\n";
			
?>
    </div> <!-- teaser --> 