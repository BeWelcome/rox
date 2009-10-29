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

// This will be to edit to add various news letters along the time
function DisplayNews() {
	$title = ww('NewsLetters');
	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("newsletters.php", ww("NewsLetters")); // Displays the second menu

	DisplayHeaderShortUserContent("newsletters.php","",""); // Display the header

	if (IsLoggedIn()) {
		 $Username=$_SESSION["Username"] ;
	}
	else { 
			 $Username=" not logged" ;
	}
	echo "<div class=\"info\">\n";
	
	

	echo ww("BroadCast_Title_NewsOctober2009",$Username),"<br>\n"  ;
	echo ww("BroadCast_Body_NewsOctober2009",$Username),"<hr>\n"  ;

	echo ww("BroadCast_Title_July2009NewBod",$Username),"<br>\n"  ;
	echo ww("BroadCast_Body_July2009NewBod",$Username),"<hr>\n"  ;

	echo ww("BroadCast_Title_NewsJune2009",$Username),"<br>\n"  ;
	echo ww("BroadCast_Body_NewsJune2009",$Username),"<hr>\n"  ;

	echo ww("BroadCast_Title_NewsFebruary2009",$Username),"<br>\n"  ;
	echo ww("BroadCast_Body_NewsFebruary2009",$Username),"<hr>\n"  ;

	echo ww("BroadCast_Title_NewsSeptember2008",$Username),"<br>\n"  ;
	echo ww("BroadCast_Body_NewsSeptember2008",$Username),"<hr>\n"  ;

	echo ww("BroadCast_Title_NewsJune2008",$Username),"<br><br>\n"  ;
	echo ww("BroadCast_Body_NewsJune2008",$Username),"<hr>\n"  ;

	echo ww("BroadCast_Title_NewsApril2008",$Username),"<br><br>\n"  ;
	echo ww("BroadCast_Body_NewsApril2008",$Username),"<hr>\n"  ;

	echo ww("BroadCast_Title_NewsOctober2007",$Username),"<br><br>\n"  ;
	echo ww("BroadCast_Body_NewsOctober2007",$Username),"<hr>\n"  ;

	echo ww("BroadCast_Title_NewsJuly2007",$Username),"<br><br>\n"  ;
	echo ww("BroadCast_Body_NewsJuly2007",$Username),"<hr>\n"  ;

	echo "</div>\n";

	require_once "footer.php";

} // DisplayForm


function DisplayResults($m,$Message) {
	global $title;
	$title = ww('InviteAFriendPage');
	require_once "header.php";

	Menu1("inviteafriend.php", ww('MainPage')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);

	DisplayHeaderWithColumns(ww("InviteAFriendPage")); // Display the header
	
   echo $Message;
	require_once "footer.php";
} // end of DisplayResults


?>
