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

// This form propose the members to admin
function DisplayAdminGroups($TPending, $Message) {
  global $countmatch;
  global $title;
  $title = "Admin groups ".RightScope('Group');
  require_once "header.php";

  Menu1("", ww('MainPage')); // Displays the top menu

  Menu2("admin/admingroups.php", ww('MainPage')); // Displays the second menu

  if (HasRight("Group") >= 10) {
      $MenuAction  = "            <li><a href=\"admingroups.php?action=formcreategroup\">Create a new group</a></li>\n";
  }
  $MenuAction .= "            <li><a href=\"admingroups.php?action=updategroupscounter\">Update group counters</a></li>\n";
  $MenuAction .= "            <li><a href=\"admingroups.php?action=listgroups\">List Groups</a></li>\n";
  $MenuAction .= "            <li><a href=\"http://www.bevolunteer.org/wiki/AdminGroup_Tool:_HowTo\">Wiki How To</a></li>\n" ;

  DisplayHeaderShortUserContent($title);
  ShowLeftColumn($MenuAction,VolMenu());

  echo "    <div id=\"col3\"> \n";
  echo "      <div id=\"col3_content\" class=\"clearfix\"> \n";
  echo "        <div class=\"info\">\n";

  if ($Message != "") {
    echo "<h2>$Message</h2>";
  }
  $max = count($TPending);
  $count = 0;

  echo "<h3> Pending Members to accept</h3>\n";
  echo "<table class=\"fixed\">\n";
  for ($ii = 0; $ii < $max; $ii++) {
    $rr = $TPending[$ii];
    $count++;
    echo "<tr>";
    echo "<td>", $rr->GroupName, "</td>";
    echo "<td>", LinkWithUsername($rr->Username), "</td><td>";
    if ($rr->Comment > 0)
      echo FindTrad($rr->Comment);
    echo "</td>\n";
    echo "<td>";
    echo "<form method=post action=admingroups.php>";
    echo "<input type=hidden name=action value=accept>";
    echo "<input type=hidden name=IdMembership value=", $rr->IdMembership, ">";
    echo "<input type=submit id=submit name=submit value=accept>";
    echo "</form> ";
    echo "<form method=post action=admingroups.php>";
    echo "<input type=hidden name=action value=Kicked>";
    echo "<input type=hidden name=IdMembership value=", $rr->IdMembership, ">";
    echo "<input type=submit id=submit name=submit value=Kicked>";
    echo "</form>";
    echo "</td>";
  }
  echo "<tr><td align=right>Total</td><td align=left>$count</td>";
  echo "\n</table><br>\n";

  echo "</center>";
  require_once "footer.php";
} // end of DisplayAdminGroups($TPending,$Message)

// This form propose the members to admin
// according to the right he has on groups
function DisplayGroupList($TPending, $Message) {
  global $countmatch;
  global $title;
  $title = "My Admin groups ".RightScope('Group');
  require_once "header.php";

  Menu1("", ww('MainPage')); // Displays the top menu

  Menu2("admin/admingroups.php", ww('MainPage')); // Displays the second menu

  if (HasRight("Group") >= 10) {
      $MenuAction  = "            <li><a href=\"admingroups.php?action=formcreategroup\">Create a new group</a></li>\n";
  }
  $MenuAction .= "            <li><a href=\"admingroups.php?action=updategroupscounter\">Update group counters</a></li>\n";
  $MenuAction .= "            <li><a href=\"admingroups.php?action=listgroups\">List Groups</a></li>\n";
  $MenuAction .= "            <li><a href=\"http://www.bevolunteer.org/wiki/AdminGroup_Tool:_HowTo\">Wiki How To</a></li>\n" ;

  DisplayHeaderShortUserContent($title);
  ShowLeftColumn($MenuAction,VolMenu());

  echo "    <div id=\"col3\"> \n";
  echo "      <div id=\"col3_content\" class=\"clearfix\"> \n";
  echo "        <div class=\"info\">\n";

  if ($Message != "") {
    echo "<h2>$Message</h2>";
  }
  $max = count($TPending);
  $count = 0;

  echo "<h3> The groups I can manage</h3>\n";
  echo "<table class=\"fixed\">\n";
  for ($ii = 0; $ii < $max; $ii++) {
    $rr = $TPending[$ii];
		if (!HasRight("Group", $rr->GroupName)) continue ;
    $count++;
    echo "<tr>";
    echo "<td>", $rr->GroupName, "</td>";
    echo "<td>";
    echo "<form method=post action=admingroups.php>";
    echo "<input type=hidden name=action value=ShowMembers>";
    echo "<input type=hidden name=IdGroup value=", $rr->IdGroup, ">";
    echo "<input type=submit id=submit name=submit value=\"Managel location\">";
    echo "</form> ";
    echo "</td>";
  }
  echo "<tr><td align=right>Total</td><td align=left>$count</td>";
  echo "\n</table><br>\n";

  echo "</center>";
  require_once "footer.php";
} // end of DisplayGroupList($TPending,$Message)

// This function propose to create a group
function DisplayFormCreateGroups($IdGroup, $Name = "", $IdParent = 0, $Type = "", $TGroupList,$Group_="",$GroupDesc_="",$MoreInfo,$Picture) {
  global $title;
  $title = "Create a new group";
  require_once "header.php";

  Menu1("", ww('MainPage')); // Displays the top menu

  Menu2("admin/admingroups.php", ww('MainPage')); // Displays the second menu

  if (HasRight("Group") >= 10) {
      $MenuAction  = "            <li><a href=\"admingroups.php?action=formcreategroup\">Create a new group</a></li>\n";
  }
  $MenuAction .= "            <li><a href=\"admingroups.php?action=updategroupscounter\">Update group counters</a></li>\n";
  $MenuAction .= "            <li><a href=\"admingroups.php?action=listgroups\">List Groups</a></li>\n";
  $MenuAction .= "            <li><a href=\"http://www.bevolunteer.org/wiki/AdminGroup_Tool:_HowTo\">Wiki How To</a></li>\n" ;

  DisplayHeaderShortUserContent($title);
  ShowLeftColumn($MenuAction,VolMenu());

  echo "    <div id=\"col3\"> \n";
  echo "      <div id=\"col3_content\" class=\"clearfix\"> \n";
  echo "        <div class=\"info\">\n";

  echo "<form method=post action=admingroups.php>\n";
  echo "<input type=hidden name=IdGroup value=$IdGroup>";
  echo "<table>";
  echo "<tr><td width=30%>Give the code name of the group as a word entry (must not exist in words table previously) like<br> <b>BeatlesLover</b> or <b>BigSausageEaters</b> without spaces !<br>";
  echo "</td>";
  echo "<td>";
  echo "<input type=text ";
  if ($Name != "")
    echo "readonly"; // don't change a group name because it is connected to words
  echo " name=Name value=\"$Name\">";
  echo "</td>";
  echo "<tr><td>Give the group parent of this group</b><br>1 is the value for initial groups of first level</td>";
  echo "<td>";
  echo "<select name=IdParent>" ;
  echo "<option value=1>Bewelcome Root</option>" ;
  for ($ii=0;$ii<count($TGroupList);$ii++) {
    echo "<option value=".$TGroupList[$ii]->id ;
    if ($TGroupList[$ii]->id==$IdParent) echo " selected" ;
    echo ">",$TGroupList[$ii]->Name,":",ww("Group_".$TGroupList[$ii]->Name) ;
    echo "</option>" ;

  }
  echo "</select>" ;
//  echo "<input type=text name=IdParent value=\"$IdParent\">";
  echo "</td>";

  echo "<tr><td width='30%'>Group name in English</td>";
  echo "<td align=left><textarea name=Group_ cols=60 rows=1>",$Group_,"</textarea></td>" ;
  echo "<tr><td>Group Description  (in English)</td>";
  echo "<td align=left><textarea name=GroupDesc_ cols=60 rows=5>",$GroupDesc_,"</textarea></td>" ;

  echo "<tr><td>Does this group is public ?</b></td>";
  echo "<td>";
  echo "\n<select name=Type>\n";
  echo "<option value=Public ";
  if ($Type == "Public")
    echo " selected ";
  echo ">Public</option>\n";
  echo "<option value=NeedAcceptance ";
  if ($Type == "NeedAcceptance")
    echo " selected ";
  echo ">NeedAcceptance</option>\n";
  echo " \n</select>\n";
  echo "</td>\n";

  echo "<tr><td>Optional forum entry to associate with the group (more info)</td><td><input type=text name=MoreInfo value=\"$MoreInfo\"></td>";
  echo "<tr><td>Optional picture to associate with the group (not yet available)</td><td><input type=text name=Picture value=\"$Picture\"></td>";

  echo "\n<tr><td colspan=2 align=center>";

  if ($IdGroup != 0)
    echo "<input type=submit id=submit name=submit value=\"update group\">";
  else
    echo "<input type=submit id=submit name=submit value=\"create group\">";

  echo "<input type=hidden name=action value=creategroup>";
  echo "</td>\n</table>\n";
  echo "</form>\n";
  echo "</center>";

  require_once "footer.php";
} // DisplayFormCreateGroups


// This display teh list of member in a Group and allow to update locations
function DisplayShowMembers($GroupName,$IdGroup,$TList, $Message) { // call the layout
  global $countmatch;
  global $title;
  $title = "Admin groups ".RightScope('Group');
  require_once "header.php";

  Menu1("", ww('MainPage')); // Displays the top menu

  Menu2("admin/admingroups.php", ww('MainPage')); // Displays the second menu

  if (HasRight("Group") >= 10) {
      $MenuAction  = "            <li><a href=\"admingroups.php?action=formcreategroup\">Create a new group</a></li>\n";
  }
  $MenuAction .= "            <li><a href=\"admingroups.php?action=updategroupscounter\">Update group counters</a></li>\n";
  $MenuAction .= "            <li><a href=\"admingroups.php?action=listgroups\">List Groups</a></li>\n";
  $MenuAction .= "            <li><a href=\"http://www.bevolunteer.org/wiki/AdminGroup_Tool:_HowTo\">Wiki How To</a></li>\n" ;

  DisplayHeaderShortUserContent($title);
  ShowLeftColumn($MenuAction,VolMenu());

  echo "    <div id=\"col3\"> \n";
  echo "      <div id=\"col3_content\" class=\"clearfix\"> \n";
  echo "        <div class=\"info\">\n";

  if ($Message != "") {
    echo "<h2>$Message</h2>";
  }
  $max = count($TList);
  $count = 0;

	$bgcolor="#ffff99" ;
	$previousUsername="" ;
  echo "<h3> Members in group ". $GroupName ."</h3>\n";
  echo "<table class=\"fixed\">\n";
  for ($ii = 0; $ii < $max; $ii++) {
    $rr = $TList[$ii];
		if ($rr->Username!=$previousUsername) {
			if ($bgcolor=="#ffff99") $bgcolor="#ffff55" ;
			else $bgcolor="#ffff99" ;
		}

    $count++;
    echo "<tr bgcolor=\"".$bgcolor."\">";
    echo "<td>", LinkWithUsername($rr->Username) ; 
		echo "<br>",$rr->CountryName,"(".$rr->IdCountry.")<br>",$rr->RegionName,"(".$rr->IdRegion.")<br>",$rr->CityName,"(".$rr->IdCity.") ";
		echo "</td><td>";
    if ($rr->Comment > 0)
      echo FindTrad($rr->Comment);
    echo "</td>\n";
    echo "<td>";
		if ($rr->Username!=$previousUsername) {
			$previousUsername=$rr->Username ;
    	echo "<br><form method=post action=admingroups.php>";
    	echo "<input type=hidden name=action value=\"add Location\">";
    	echo "<input type=hidden name=IdMemberShip value=", $rr->IdMemberShip, ">";
    	echo "IdLocation: <input type=text name=IdLocation value=\"\"> ";
    	echo "<input type=submit id=submit name=submit value=\"add Location\">";
    	echo "</form><br>";
		}
		if (!empty($rr->IdLocation)) {
    	echo "<form method=post action=admingroups.php> ";
    	echo "<input type=hidden name=action value=\"del Location\">";
    	echo "<input type=hidden name=IdMemberShip value=", $rr->IdMemberShip, ">";
    	echo " <input type=hidden name=IdLocation value=", $rr->IdLocation, "><br>",$rr->LocationName," ";
    	echo "<input type=submit id=submit name=submit value=\"delete\">";
    	echo "</form> ";
		}
    echo "</td>";
  }
  echo "<tr><td align=right>Total</td><td align=left>$count</td>";
  echo "\n</table><br>\n";

  echo "</center>";

  require_once "footer.php";
} // end of DisplayShowMembers
