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

// Display links
function DisplayLinks() {
	global $title;
	$title = ww('LinksPage');
	include "header.php";

	Menu1("", ww('LinksPage')); // Displays the top menu
	Menu2($_SERVER["PHP_SELF"]); // Displays the second menu

	DisplayHeaderWithColumns(ww("LinksPage")); // Display the header
	
	echo ww("LinksPageExplanation") ;	
	
	echo "<ul>\n";

	echo "<li>" ;
	echo "<a href=\"http://www.forum-voyages-vacances.com\" target=\"_blank\">Forum du voyage</a>" ;
	echo "</li>\n" ;

	echo "</ul>\n";

	include "footer.php";
} // end of DisplayLinks

?>
