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
function DisplayAdminView($username, $name, $description, $TDatas, $TDatasVol, $rright, $lastaction) {
  global $countmatch;
  global $title;
  global $AdminRightScope;

  require_once "header.php";
  Menu1("", $title); // Displays the top menu

  Menu2($_SERVER["PHP_SELF"], $title); // Displays the second menu

  $MenuAction  = "            <li><a href=\"".$_SERVER["PHP_SELF"]."\">Admin Rights</a></li>\n";
  $MenuAction .= "            <li><a href=\"".$_SERVER["PHP_SELF"]."?action=helplist\">Help</a></li>\n";
  $MenuAction .= "            <li><a href=\"".$_SERVER["PHP_SELF"]."?action=viewbyusername\">View by Username</a></li>\n";
  $MenuAction .= "            <li><a href=\"".$_SERVER["PHP_SELF"]."?action=viewbyright\">View by Right</a></li>\n";

  DisplayHeaderShortUserContent($title);
  ShowLeftColumn($MenuAction,VolMenu());

  echo "    <div id=\"col3\"> \n";
  echo "      <div id=\"col3_content\" class=\"clearfix\"> \n";
  echo "        <div class=\"info clearfix\">\n";

  if ($lastaction != "") {
    echo "$lastaction<br>";
  }
  echo "            <h2>Your Scope is for <strong>", $AdminRightScope, "</strong></h2>\n";

  $max = count($TDatasVol);
  $count = 0;

  echo "            <form method=\"post\" action=\"".$_SERVER["PHP_SELF"]."\">\n";
  echo "              <table class=\"fixed\">\n";
  echo "                <tr>\n";
  echo "                  <td class=\"label\">Username: </td>\n";
  echo "                  <td><input type=\"text\" name=\"username\" value=\"", $username, "\" /></td>\n";
  echo "                  <td>\n";
  echo "                    <input type=\"hidden\" name=\"action\" value=\"find\" />\n";
  echo "                    <input type=\"submit\" id=\"submit\" name=\"submit\" value=\"find\" />\n";
  echo "                  </td>\n";
  echo "                </tr>\n";
  echo "                <tr>\n";
  echo "                  <td class=\"label\">Right: </td>\n";
  echo "                  <td>\n";
  echo "                    <select name=\"Name\" >\n";
  $max = count($TDatas);
    if ($AdminRightScope == "\"All\"") {
    echo "                      <option value=\"\">-All-</option>\n";
  }
  for ($ii = 0; $ii < $max; $ii++) {
    echo "                      <option value=\"" . $TDatas[$ii]->Name . "\"";
    if ($TDatas[$ii]->Name == $name)
      echo " selected=\"selected\" ";
    echo ">", $TDatas[$ii]->Name;
    echo "</option>\n";
  }
  echo "                    </select>\n";
  echo "                  </td>\n";
  echo "                  <td align=\"left\" >";
  if ($description != "") {
    echo "<strong>", $name, "</strong> :<p class=\"grey\"\">";
    echo str_replace("\n", "<br>", $description);
    echo "</p>";
  }
  echo "                  </td>\n";
  echo "                </tr>\n";
  echo "              </table>\n";
  echo "            </form>\n";
  echo "            <hr />\n";

  $max = count($TDatasVol);
  for ($ii = 0; $ii < $max; $ii++) {
    $rr = $TDatasVol[$ii];
    $count++;
    echo "            <form method=\"post\" action=\"".$_SERVER["PHP_SELF"]."\">\n";
    echo "              <input type=\"hidden\" name=\"IdItemVolunteer\" value=\"", $rr->id, "\" />\n";
    echo "              <input type=\"hidden\" name=\"action\" value=\"update\" />\n";
    echo "              <input type=\"hidden\" name=\"username\" value=\"", $rr->Username, "\" />\n";
    echo "              <table class=\"full\">\n";
    if ($username == "") {
      echo "            <table class=\"full\">\n";
      echo "              <tr>\n";
      echo "                <td><strong>", LinkWithUsername($rr->Username), "</strong></td>\n";
    }
    echo "                <tr>\n";
    echo "                  <td class=\"label\">Right: </td>\n";
    echo "                  <td><input type=\"text\" name=\"Name\" readonly value=\"", $rr->Name, "\" /></td>\n";
    echo "                </tr>\n";
    echo "                <tr>\n";
    echo "                  <td class=\"label\">Level: </td>\n";
    echo "                  <td><input type=\"text\" name=\"Level\" value=\"", $rr->Level, "\"></td>\n";
    echo "                </tr>\n";
    echo "                <tr>\n";
    echo "                  <td class=\"label\">Scope: </td>\n";
    echo "                  <td><textarea name=\"Scope\" rows=\"1\" cols=\"70\">", $rr->Scope, "</textarea></td>\n";
    echo "                </tr>\n";
    echo "                <tr>\n";
    echo "                  <td class=\"label\">Comment: </td>\n";
    echo "                  <td><textarea name=\"Comment\" rows=\"3\" cols=\"70\">", $rr->Comment, "</textarea></td>\n";
    echo "                </tr>\n";
    echo "                <tr>\n";
    echo "                  <td colspan=\"3\" valign=\"center\" align=\"center\"><input type=\"submit\" id=\"submit\" name=\"submit\" value=\"update\" /> <input type=\"submit\" id=\"submit\" name=\"submit\" value=\"del\" /></td>\n";
    echo "                </tr>\n";
    echo "              </table>\n";
    echo "            </form>\n";
    if (HasRight("Right", $rr->Name)) {
      echo " <a href=\"" . $_SERVER["PHP_SELF"] . "?IdItemVolunteer=", $TDatasVol[$ii]->id, "\" onclick=\"return confirm('Your really want to delete right " . $rr->Name . " for " . $rr->Username . " ?');\">del</a>";
    }
    echo "            <hr />\n";
    echo "            <br />\n";
  }

  if ($username != "") { // If a username is selected propose to add him a right
    echo "            <form method=post  method=\"".$_SERVER["PHP_SELF"]."\">";
    echo "              <table class=\"admin\" width=80%>\n";
    echo "                <tr>\n";
    echo "                  <td class=\"label\">Username: </td>\n";
    echo "                  <td><input type=text readonly name=username value=\"", $username, "\"></td>\n ";
    echo "                </tr>\n";
    echo "                <tr>\n";
    echo "                  <td class=\"label\">Right: </td>\n";
    echo "                  <td>\n";
    $max = count($TDatas);
    echo "                    <select name=Name>\n";
    for ($ii = 0; $ii < $max; $ii++) {
      echo "                      <option value=\"", $TDatas[$ii]->Name, "\">", $TDatas[$ii]->Name, "</option>\n";
    }
    echo "                    </select></td>\n";
    echo "                </tr>\n";
    echo "                <tr>\n";
    echo "                  <td class=\"label\">Level: </td>\n";
    echo "                  <td><input type=text name=Level></td>\n";
    echo "                </tr>\n";
    echo "                <tr>\n";
    echo "                  <td class=\"label\">Scope: </td>\n";
    echo "                  <td><textarea name=Scope rows=1 cols=70></textarea></td>\n";
    echo "                </tr>\n";
    echo "                <tr>\n";
    echo "                  <td class=\"label\">Comment: </td>\n";
    echo "                  <td><textarea name=Comment rows=3 cols=70></textarea></td>\n";
    echo "                </tr>\n";
    echo "                <tr>\n";
    echo "                  <td colspan=\"3\" valign=center align=center>\n";
    echo "                    <input type=hidden name=action value=add>\n";
    echo "                    <input type=submit id=submit name=submit value=add>\n";
    echo "                  </td>\n";
    echo "                </tr>\n";
    echo "              </table>\n";
    echo "            </form>\n";
  }
  echo "          </div> <!-- info -->\n";
  require_once "footer.php";
} // DisplayAdmin($username,$name,$TDatas,$TDatasVol,$rright,$lastaction,$scope) {

function DisplayHelpRights($TDatas,$AdminRightScope) {
  global $countmatch;
  global $title;
  global $AdminRightScope;
  $styles = array( 'highlight', 'blank' );

  require_once "header.php";
  Menu1("", $title); // Displays the top menu

  Menu2($_SERVER["PHP_SELF"], $title); // Displays the second menu

  $MenuAction  = "            <li><a href=\"".$_SERVER["PHP_SELF"]."\">Admin Rights</a></li>\n";
  $MenuAction .= "            <li><a href=\"".$_SERVER["PHP_SELF"]."?action=helplist\">Help</a></li>\n";
  $MenuAction .= "            <li><a href=\"".$_SERVER["PHP_SELF"]."?action=viewbyusername\">View by Username</a></li>\n";
  $MenuAction .= "            <li><a href=\"".$_SERVER["PHP_SELF"]."?action=viewbyright\">View by Right</a></li>\n";

  DisplayHeaderShortUserContent($title);
  ShowLeftColumn($MenuAction,VolMenu());

  echo "    <div id=\"col3\"> \n";
  echo "      <div id=\"col3_content\" class=\"clearfix\"> \n";
  echo "        <div class=\"info clearfix\">\n";

  // TODO: check the meaning of the next row. $lastaction is not defined
  if ($lastaction != "") {
    echo "$lastaction<br>";
  }
  echo "<h2>Your Scope is for <strong>", $AdminRightScope, "</strong> </h2>\n";


  echo "<table class=\"full\">\n";
  echo "<tr><th>Right</th><th>Description</th></tr>\n";
  $max = count($TDatas);
  for ($ii = 0; $ii < $max; $ii++) {
    echo "<tr class=\"",$styles[$ii%2],"\"><td><strong>",$TDatas[$ii]->Name,"</strong></td><td>",str_replace("\n","<br />",$TDatas[$ii]->Description),"</td></tr>";
  }
  echo "</table>\n";

  echo "</div> <!-- info --> \n";
  require_once "footer.php";
} // DisplayHelpRights()

function DisplayRightsList($TDatas,$AdminRightScope,$ByRight=true) {
  global $countmatch;
  global $title;
  global $AdminRightScope;

  require_once "header.php";
  Menu1("", $title); // Displays the top menu

  Menu2($_SERVER["PHP_SELF"], $title); // Displays the second menu

  $MenuAction  = "            <li><a href=\"".$_SERVER["PHP_SELF"]."\">Admin Rights</a></li>\n";
  $MenuAction .= "            <li><a href=\"".$_SERVER["PHP_SELF"]."?action=helplist\">Help</a></li>\n";
  $MenuAction .= "            <li><a href=\"".$_SERVER["PHP_SELF"]."?action=viewbyusername\">View by Username</a></li>\n";
  $MenuAction .= "            <li><a href=\"".$_SERVER["PHP_SELF"]."?action=viewbyright\">View by Right</a></li>\n";

  DisplayHeaderShortUserContent($title);
  ShowLeftColumn($MenuAction,VolMenu());

  echo "    <div id=\"col3\"> \n";
  echo "      <div id=\"col3_content\" class=\"clearfix\"> \n";
  echo "        <div class=\"info clearfix\">\n";

  // TODO: check the meaning of the next row. $lastaction is not defined
  if ($lastaction != "") {
    echo "$lastaction<br>";
  }
  echo "<h2>Your Scope is for <strong>", $AdminRightScope, "</strong> </h2>";


  if ($ByRight) {
     echo "<h4> List of rights by rights</h4>" ;
  }
  else {
     echo "<h4> List of rights by username</h4>" ;
  }

  echo "<table class=\"full\"\n";
  echo "<tr><th>Picture</th><th>Username</th><th>Last Login</th><th>#Comment</th><th>Right</th><th>Scope</th></tr>" ;
  $max = count($TDatas);
  $bgcolor="highlight" ;
  for ($ii = 0; $ii < $max; $ii++) {
       $rr=$TDatas[$ii] ;
       echo "<tr" ;
       if ($ii>0) {
           if ($ByRight) {
              if ($TDatas[$ii-1]->TopicName!=$rr->TopicName) {
               if ($bgcolor=="highlight") $bgcolor="blank";
               else $bgcolor="highlight" ;
             }
           }
           else {
              if ($TDatas[$ii-1]->Username!=$rr->Username) {
               if ($bgcolor=="highlight") $bgcolor="blank";
               else $bgcolor="highlight" ;
             }
           }
       }
       echo " class=\"".$bgcolor."\">\n";
       echo "<td>",LinkWithPicture($rr->Username,$rr->photo),"</td>\n";
       echo "<td><a href=\"".$_SERVER["PHP_SELF"]."?username=".$rr->Username."\">", $rr->Username,"</a> (",$rr->Status,"/",$rr->CountryName,")</td>" ;
       echo "<td>",$rr->LastLogin,"</td><td>",$rr->NbComment,"</td><td>" ;
       if ($rr->Level>0) {
           echo "<a href=\"".$_SERVER["PHP_SELF"]."?Name=".$rr->TopicName."\">", $rr->TopicName,"</a>" ;
       }
       else {
           echo "<strike>",$rr->TopicName,"</strike>" ;
       }
       echo "</td>" ;
       echo "<td>",$rr->Scope,"</td></tr>" ;
  }
  echo "</table>\n";
  echo "</div> <!-- info -->\n";
  require_once "footer.php";
} // DisplayRightsList()

?>
