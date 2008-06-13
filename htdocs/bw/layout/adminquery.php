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


// This form displays the list of the possible queries for the current user 
function DisplayMyQueryList($TList,$Message="") {

  global $title;
  $title = "Queries for BW volunteers";
  require_once "header.php";

  Menu1("", ww('MainPage')); // Displays the top menu

  Menu2("admin/adminquery.php", ww('MainPage')); // Displays the second menu

  if (HasRight("SqlForVolunteers") >= 10) {
      $MenuAction  = "            <li><a href=\"adminquery.php\">admin query</a></li>\n";
  }
//  $MenuAction .= "            <li><a href=\"admingroups.php?action=updategroupscounter\">Update group counters</a></li>\n";

  DisplayHeaderShortUserContent($title);
  ShowLeftColumn($MenuAction,VolMenu());

  echo "    <div id=\"col3\"> \n";
  echo "      <div id=\"col3_content\" class=\"clearfix\"> \n";
  echo "        <div class=\"info\">\n";

  if (!empty($Message)) {
    echo "<h2>$Message</h2>";
  }

		echo "<p><table>\n" ;
		$max=count($TList) ;
		echo "<tr><th colspan=4>you have ",$max," possible queries</th></tr>\n" ;
		echo "<tr align=left><th>Query</th><th>param1</th><th>param2</th><th>action</th></tr>\n" ;
		for ($ii=0;$ii<$max;$ii++) {
				$rr=$TList[$ii] ;
				echo "<form method=\"post\" action=\"adminquery.php\">" ;
			  echo "<input type=\"hidden\" name=\"IdQuery\" value=\"\">" ;
				echo "<tr align=left valign=center>" ;
				echo "<td>",$rr->Name,"</td>" ;

				if (!empty($rr->param1)) {
					 	 echo "<td>" ;
					 	 echo $rr->param1,":" ;
						 echo "<input type=\"texte\" name=\"param1\">" ;
						 echo "</td>" ;
				}
				else {
					 	 echo "<td bgcolor=gray>" ;
						 echo "-<input type=\"hidden\" name=\"param1\">" ;
						 echo "</td>" ;
				}

				if (!empty($rr->param2)) {
					 	 echo "<td>" ;
					 	 echo $rr->param2,":" ;
						 echo "-<input type=\"texte\" name=\"param2\">" ;
						 echo "</td>" ;
				}
				else {
					 	 echo "<td bgcolor=gray>" ;
						 echo "-<input type=\"hidden\" name=\"param2\">" ;
						 echo "</td>" ;
				}
				
				echo "<td>" ;
				echo "<input type=\"submit\" name=\"action\" value=\"execute\">" ;
				if (HasRight("Rights","SqlForVolunteers")) {
					 echo " <input type=\"submit\" name=\"action\" value=\"MANAGE RIGHT\">" ;
				}
				echo "</td>" ;
				echo "</tr>\n" ;
		}
		echo "</table></p>\n" ;

  echo "</center>";
  require_once "footer.php";

} // end of DisplayMyQueryList


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
    echo "<td>", ww("Group_" . $rr->GroupName), "</td>";
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

// This function propose to create a group
function DisplayFormCreateGroups($IdGroup, $Name = "", $IdParent = 0, $Type = "", $HasMember = "", $TGroupList,$Group_="",$GroupDesc_="",$MoreInfo,$Picture) {
  global $title;
  $title = "Create a new group";
  require_once "header.php";

  Menu1("", ww('MainPage')); // Displays the top menu

  Menu2("admin/admingroups.php", ww('MainPage')); // Displays the second menu

  if (HasRight("Group") >= 10) {
      $MenuAction  = "            <li><a href=\"admingroups.php?action=formcreategroup\">Create a new group</a></li>\n";
  }
  $MenuAction .= "            <li><a href=\"admingroups.php?action=updategroupscounter\">Update group counters</a></li>\n";

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
  echo "<tr><td>Does this group has members ?</td>";
  echo "<td>";
  echo "\n<select name=HasMember>\n";
  echo "<option value=HasMember ";
  if ($HasMember == "HasMember")
    echo " selected ";
  echo ">HasMember</option>\n";
  echo "<option value=HasNotMember ";
  if ($HasMember == "HasNotMember")
    echo " selected ";
  echo ">HasNotMember</option>\n";
  echo " \n</select>\n";
  echo "</td>\n";

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
