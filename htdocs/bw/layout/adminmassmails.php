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

// This just display the list of massmail
function DisplayAdminMassMailsList($TData) {
  global $title;
  $title = "Admin Mass Mails";
  require_once "header.php";

  Menu1("", ww('MainPage')); // Displays the top menu

  Menu2("admin/adminmassmails.php", ww('MainPage')); // Displays the second menu

  $MenuAction  = "            <li><a href=\"adminmassmails.php\">Admin Massmails</a></li>\n";
  $MenuAction .= "            <li><a href=\"adminmassmails.php?action=createbroadcast\">Create new broadcast</a></li>\n";
  if (HasRight("MassMail","Send")) { // if has right to trig
    $MenuAction .= "            <li><a href=\"adminmassmails.php?action=ShowPendingTrigs\">Trigger mass mails</a></li>\n";
  }

  DisplayHeaderShortUserContent("Admin Mails - Broadcast Messages","");
  ShowLeftColumn($MenuAction,VolMenu());

  echo "    <div id=\"col3\"> \n";
  echo "      <div id=\"col3_content\" class=\"clearfix\"> \n";
  echo "        <div class=\"info clearfix\">\n";

  $max = count($TData);

  for ($ii=0;$ii<$max;$ii++) {
      echo "<br \>&nbsp;&nbsp;&nbsp;&nbsp;* <font color=green>",$TData[$ii]->Name,"</font> (",$TData[$ii]->Status,") <a href=\"adminmassmails.php?action=edit&IdBroadCast=".$TData[$ii]->id."\">edit</a> | <a href=\"adminmassmails.php?action=prepareenque&IdBroadCast=".$TData[$ii]->id."\">prepare & enqueue</a><br />" ;
  }

  echo "</div> <!-- info -->\n";

  require_once "footer.php";
} // end of DisplayAdminMassMailsList

// This prepare the enqueuing according to criteria
function DisplayAdminMassToApprove($ToApprove) {
  global $title;
  $title = "Admin Mass Mails";
  require_once "header.php";

  Menu1("", ww('MainPage')); // Displays the top menu

  Menu2("admin/adminmassmails.php", ww('MainPage')); // Displays the second menu

  $MenuAction  = "            <li><a href=\"adminmassmails.php\">Admin Massmails</a></li>\n";
  $MenuAction .= "            <li><a href=\"adminmassmails.php?action=createbroadcast\">Create new broadcast</a></li>\n";
  if (HasRight("MassMail","Send")) { // if has right to trig
    $MenuAction .= "            <li><a href=\"adminmassmails.php?action=ShowPendingTrigs\">Trigger mass mails</a></li>\n";
  }

  DisplayHeaderShortUserContent( "Admin Mails - Broadcast Messages", "");
  ShowLeftColumn($MenuAction,VolMenu());

  echo "    <div id=\"col3\"> \n";
  echo "      <div id=\"col3_content\" class=\"clearfix\"> \n";
  echo "        <div class=\"info clearfix\">\n";

  if (HasRight("MassMail","Send")) { // if has right to trig
    $max=count($ToApprove) ;
    echo "Pending messages to Send $max<br />\n" ;
    for ($ii=0;$ii<$max;$ii++) {
      $m=$ToApprove[$ii] ;
      echo "<br />&nbsp;&nbsp;&nbsp;&nbsp;* <a href=\"adminmassmails.php?action=Trigger&IdBroadCast=$m->IdBroadcast&Name=$m->Name"."\">Trigger ",$m->Name,"(",$m->cnt,")</a><br />\n" ;
    }
  }

  echo "</div> <!-- info -->\n";

  require_once "footer.php";

} // end of DisplayAdminMassToApprove

// This prepare the enqueing according to criteria
function DisplayAdminMassprepareenque($rBroadCast,$TGroupList,$TCountries,$TData,$count=0,$countnonews=0,$query="") {
  global $title;
  $title = "Admin Mass Mails";
  require_once "header.php";

  Menu1("", ww('MainPage')); // Displays the top menu

  Menu2("admin/adminmassmails.php", ww('MainPage')); // Displays the second menu

  $MenuAction  = "            <li><a href=\"adminmassmails.php\">Admin Massmails</a></li>\n";
  $MenuAction .= "            <li><a href=\"adminmassmails.php?action=createbroadcast\">Create new broadcast</a></li>\n";

  if (HasRight("MassMail","Send")) { // if has right to trig
    $MenuAction .= "            <li><a href=\"adminmassmails.php?action=ShowPendingTrigs\">Trigger mass mails</a></li>\n";
  }


  DisplayHeaderShortUserContent( "Admin Mails - Broadcast Messages", "");
  ShowLeftColumn($MenuAction,VolMenu());

  $Name=$rBroadCast->Name ;
  $IdGroup=GetParam("IdGroup",0) ;
  $CountryIsoCode=GetParam("CountryIsoCode",0) ;

  echo "    <div id=\"col3\"> \n";
  echo "      <div id=\"col3_content\" class=\"clearfix\"> \n";
  echo "        <div class=\"info clearfix\">\n";

  echo "<h2>For broadcast <b>",$Name,"</b></h2>" ;
  if ($count>0) {
     echo "<p class=\"note\"> $count enqueued messages !<br /><i>$countnonews will not receive the mail because of their preference</i></p>" ;
  }

  $BroadCast_Title_ = getBroadCastElement("BroadCast_Title_" . $Name, 0);
  $BroadCast_Body_ = getBroadCastElement("BroadCast_Body_" . $Name, 0);

  $rr=LoadRow("select * from words where code='BroadCast_Title_".$Name."' and IdLanguage=0") ;
  if (isset($rr->Description)) {
      $Description=$rr->Description ;
  }
  else {
      $Description="" ;
  }

  echo "<h3>", nl2br($BroadCast_Title_) ,"</h3>" ;
  echo "<p>", nl2br($BroadCast_Body_) ,"</p>" ;

  echo "<br /><form method=\"post\" action=\"adminmassmails.php\" name=\"adminmassmails\" class=\"yform full\">\n" ;
  echo "<input type=\"hidden\" Name=\"IdBroadCast\" value=".GetParam("IdBroadCast",0).">\n" ;
  echo "<h3> Filtering the scope of the mass mail</h3>" ;
  echo "<div class=\"type-text\">";
  echo "<label for=\"Usernames\">Restrict to some members (ex : lupochen;kiwiflave;jeanyves)</label>" ;
  echo "<input type=\"text\" id=\"Usernames\" name=\"Usernames\" value=\"".GetStrParam("Usernames",""),"\" />\n" ;
  echo "</div>";
  echo "<div class=\"type-select\">";
  echo "<label for=\"CountryIsoCode\">Choose a country</label>" ;
  echo "<select id=\"CountryIsoCode\" name=\"CountryIsoCode\">" ;
  echo "<option value=\"0\">All countries</option>" ;
  for ($ii=0;$ii<count($TCountries);$ii++) {
    echo "<option value=\"",$TCountries[$ii]->isoCode.'"' ;
    if (strcmp($TCountries[$ii]->isoCode,$CountryIsoCode) === 0) echo " selected";
    echo ">",$TCountries[$ii]->Name ;
    echo "</option>" ;

  }
  echo "</select>\n" ;
  echo "</div>";
  echo "<div class=\"type-select\">";
  echo "<label for=\"IdGroup\">Choose a group</label>" ;
  echo "<select id=\"IdGroup\" name=\"IdGroup\">" ;
  echo "<option value=\"0\">All groups</option>" ;
  for ($ii=0;$ii<count($TGroupList);$ii++) {
    echo "<option value=",$TGroupList[$ii]->id ;
    if ($TGroupList[$ii]->id==$IdGroup) echo " selected" ;
    echo ">",$TGroupList[$ii]->Name,":",$TGroupList[$ii]->Name ;
    echo "</option>" ;

  }
  echo "</select>\n" ;
  echo "</div>";
  echo "<div class=\"type-text\">";
  echo "<label for =\"MemberStatus\">Member with status</label>";
  echo "<input type=\"text\" id=\"MemberStatus\" name=\"MemberStatus\" value=\"".GetStrParam("MemberStatus","Active")."\" />\n" ;
  echo "</div>";

  echo '<div class="type-text">';
  echo '<label for="Limit">Maximum number of members (i.e. 100)</label>';
  echo '<input type="text" id="limit" name="limit" value="' . GetStrParam("limit", "") . '" />';
  echo '</div>';

  if (GetStrParam("random_order", "") == "on") {
    $random_order_checked = ' checked="checked"';
  } else {
    $random_order_checked = '';
  }
  echo '<div class="type-check">';
  echo '<p>';
  echo '<input type="checkbox"' . $random_order_checked . ' id="random_order" name="random_order" /> ';
  echo '<label for="random_order">Select random members</label>';
  echo '<br>Note: If this option is checked the recipients list below is only an example and does not reflect the list of members the mail will actually be sent to. Members will be randomly selected again when pressing "enqueue".';
  echo '</p>';
  echo '</div>';

  if (HasRight('MassMail',"test")) {
    if (GetStrParam("hide_recipients", "") == "on") {
      $checked = ' checked="checked"';
    } else {
      $checked = '';
    }
    echo '<div class="type-check">';
    echo '<p>';
    echo '<input type="checkbox"' . $checked . ' id="hide_recipients" name="hide_recipients" /> ';
    echo '<label for="hide_recipients">Hide recipients list</label>';
    echo '</p>';
    echo '</div>';
    echo '<p>';
    echo '<input type="submit" class="button" name="action" value="test" />';
    echo ' (Shows number of matching members and list of recipients)';
    echo '</p>';
  }



// if it was a test action display the result build from previous filtering
  if (GetStrParam("action")=="test") {
     $max=count($TData) ;
     echo "<h3>This newsletter will be sent to $max members</h3>\n" ;
    if (GetStrParam("hide_recipients", "") != "on") {
     echo "<table>\n"  ;
     echo "<tr align=left><th>Username</th><th>country</th>" ;
     if (IsAdmin()) echo "<th>email</th>" ;
     echo "<th>Status</th><th>Will try in</th></tr>" ;
     for ($ii=0;$ii<$max;$ii++) {
          $m=$TData[$ii] ;
          echo "<tr class=\"highlight\">" ;
          echo "<td>",$m->Username,"</td>" ;
          echo "<td>",getcountrynamebycode($m->isoCode),"</td>" ;
         if (IsAdmin()) echo "<td>",GetEmail($m->id),"</td>" ;
          echo "<td>",$m->Status,"</td>" ;
                    $iLang=GetDefaultLanguage($m->id);
          $PrefLanguageName=LanguageName($iLang) ;
          echo "<td>",$PrefLanguageName,"</td>" ;

          echo "</tr>\n" ;
         echo "<tr>" ;
         echo "<td colspan=5 class=\"blank\">" ;
         echo getBroadCastElement("BroadCast_Title_".$Name,$iLang, $m->Username),"<br />" ;
         echo getBroadCastElement("BroadCast_Body_".$Name,$iLang,$m->Username),"<br />" ;
         echo "</td>" ;
         echo "</tr>" ;
     }
     echo "</table>\n" ;
    }
  }
  if (HasRight('MassMail',"enqueue")) {
     echo "<div class=\"note\">";
     echo "<div class=\"type-check\">";
     echo "<input type=\"checkbox\" id=\"enqueuetick\"  name=\"enqueuetick\" />";
     echo "<label for=\"enqueuetick\">Tick this if you really want to enqueue the messages to send and click on enqueue</label>";
     echo "</div>";
     echo "<div class=\"type-button\">";
     echo "<input type=\"submit\" name=\"action\" value=\"enqueue\" />\n" ;
     echo "</div>";
     echo "</div>";
  }
  echo "</form>\n";

  echo "<div> <!-- info -->\n";

  require_once "footer.php";
} // end of DisplayAdminprepareenque



function DisplayAdminMassMails($TData) {
  global $title;
  $title = "Admin Mass Mails";
  require_once "header.php";

  Menu1("", ww('MainPage')); // Displays the top menu

  Menu2("admin/adminmassmails.php", ww('MainPage')); // Displays the second menu

  $MenuAction  = "            <li><a href=\"adminmassmails.php\">Admin Massmails</a></li>\n";
  $MenuAction .= "            <li><a href=\"adminmassmails.php?action=createbroadcast\">Create new broadcast</a></li>\n";
  if (HasRight("MassMail","Send")) { // if has right to trig
    $MenuAction .= "            <li><a href=\"adminmassmails.php?action=ShowPendingTrigs\">Trigger mass mails</a></li>\n";
  }


  DisplayHeaderShortUserContent( "Admin Mails - Broadcast Messages","");
  ShowLeftColumn($MenuAction,VolMenu());

  $max = count($TData);
  $max = 0;

  echo "    <div id=\"col3\"> \n";
  echo "      <div id=\"col3_content\" class=\"clearfix\"> \n";
  echo "        <div class=\"info clearfix\">\n";

  echo "<table><tr><td align='right'>Please write here in </td><td bgcolor=yellow align=left>".LanguageName($_SESSION['IdLanguage'])."</td></table>";
  echo "<br />" ;
  // echo "<hr />\n";
  echo "<table>\n";
  echo "<form method=post action=adminmassmails.php>\n";
  echo "<input type=hidden name=IdBroadCast value=",$TData->IdBroadcast,">\n" ;
  echo "<tr><td>subject</td><td> <textarea name=subject  rows=1 cols=80>", GetParam(subject), "</textarea></td>";
  echo "<tr><td>body</td><td> <textarea name=body rows=10 cols=80>", GetParam(body), "</textarea></td>";
  echo "<tr><td>greetings</td><td> <textarea name=greetings rows=2 cols=80>", GetParam(greetings), "</textarea></td>";
  echo "\n<tr><td colspan=2 align=center>";
  echo "<input type='submit' name='action' value='find'>";
  if (empty($TData->IdBroadcast)) echo " <input type=submit name=action value=update>";
  else echo " <input type=submit name=action value=update>";
  echo "</td><td align=center>" ;
  if (HasRight('MassMail','Send')) {
     echo "Send <input type=checkbox name=send> ";
     echo " <input type=submit name=action value=send>";
  }
  echo "</td> ";
  echo "</form>\n";
  echo "</table>\n";
  echo "</div> <!-- info -->\n";

  require_once "footer.php";

}


// This function proposes (?) to create a broadcast
function DisplayFormCreateBroadcast($IdBroadCast=0, $Name = "",$BroadCast_Title_,$BroadCast_Body_,$Description, $Type = "") {
  global $title;
  $title = "Create a new broadcast";
  require_once "header.php";

  Menu1("", ww('MainPage')); // Displays the top menu

  Menu2("admin/adminmassmails.php", ww('MainPage')); // Displays the second menu

  $MenuAction  = "            <li><a href=\"adminmassmails.php\">Admin Massmails</a></li>\n";
  $MenuAction .= "            <li><a href=\"adminmassmails.php?action=createbroadcast\">Create new broadcast</a></li>\n";
  if (HasRight("MassMail","Send")) { // if has right to trig
    $MenuAction .= "            <li><a href=\"adminmassmails.php?action=ShowPendingTrigs\">Trigger mass mails</a></li>\n";
  }

  DisplayHeaderShortUserContent( "Admin Mails - Broadcast Messages","");
  ShowLeftColumn($MenuAction,VolMenu());

  echo "    <div id=\"col3\"> \n";
  echo "      <div id=\"col3_content\" class=\"clearfix\"> \n";
  echo "        <div class=\"info clearfix\">\n";

  echo "<form method=\"post\" action=\"adminmassmails.php\" class=\"yform full\">\n";
  echo "<input type=\"hidden\" name=\"IdBroadCast\" value=\"$IdBroadCast\">";
  echo "<p class=\"note center\">Please write here in <strong>".LanguageName($_SESSION['IdLanguage'])."</strong></p>";
  echo "<div class=\"type-text\">";
  echo "<p>Give the code name of the broadcast as a word entry (must not exist in words table previously) like <b>NewsJuly2007</b> or <b>NewsAugust2007</b> without spaces!</p>";
  echo "<label for=\"Name\">WordCode for the newsletter</label>";
  echo "<input type=\"text\" ";
  if ($Name != "")
    echo "readonly"; // don't change a group name because it is connected to words
  echo " id=\"Name\" name=\"Name\" value=\"$Name\" />";
  echo "</div>";

  echo "<div class=\"type-text\">";
  echo "<label for=\"BroadCast_Title_\">Subject for the newsletter (%username% will be replaced by the username at sending)</label>";
  echo "<input type=\"text\" id=\"BroadCast_Title_\" name=\"BroadCast_Title_\" value=\"$BroadCast_Title_\" />" ;
  echo "</div>";

  echo "<div class=\"type-text\">";
  echo "<label for=\"BroadCast_Body_\">Body of the newsletter (%username% will be replaced by the username at sending)</label>";
  echo "<textarea id=\"BroadCast_Body_\" name=\"BroadCast_Body_\" rows=\"30\">",$BroadCast_Body_,"</textarea>" ;
  echo "</div>";

  echo "<div class=\"type-text\">";
  echo "<label for=\"Description\">Description (as translators will see it in AdminWord) </label>";
  echo "<textarea id=\"Description\" name=\"Description\" rows=\"8\">",$Description,"</textarea>" ;
  echo "</div>";

  echo "<div class=\"type-button\">";
  if ($IdBroadCast != 0)
    echo "<input type=\"submit\" name=\"submit\" value=\"update massmail\">";
  else
    echo "<input type=\"submit\" name=\"submit\" value=\"create massmail\">";

  echo "<input type=\"hidden\" name=\"action\" value=\"createbroadcast\">";
  echo "</div>";
  echo "</form>";

  require_once "footer.php";
} // DisplayFormCreateBroadcast

?>
