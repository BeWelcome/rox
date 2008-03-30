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
function DisplayMessages($TMess, $lastaction = "",$IdSender="") {
    global $countmatch;
    global $title;
    $title = "Admin Spam";
    require_once "header.php";

    Menu1(); // Displays the top menu

    Menu2($_SERVER["PHP_SELF"]);

    $rr=LoadRow("select count(*) as cnt from messages,members as mSender,members as mReceiver where mSender.id=IdSender and messages.SpamInfo='SpamSayMember' and mReceiver.id=IdReceiver and mSender.Status='Active'");
//           "select count(*) as cnt from messages,members as mSender where mSender.id=IdSender and messages.SpamInfo='SpamSayMember' and mSender.Status='Active'"

    $MenuAction  = "            <li><a href=\"".$_SERVER["PHP_SELF"]."\">Admin Spam</a></li>\n";
    $MenuAction .= "            <li><a href=\"".$_SERVER["PHP_SELF"]."?action=PendingSpammers\">Pending Spammers</a></li>\n";
    $MenuAction .= "            <li><a href=\"".$_SERVER["PHP_SELF"]."?action=viewSpamSayMember\">Spam reported (".$rr->cnt.")</a></li>\n";

    DisplayHeaderShortUserContent( $title ,$MenuAction,"");
    ShowLeftColumn($MenuAction);

    $max = count($TMess);
    $count = 0;

    echo "    <div id=\"col3\"> \n";
    echo "      <div id=\"col3_content\" class=\"clearfix\"> \n";
    echo "        <div class=\"info\">\n";

    if ($lastaction != "") {
        echo "<p class=\"note center\"> $lastaction </p>";
    }

    echo "          <form method=post action=adminchecker.php>\n";
    echo "          <input type=hidden name=action value=check />\n";
    echo "          <table class=\"fixed\">\n";
    if ($max == 0) {
        echo "            <tr><th>No pending messages to check</th></tr>\n";
    } else {
        echo "            <tr><th>Sender<br />Receiver</th><th>Message</th><th>Action</th><th>SpamInfo</th></tr>\n";
    }


    for ($ii = 0; $ii < $max; $ii++) {
        $rr = $TMess[$ii];
        $count++;
        echo "            <tr><td>";
        echo "<strong>", LinkWithUsername($rr->Username_sender), "</strong>";
        echo "<a href=\"",$_SERVER["PHP_SELF"],"?action=SpamReportsFor&IdSender=$rr->Username_sender \"> (view all) </a>\n";
        echo "<br />";
        echo "<strong>", LinkWithUsername($rr->Username_receiver), "</strong>";
        echo "</td>";
        echo "<td>";
        echo "(",fsince($rr->created)," - ",localdate($rr->created),")<br />";
        if ($rr->CheckerComment!="") echo "<strong>",$rr->CheckerComment,"</strong><br />\n";
        echo "<textarea cols=\"40\" rows=\"7\" readonly>";
        echo $rr->Message;
        echo "</textarea>";
        echo "</td>";
        echo "<td align=\"left\">";
        echo "<input type=hidden name=IdMess_" . $ii . "  value=" . $rr->id . " />";
        if ($rr->MessageStatus=='ToCheck') {
           echo "<input type=checkbox name=Approve_" . $ii ;
           echo " /> Approve <br />";
           echo "<input type=checkbox name=Freeze_" . $ii ;
           echo " /> Freeze <br />";
        }
        else {
           echo "Status=<strong>".$rr->Status."</strong><br />" ;
        }
        $checked = "";
        $SpamInfo = "";

        if ($rr->SpamInfo != "NotSpam") { // use to pre-tick Spam
            $checked = "checked";
        }
        echo "<input type=checkbox name=Mark_Spam_" . $ii . " $checked  /> Mark Spam";
        if ($rr->SpamInfo=="SpamSayMember") echo "<br /><input type=checkbox name=Processed_" . $ii . "  /> I have processed it";
        echo "</td>";
        echo "<td>";
        echo $rr->SpamInfo;
        echo "</td></tr>\n";
    }
    echo "            <tr><td colspan=\"5\" align=\"center\"><input type=\"submit\" name=\"submit\" value=\"submit\" />";
    if ($IdSender!="") {
       echo "<input type=hidden name=IdSender value=".$IdSender."  />" ;
    }

    echo "</td></tr>\n";
    echo "          </table>\n";
    echo "          </form>\n";

    echo "          <p><a href=\"",$_SERVER["PHP_SELF"],"?action=view \">view 20 last messages</a></p>\n" ;
    echo "        </div>\n" ;

    require_once "footer.php";

} // DisplayMessages()

function DisplayPendingMayBeSpammers($TMess, $lastaction = "") {
    global $countmatch;
    global $title;
    $title = "Pending May Be Spammers";
    require_once "header.php";

    Menu1(); // Displays the top menu
    Menu2($_SERVER["PHP_SELF"]);

    $rr=LoadRow("select count(*) as cnt from messages,members as mSender,members as mReceiver where mSender.id=IdSender and messages.SpamInfo='SpamSayMember' and mReceiver.id=IdReceiver and mSender.Status='Active'");
//           "select count(*) as cnt from messages,members as mSender where mSender.id=IdSender and messages.SpamInfo='SpamSayMember' and mSender.Status='Active'"

    $MenuAction  = "            <li><a href=\"".$_SERVER["PHP_SELF"]."\">Admin Spam</a></li>\n";
    $MenuAction .= "            <li><a href=\"".$_SERVER["PHP_SELF"]."?action=PendingSpammers\">Pending Spammers</a></li>\n";
    $MenuAction .= "            <li><a href=\"".$_SERVER["PHP_SELF"]."?action=viewSpamSayMember\">Spam reported (".$rr->cnt.")</a></li>\n";

    DisplayHeaderShortUserContent($title);
    ShowLeftColumn($MenuAction);

    echo "    <div id=\"col3\"> \n";
    echo "      <div id=\"col3_content\" class=\"clearfix\"> \n";
    echo "          <div class=\"info\">\n";

    if ($lastaction != "") {
        echo "<p class=\"note center\">$lastaction</p>";
    }

    $max = count($TMess);
    $count = 0;

    echo "<center>\n";
    echo "<table class=\"fixed\">\n";
    if ($max == 0) {
        echo "<tr><th align=center>No pending messages to check</th>";
    } else {
        echo "\n<tr><th>Sender</th><th>Nb Pending</th><th>Action</th><th>SpamInfo</th>";
    }

    for ($ii = 0; $ii < $max; $ii++) {
        $rr = $TMess[$ii];
        $count++;
        echo "<tr>";
        echo "<td>";
        echo LinkWithUsername($rr->Username_sender);
        echo "</td>";
        echo "<td>";
        echo "$rr->cnt";
        echo "</td>";
        echo "<td align=left>";
        echo "<a href=\"",$_SERVER["PHP_SELF"],"?action=view&IdSender=$rr->IdSender\">view spam(?) messages</a>" ;
        echo "</td>";
    }
    echo "\n</table><br>\n";


    echo "</center>";
    echo "</div>" ;

    require_once "footer.php";

} // DisplayPendingMayBeSpammers
