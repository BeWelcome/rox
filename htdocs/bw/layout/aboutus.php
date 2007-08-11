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

function DisplayAboutUs() {
	global $title;
	$title = ww('AboutUsPage');
	require_once "header.php";
	Menu1("aboutus.php", ww('AboutUsPage')); // Displays the top menu
	Menu2("aboutus.php", ww('GetAnswers')); // Displays the second menu

	echo "\n";
	echo "    <div id=\"main\">\n";
	echo "      <div id=\"teaser_bg\">\n";
	echo "      <div id=\"teaser\">\n";
	echo "        <h1>", $title, " </h1>\n";
	echo "      </div>\n";

	menugetanswers("aboutus.php" . $menutab, $title);
	echo "      </div>\n";

        // ShowLeftColumn($MenuAction)  ; // Show the Actions
	ShowAds(); // Show the Ads

	// Content with just two columns
	echo "\n";
	echo "      <div id=\"col3\" class=\"twocolumns\">\n";
	echo "        <div id=\"col3_content\" class=\"clearfix\">\n";

?>

<div class="subcolumns">
  <div class="c50l">
    <div class="subcl">
<?php
	echo "<div class=\"info\">\n";
	echo "<h3>", ww("AboutUs_TheIdea"),"</h3>";
	echo "<p>",ww("AboutUs_TheIdeaText"),"</p>";
	echo "<h3>", ww("AboutUs_GetActive"),"</h3>";
	echo "<p>",ww("AboutUs_GetActiveText"),"</p>";
	echo "<p>",ww("AboutUs_Greetings"),"</p>";
	echo "</div>\n";
?>
    </div>
   </div>


  <div class="c50r">
    <div class="subcr">
<?php	
	echo "<div class=\"info\">\n";
	echo "<h3>", ww("AboutUs_HowOrganized"),"</h3>";
	echo "<p>",ww("AboutUs_HowOrganizedText"),"</p>";
	echo "<h3>", ww("AboutUs_GiveFeedback"),"</h3>";
	echo "<p>",ww("AboutUs_GiveFeedbackText"),"</p>";
	echo "</div>\n";
?>			  
    </div>
  </div>
</div>	
<?php

	require_once "footer.php";
}
?>