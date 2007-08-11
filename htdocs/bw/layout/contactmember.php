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
require_once ("profilepage_header.php");

// $iMes contain eventually the previous messaeg number
function DisplayContactMember($m, $Message = "", $iMes = 0, $Warning = "",$JoinMemberPict="") {
	global $title;
	$title = ww('ContactMemberPageFor', $m->Username);
	require_once "header.php";

	Menu1(); // Displays the top menu

	Menu2("member.php");
	// Header of the profile page
	DisplayProfilePageHeader( $m );

	menumember("contactmember.php?cid=" . $m->id, $m);
	$MenuAction = "";
//	$MenuAction .= "               <li><a href=\"contactmember.php?cid=" . $m->id . "\">" . ww("ContactMember") . "</a></li>\n";
	$MenuAction .= "               <li class=\"icon addcomment16\"><a href=\"addcomments.php?cid=" . $m->id . "\">" . ww("addcomments") . "</a></li>\n";
	$MenuAction .= "               <li class=\"icon forumpost16\"><a href=\"todo.php\">".ww("ViewForumPosts")."</a></li>\n";
	if (GetPreference("PreferenceAdvanced")=="Yes") {
      if ($m->IdContact==0) {
	   	  $MenuAction .= "                <li class=\"icon mylist16\"><a href=\"mycontacts.php?IdContact=" . $m->id . "&action=add\">".ww("AddToMyNotes")."</a> </li>\n";
	   }
	   else {
	   	  $MenuAction .= "                <li class=\"icon mylist16\"><a href=\"mycontacts.php?IdContact=" . $m->id . "&action=view\">".ww("ViewMyNotesForThisMember")."</a> </li>\n";
	   }
	}
	ShowActions($MenuAction); // Show the Actions
	ShowAds(); // Show the Ads

	// col3 (middle column)
	echo "\n";
	echo "      <div id=\"col3\"> \n";
	echo "        <div id=\"col3_content\"> \n"; 
  echo "          <div class=\"info highlight\">\n";

	if ($Warning != "") {
		echo "<br><table width=50%><tr><td><h4><font color=red>";
		echo $Warning;
		echo "</font></h4></td></table>\n";
	}

	echo "            <form method=post action=contactmember.php>\n";
	echo "              <input type=hidden name=action value=sendmessage>\n";
	echo "              <input type=hidden name=cid value=\"" . $m->id . "\">\n";
	echo "              <input type=hidden name=iMes value=\"" . $iMes . "\">\n";
	echo "              <h4>", ww("YourMessageFor", LinkWithUsername($m->Username)), "</h4>\n";
	echo "              <p><textarea name=Message rows=15 cols=80>", $Message, "</textarea></p>\n";
	echo "              <p>", ww("IamAwareOfSpamCheckingRules"), "</p>\n";
	echo "              <p><input type=checkbox name=IamAwareOfSpamCheckingRules> ", ww("IAgree"),"</p>\n";
	echo "              <p>";
	echo "<input type=checkbox name=JoinMemberPict ";
	if ($JoinMemberPict=="on") echo "checked";
	echo "> ", ww("JoinMyPicture"),"</p>\n";
	echo "              <p><input type=submit id=submit name=submit value=submit>";
	if (GetPreference("PreferenceAdvanced")=="Yes") echo " <input type=submit id=submit name=action value=\"", ww("SaveAsDraft"), "\">";
	echo "</p>\n";
	echo "            </form>\n";
	echo "          </div>\n";

	require_once "footer.php";

}

function DisplayResult($m, $Message = "", $Result = "") {
	global $title;
	$title = ww('ContactMemberPageFor', $m->Username);
	require_once "header.php";

	Menu1(); // Displays the top menu

	Menu2("member.php");
	// Header of the profile page
	DisplayProfilePageHeader( $m );

	menumember("contactmember.php?cid=" . $m->id, $m);
	ShowActions("<li><a href=\"todo.php\">Add to my list</a></li>\n<li><a href=\"todo.php\">View forum posts</a></li>\n"); // Show the Actions
	ShowAds(); // Show the Ads
	
	echo "\n";
	echo "      <div id=\"col3\"> \n";
	echo "        <div id=\"col3_content\"> \n";
	echo "          <div class=\"info highlight\">\n";
	echo "            <h2>Contact ", $m->Username, "</h2>\n";

	echo "            <p>";
	echo $Result;
	echo "</p>\n";
	echo "          </div>";

	require_once "footer.php";

} // end of display result
?>
