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

// Display the group list without hierarchy
function DisplayGroupList($TGroup) {
	global $title,$_SYSHCVOL;
	$title = ww('GroupsList');
	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("groups.php", ww('Groups')); // Displays the second menu

	$MenuGroup = "";
	if (HasRight("Group")) {
		$MenuGroup = "<li><a href=\"admin/admingroups.php\">AdminGroups</a>";
	}
	DisplayHeaderShortUserContent($title);
  
  echo "<div class=\"info\">\n";
	echo "<form method=post><table>\n";
	echo "<input type=hidden name=cid value=$IdMember>";
	echo "<input type=hidden name=action value=update>";

	$iiMax = count($TGroup);
	for ($ii = 0; $ii < $iiMax; $ii++) {
		echo "<tr><td>";
		echo ww("Group_" . $TGroup[$ii]->Name);
		echo "</td>";
		echo "<td>";
		echo ww("GroupDesc_" . $TGroup[$ii]->Name);
		echo "</td>";
	}
	echo "\n<tr><td align=center colspan=3><input type=submit id=submit name=submit></td>";

	echo "</table>\n";
	echo "</form>\n";
	echo "</div>\n";

	require_once "footer.php";
} // end of DisplayGroupList($TGroup)

// This display the subscription for for a group
function DisplayDispSubscrForm($TGroup) {
	global $title;
	$title = ww("SubscribeToGroup", ww("Group_" . $TGroup->Name));
	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("groups.php", ww('Groups')); // Displays the second menu

	$MenuGroup = "";
	if (HasRight("Group")) {
		$MenuGroup = "<li><a href=\"admin/admingroups.php\">AdminGroups</a>";
	}

	DisplayHeaderWithColumns($title, "", $MenuGroup); // Display the header
  
  echo "<div class=\"info\">\n";
	echo "<form><table>\n";
	echo "<input type=hidden name=action value=Add>";
	echo "<input type=hidden name=IdGroup value=" . $TGroup->id . ">\n";
	if ($TGroup->Type == "NeedAcceptance") {
		$intro = ww("ThisGroupNeedAcceptance", $TGroup->Name);
	} else {
		$intro = ww("ThisGroupDontNeedAcceptance", $TGroup->Name);
	}
	echo "<tr><td colspan=2>";
	echo ww("GroupDesc_" . $TGroup->Name);
	echo "</td>";
	echo "<tr><td colspan=2>", $intro, "</td>\n";
	echo "<tr><td>", ww("ExplayWhyToBeIn", $TGroup->Name), "</td><td><textarea name=Comment cols=70 rows=7></textarea></td>\n";
	echo "<tr><td>",ww('AcceptMessageFromThisGroup'),"</td><td>","<input type=checkbox name=AcceptMessage>","</td>";
	echo "<tr><td colspan=2 align=center><input type=submit id=submit name=submit value=submit></td>";
	echo "</table>\n";
	echo "</form>\n";
  echo "</div>\n";
  
	require_once "footer.php";
} // end of DisplayDispSubscrForm

// This display the members in a group
function DisplayGroupMembers($TGroup, $TMembers,$IdMemberShip=0) {
	global $title;
	$title = ww("GroupsListFor", ww("Group_" . $TGroup->Name));
	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("groups.php", ww('Groups')); // Displays the second menu

	$MenuGroup = "";
	if (HasRight("Group")) {
		$MenuGroup = "<li><a href=\"admin/admingroups.php\">AdminGroups</a>";
		if (HasRight("Group")>=10) {
			 $MenuGroup .= "<li><a href=\"admin/admingroups.php?IdGroup=" . $TGroup->id . "&action=formcreategroup\" title=\"allow moderator to modify group description\"> edit </a><li>" ;
		}
	}
	if (HasRight("Beta","GroupMessage")) { 
		$MenuGroup .= "<li><a href=\"contactgroup.php?IdGroup=".$TGroup->id."\">Send a message to this group</a>";
	}

	DisplayHeaderWithColumns($title, "", $MenuGroup); // Display the header
  
  echo "<div class=\"info\">\n";
	echo "<table>";
	if (!IsLoggedIn()) {
		echo "<tr><td colspan=2>";
		echo ww("MustBeLoggedToSeeAllData");
		echo "</td>";
	}
	if (!empty($TGroup->Picture)) { // Display picture associated with the group if any
		 echo "<tr><td colspan=2 align=center>";
		 echo "<img src=\"$TGroup->Picture\" alt=\"$TGroup->Picture\">" ;
		 echo "</td>" ;
	}
	echo "<tr><td colspan=2>";
//	echo "<b>", ww("Group_" . $TGroup->Name), "</b>";
	echo "</td>";
	echo "<td>";
	echo ww("GroupDesc_" . $TGroup->Name);
	echo "<br>" ;
	if (!empty($TGroup->MoreInfo)) {
		 echo ww("GroupMoreInfo","".$_SYSHCVOL['SiteName'].$TGroup->MoreInfo) ;
	}
	echo "</td>";
	echo "<tr><td colspan='3'><hr /></td>";
	$iiMax = count($TMembers);
	for ($ii = 0; $ii < $iiMax; $ii++) {
		echo "<tr valign=center><td>";
		if ($TMembers[$ii]->photo!="") {
      echo LinkWithPicture($TMembers[$ii]->Username,$TMembers[$ii]->photo);
		}
		echo "</td>";
		echo "<td>";
		echo LinkWithUsername($TMembers[$ii]->Username);
		echo "</td>";
		echo "<td>";
		echo FindTrad($TMembers[$ii]->GroupComment);
		echo "</td>";
	}

	echo "<tr><td colspan=3 align=center><br>";
	if (IsLoggedIn()) { // Logged people can join the group
		if ($IdMemberShip==0) // If member not already in this group propose to join 
		    $joinlink = "groups.php?action=ShowJoinGroup&IdGroup=" . $TGroup->id;
		else
		    $joinlink = "";
	} else {
		$joinlink = "signup.php";
	}
	if ($joinlink != "") echo "<a href=\"", $joinlink, "\">", ww("jointhisgroup"), "</a>\n";
	echo "</td>";

	echo "</table>\n";
  echo "</div>\n";
  
	require_once "footer.php";
} // end of DisplayGroupMembers($TGroup,$TList)

// Display the group list with its hierarchy
function DisplayGroupHierarchyList($TGroup) {
	global $title;
	$title = ww('GroupsList');
	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("groups.php", ww('Groups')); // Displays the second menu

	$MenuGroup = "";
	if (HasRight("Group")) {
		$MenuGroup = "<li><a href=\"admin/admingroups.php\">AdminGroups</a>";
	}
	DisplayHeaderWithColumns($title, "", $MenuGroup); // Display the header

  echo "<div class=\"info\">\n";
	// echo "<form method=post><table>\n";
	echo "<table>\n";
	// echo "<input type=hidden name=cid value=$IdMember>";
	// echo "<input type=hidden name=action value=update>";

	$iiMax = count($TGroup);
	for ($ii = 0; $ii < $iiMax; $ii++) {
		echo "<tr valign=center><td>";
		for ($jj = 0; $jj < $TGroup[$ii]->Depht; $jj++) { // indent according to depht
			echo "&nbsp;&nbsp;";
		}
		echo ww("Group_" . $TGroup[$ii]->Name);
		echo "</td>";
		echo "<td>";
		//		echo "(",$TGroup[$ii]->NbChilds," sub groups) ";
		echo "</td>\n";
		echo "<td>";
		if ($TGroup[$ii]->HasMembers == 'HasMember') {
			if (IsLoggedIn()) { // Logged people can join the group
		 		if ($TGroup[$ii]->IdMemberShip==0) { // If member not already in this group propose to join 
					$wwmsg = "jointhisgroup";
					$joinlink = "groups.php?action=ShowJoinGroup&IdGroup=" . $TGroup[$ii]->IdGroup;
				} else {
					$joinlink = "groups.php?action=LeaveGroup&IdGroup=" . $TGroup[$ii]->IdGroup . "\" onclick=\"return confirm('" . ww("confirmleavethisgroup") . "');";
					$wwmsg = "leavehisgroup";
				}
			} else {
				$wwmsg = "SignupNow";
				$joinlink = "signup.php";
			}
			echo "<a href=\"groups.php?action=ShowMembers&IdGroup=" . $TGroup[$ii]->IdGroup . "\">" . ww("viewthisgroup") . " (" . $TGroup[$ii]->NbMembers . ")</a>&nbsp;&nbsp;&nbsp;\n";
			// todo not display join this group if member is already in
			echo "<a href=\"", $joinlink, "\">", ww($wwmsg), "</a>\n";
		}
		echo "</td>";
	}
	//	echo "\n<tr><td align=center colspan=3><input type=submit id=submit name=submit></td>";

	echo "</table>\n";
	// echo "</form>\n";
	echo "</div>\n";

	require_once "footer.php";
} // DisplayGroupHierarchyList
?>
