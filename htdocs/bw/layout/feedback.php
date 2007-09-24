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

function DisplayResults($Message) {
	global $title;
	$title = ww('FeedbackPage');
	require_once "header.php";

	Menu1("feedback.php", ww('MainPage')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);

	DisplayHeaderWithColumns(ww("ContactUs")); // Display the header
	
   echo $Message;
	require_once "footer.php";
} // end of DisplayResults

function DisplayFeedback($tlist,$IdCategory=0) {
	global $title;
	$title = ww('FeedbackPage');
	require_once "header.php";

	Menu1("feedback.php", ww('MainPage')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);

	DisplayHeaderWithColumns(ww("ContactUs")); // Display the header
   echo "<div class=\"info\">\n"; 
	 echo "<p>", ww("FeedBackDisclaimer"), "</p>\n";
	 echo "<form action=feedback.php method=post>\n";
	 $max = count($tlist);
	 //echo "</div>\n";
	 echo "\n";
	 echo "<div class=\"info highlight\">\n";
	 echo "  <h4>", ww("FeedBackChooseYourCategory"), "</h4>\n";
	 echo "  <p><select name=\"IdCategory\">\n";

	 for ($ii = 0; $ii < $max; $ii++) {
	 	 echo "<option value=" . $tlist[$ii]->id;
		 if ($IdCategory==$tlist[$ii]->id) echo " selected ";
		 echo  ">";
		 echo ww("FeedBackName_" . $tlist[$ii]->Name);
		 echo "</option>\n";
	 }
	 echo "</select>\n</p>\n";
	 echo "<h4>", ww("FeedBackEnterYourQuestion"), "</h4>";
	 echo "<p><textarea name=FeedbackQuestion cols=40 rows=9>", "</textarea></p>\n";
	 echo "<p><input type=checkbox name=urgent> " , ww("FeedBackUrgentQuestion"), "</p>";
	 if (!IsLoggedIn()) {
	 	 echo "<h4>", ww("FeedBackEmailNeeded"), "</h4>";
	 	 echo "<p><input type=\"text\" name=\"Email\" size=\"45\" /></p>";
	 } else {
	  echo "<p><input type=checkbox name=answerneeded> ", ww("FeedBackIWantAnAnswer"), "</p>";
	 }
	 echo "<p><input type=submit id=submit name=submit value=submit></p>\n";
	 echo "<input name=action type=hidden value=ask>\n";
	 echo "</div>\n";
	 echo "</form>\n";
	 echo "</div>\n";

	require_once "footer.php";
}
?>
