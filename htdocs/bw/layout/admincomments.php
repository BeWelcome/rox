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

function ShowList($TData) {
  global $_SYSHCVOL;
  $maxTData = count($TData);
  $count = 0;
  $tt = $_SYSHCVOL['LenghtComments'];
  $max = count($tt);

    for ($iData = 0; $iData < $maxTData; $iData++) {
    $c = $TData[$iData];
    $count++;
    echo "          <div class=\"admincomment\">\n";
    echo "            <h3> comment from ", LinkWithUsername($c->UsernameWriterMember), " to ", LinkWithUsername($c->UsernameReceiverMember), "</h3>\n";
    echo "            <p><strong>", $c->AdminAction, "</strong></p>\n";
    echo "            <form method=\"post\" action=\"admincomments.php\">\n";
    echo "              <div class=\"subcolumns\">\n";
    echo "                <div class=\"c50l\">\n";
    echo "                  <div class=\"subcl\">\n";

    $QualityStyle = "background-color:lightgreen;";
    if ($c->Quality == "Bad") {
      $QualityStyle = "background-color:red;color:white;";
    }
    if ($c->Quality == "Neutral") {
      $QualityStyle = "background-color:lightgray;";
    }
    echo "                    <p>\n";
    echo "                      <select name=\"Quality\" style=\"", $QualityStyle, "\">\n";
    echo "                        <option value=\"Neutral\" ";
    if ($c->Quality == "Neutral")
      echo " selected=\"selected\"";
    echo ">";
    echo ww("CommentQuality_Neutral"), "</option>\n";

    echo "                        <option value=\"Good\"";
    if ($c->Quality == "Good")
      echo " selected=\"selected\" ";
    echo ">", ww("CommentQuality_Good"), "</option>\n";

    echo "                        <option value=\"Bad\"";
    if ($c->Quality == "Bad")
      echo " selected=\"selected\" ";
    echo ">", ww("CommentQuality_Bad"), "</option>\n";
    echo "                      </select>\n";
    echo "                    </p>\n";

    $ttLenght = explode(",", $c->Lenght);
    echo "                    <ul>\n";
    for ($ii = 0; $ii < $max; $ii++) {
      echo "                      <li><input type=\"checkbox\" name=\"Comment_" . $tt[$ii] . "\"";
      if (in_array($tt[$ii], $ttLenght))
        echo " checked=\"checked\" ";
      echo " />";
      echo "&nbsp;", ww("Comment_" . $tt[$ii]), "</li>\n";
    }
    echo "                    </ul>\n";
    echo "                  </div> <!-- subcl -->\n";
    echo "                </div> <!-- c50l -->\n";
    echo "                <div class=\"c50r\">\n";
    echo "                  <div class=\"subcr\">\n";
    echo "                    <ul class=\"linklist\">\n";
    if ($c->AdminComment != "Checked")
      echo "                      <li><a href=\"".bwlink("admin/admincomments.php?IdComment=". $c->id. "&amp;action=Checked")."\">Checked</a></li>\n";
    if (($c->AdminComment != "Checked") and (HasRight("Comments", "AdminComment")))
      echo "                      <li><a href=\"".bwlink("admin/admincomments.php?IdComment=". $c->id. "&amp;action=AdminCommentMustCheck")."\">Admin Comment Must Check</a></li>\n";
    if (($c->AdminComment != "Checked") and (HasRight("Comments", "AdminAbuser")))
      echo "                      <li><a href=\"".bwlink("admin/admincomments.php?IdComment=". $c->id. "&amp;action=AdminAbuserMustCheck")."\">Admin Abuser Must Check</a></li>\n";
    if (($c->AdminComment != "Checked") and (HasRight("Comments", "DeleteComment")))
      echo "                      <li><a href=\"".bwlink("admin/admincomments.php?IdComment=". $c->id. "&amp;action=del\" onclick=\"return('Confirm delete ?');")."\">del</a></li>\n";
    echo "                      <li><a href=\"".bwlink("admin/admincomments.php?FromIdMember=" . $c->UsernameWriterMember )."&amp;action=All\">Other comments written by ", $c->UsernameWriterMember, "</a></li>\n";
    echo "                      <li><a href=\"".bwlink("admin/admincomments.php?ToIdMember=" . $c->UsernameReceiverMember )."&amp;action=All\">Other comments written about ", $c->UsernameReceiverMember, "</a></li>\n";
    echo "                      <li><a href=\"".bwlink("messages/compose/". $c->UsernameWriterMember, true)."\">contact writer (". $c->UsernameWriterMember.")</a></li>\n";
    echo "                      <li><a href=\"".bwlink("messages/compose/". $c->UsernameReceiverMember, true)."\">contact receiver (". $c->UsernameReceiverMember.")</a></li>\n";
    echo "                    </ul>\n";
    echo "                  </div> <!-- subcr -->\n";
    echo "                </div> <!-- c50r -->\n";
    echo "              </div> <!-- subcolumns -->\n";
    echo "              <h4>Where ?</h4>\n";
    echo "              <p><textarea name=\"TextWhere\" cols=\"70\" rows=\"5\">", $c->TextWhere, "</textarea></p>\n";
    echo "              <h4>Comment:</h4>\n";
    echo "              <p><textarea name=\"TextFree\" cols=\"70\" rows=\"8\">", $c->TextFree, "</textarea></p>\n";
    echo "              <p class=\"center\">\n";
    echo "                <input type=\"hidden\" value=\"" . $c->id . "\" name=\"IdComment\" />\n";
    echo "                <input type=\"hidden\" value=\"" . $IdMember . "\" name=cid />\n";
    echo "                <input type=\"hidden\" name=\"action\" value=\"update\" />\n";
    echo "                <input type=\"submit\" id=\"submit\" value=\"update\" />\n";
    echo "              </p>\n";
    echo "             </form>\n";
    echo "            </div> <!-- admincomment -->\n";
  }
  echo "            <p><strong>Total number of comments:</strong> ", $count, "</p>\n";
  echo "        </div> <!-- info -->\n";
} // end of ShowList

function DisplayAdminComments($TData, $lastaction = "") {
  global $countmatch;
  global $title;
  $title = "Admin Comments";
  global $AdminCommentsScope;

  require_once "header.php";

  Menu1("", ww('MainPage')); // Displays the top menu

  Menu2("admincomments.php", ww('MainPage')); // Displays the second menu

  if (HasRight("Comments", "AdminAbuser"))
  $MenuAction  = "            <li><a href=\"".bwlink("admin/admincomments.php?action=AdminAbuser")."\">Comments to check by Admin Abuser</a></li>\n";
  $MenuAction .= "            <li><a href=\"".bwlink("admin/admincomments.php?action=All")."\">All Comments </a></li>\n";

  DisplayHeaderShortUserContent($title . " : " . $lastaction);
  ShowLeftColumn($MenuAction,VolMenu());

  echo "    <div id=\"col3\"> \n";
  echo "      <div id=\"col3_content\" class=\"clearfix\"> \n";
  echo "        <div class=\"info clearfix\">\n";

  echo "          <h2> your Scope :", $AdminCommentsScope, "</h2>\n";

  ShowList($TData);

  require_once "footer.php";
} // end of DisplayAdminAccepter($Taccepted,$Tmailchecking,$Tpending)
