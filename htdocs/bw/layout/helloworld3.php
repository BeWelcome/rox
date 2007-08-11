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


require_once ("menus.php"); // load the menu routines

function DisplayHelloWorld3($Data) {
	$title = "hello world three"; // set the title of the page
	require_once "header.php"; // Load the headers routines
	Menu1("", "");

	Menu2($_SERVER["PHP_SELF"], $title); // Displays the second menu

	DisplayHeaderShortUserContent(); // Set the header type here, its a simple one

	echo "<br><center><H2>";
	echo ww("HelloWorldFor", $Data->Username), "<br>"; // HelloWorlddFor is a translatable 
	// word like "Hello World for %s" in english 
	// or "Bonjour le monde � %s" in French, 
	// or "Hallo Welt f�r %s in German etc, etc
	// here %s will be replaced by the username

	echo "!</H2></center>"; /// here is the output 

	echo "<br><br>"; // Skip some lines

	echo ww("ProfileSummary"), " : "; // ProfileSummary will be translated according on current language (if translation is available)
	echo FindTrad($Data->ProfileSummary); // Will find the memberstrads text entry according to current language if available

	echo "\n"; // \n mean carraige return in the output page for easiest readibility

	echo "<br><br>"; // Skip some lines

	echo "<form method=post action=helloworld3.php>";
	echo "<input type=hidden name=action value=show_new_user>";
	echo ww("Username"), "<input type=text name=Username><br>";
	echo "<input type=submit id=submit>";
	echo "</form>";

	require_once "footer.php"; // This close the header
} // end of DisplayHelloWorld2
?>