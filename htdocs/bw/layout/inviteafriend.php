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
function DisplayForm($m,$JoinMemberPict="") {
	global $title;
	$title = ww('InviteAFriendPage');
	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("inviteafriend.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderShortUserContent("inviteafriend.php","",""); // Display the header

	echo "<div class=\"info\">\n";

	// TODO: check the meaning of the next row. $TData is not defined
	$iiMax = count($TData);
	$CurrentCategory="";
	echo "<form method=\"post\" action=\"inviteafriend.php\">\n";
	echo "<input type=\"hidden\" name=\"action\" value=\"Send\">\n";
	echo "<p>",ww("InviteAFriendRule",$m->FullName),"</p>\n";
	echo "<p>",ww("EmailOfYourFriend")," <input type=\"text\" name=\"Email\" value=\"",GetStrParam("Email"),"\"></p>\n";
	if (IsPublic($_SESSION["IdMember"])) {
		 echo "<p>","<textarea name=\"Message\" rows=\"20\" cols=\"80\">",str_replace("<br />","\n",ww("InviteAFriendStandardText","<a href=\"http://www.bewelcome.org/member.php?cid=".$_SESSION["Username"]."\">".$_SESSION["Username"]."</a>")),"</textarea></p>\n";
	}
	else {
		 echo "<p>","<textarea name=\"Message\" rows=\"20\" cols=\"80\">",str_replace("<br />","\n",ww("InviteAFriendTextPrivateProf","<a href=\"http://www.bewelcome.org/member.php?cid=".$_SESSION["Username"]."\">".$_SESSION["Username"]."</a>")),"</textarea></p>\n";
	}
	echo "<p><input type=\"checkbox\" name=\"JoinMemberPict\" ";
	if ($JoinMemberPict=="on") echo "checked";
	echo "> ",ww("JoinMyPicture")," </p>\n";
	echo "<p align=\"center\"><input type=\"submit\" id=\"submit\"></p>\n";
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
