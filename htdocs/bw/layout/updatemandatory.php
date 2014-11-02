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
// Warning this page is not a good sample for layout
// it contain too much logic/algorithm - May be the signup page is to be an exception ?-

function DisplayUpdateMandatory($Username = "", $FirstName = "", $SecondName = "", $LastName = "", $pIdCountry = 0, $pIdRegion = 0, $pIdCity = 0, $HouseNumber = "", $StreetName = "", $Zip = "", $Gender = "", $MessageError = "", $BirthDate = "", $HideBirthDate = "No", $HideGender = "No", $MemberStatus = "",$CityName="") {
  global $title, $IsVolunteerAtWork;
  $title = ww('UpdateMandatoryPage');

  require_once "header.php";

  Menu1($title, ww('UpdateMandatoryPage')); // Displays the top menu
?>
  <SCRIPT SRC="lib/select_area.js" TYPE="text/javascript"></SCRIPT>
<?php


  Menu2("", ww('UpdateMandatoryPage')); // Displays the second menu
  $stitle = $title;
  $stitle .= " - " . $Username;

  DisplayHeaderShortUserContent($stitle);

  $IdCountry = $pIdCountry;
  $IdCity = $pIdCity;
  if ($IdCity!=0) {
     $IdRegion = GetIdRegionForCity($IdCity);
  }
  else {
     $IdRegion = $pIdRegion;
  }
  $scountry = ProposeCountry($IdCountry, "updatemandatory");

  echo "    <div id=\"col3\" style=\"margin:0;\"> \n";
  echo "      <div id=\"col3_content\" class=\"clearfix\"> \n";
  echo "        <div class=\"info clearfix\">\n";

  echo "<input type=hidden name=IdRegion value=-1>" ;
   $scity= ProposeCity($IdCity, $IdRegion, "updatemandatory",$CityName,$IdCountry);

  echo "<form method=post name=\"updatemandatory\" action=\"updatemandatory.php\">\n";
  echo "<table  style=\"font-size: 12;\">\n";
  echo "<input type=hidden name=action value=updatemandatory>\n";
  if (GetStrParam("cid") != "")
    echo "<input type=hidden name=cid value=\"", GetStrParam("cid"), "\">\n";
  if ($MessageError != "") {
    echo "\n<tr><th colspan=3>", ww("SignupPleaseFixErrors"), ":<br><font color=red>", $MessageError, "</font></th>";
  }
//  else if($IdCity!=0) {
//    echo "\n<tr><th colspan=3 align=left>", ww('UpdateMandatoryUpdated'), "</th>";
//  }
  else {
    echo "\n<tr><th colspan=3 align=left>", ww('UpdateMandatoryIntroduction'), "</th>";
  }
  echo "\n<tr><td colspan=3 align=center><hr /></td>";
  echo "\n<input name=Username type=hidden value=\"$Username\">";
  echo "\n<tr><td>", ww('SignupName'), "</td><td><input name=FirstName type=text value=\"$FirstName\" size=12> <input name=SecondName type=text value=\"$SecondName\" size=8> <input name=LastName type=text value=\"$LastName\" size=14></td><td style:\"font-size=2\">", ww('SignupNameDescription'), "</td>";
  echo "\n<tr><td colspan=3 align=center><hr /></td>";
  echo "\n<tr><td>", ww('SignupIdCity'), "</td><td>";
  echo $scountry, " " ;
  if ($IdCountry!=0) {
       echo "\n<br>" . ww("City")." <input type=text name=CityName value=\"".$CityName."\" onChange=\"change_region('updatemandatory')\">" ;
  }
  echo $scity ;
  echo "</td><td>", ww('SignupIdCityDescription'), "</td>";
  echo "\n<tr><td>", ww('SignupHouseNumber'), "</td><td><input name=HouseNumber type=text value=\"$HouseNumber\" size=8></td><td>", ww('SignupHouseNumberDescription'), "</td>";
  echo "\n<tr><td>", ww('SignupStreetName'), "</td><td><input name=StreetName type=text value=\"$StreetName\" size=30></td><td>", ww('SignupStreetNameDescription'), "</td>";
  echo "\n<tr><td>", ww('SignupZip'), "</td><td><input name=Zip type=text value=\"$Zip\"></td><td>", ww('SignupZipDescription'), "</td>";
  echo "\n<tr><td colspan=3 align=center><hr /></td>";

  echo "\n<tr><td colspan=2>";
  echo ww("Gender"), " ";

  echo "<select name=Gender>";
  echo "<option value=\"\"></option>"; // set to not initialize at beginning
  /*
    echo "<option value=\"IDontTell\"";
    if ($Gender=="IDontTell") echo " selected";
    echo ">",ww("IDontTell"),"</option>";
  */

  echo "<option value=\"male\"";
  if ($Gender == "male")
    echo " selected";
  echo ">", ww("male"), "</option>";

  echo "<option value=\"female\"";
  if ($Gender == "female")
    echo " selected";
  echo ">", ww("female"), "</option>";
  echo "</select>\n ";
  echo " ", ww("Hidden"), " \n<input type=checkbox Name=HideGender";
  if ($HideGender == 'Yes')
    echo " checked";
  echo ">\n";
  echo "</td><td>", ww("SignupGenderDescription"), "</td>";

  echo "\n<tr><td colspan=3 align=center><hr /></td>";
  echo "\n<tr><td>", ww('SignupBirthDate'), "</td><td><input name=BirthDate type=text value=\"$BirthDate\" size=10>";
  echo " ", ww("AgeHidden"), " \n<input type=checkbox Name=HideBirthDate";
  if ($HideBirthDate == 'Yes')
    echo " checked";
  echo ">\n";
  echo "</td><td>", ww('SignupBirthDateDescription', ww('AgeHidden')), "</td>";
  echo "\n<tr><td colspan=3 align=center><hr /></td>";

  echo "\n<tr><td>", ww('FeedbackUpdateMandatory'), "</td><td><textarea name=Comment cols=60 rows=4>", GetStrParam("Comment"), "</textarea></td><td>", ww('FeedbackUpdateMandatoryDesc'), "</td>";
  echo "\n<tr><td colspan=3 align=center><hr /></td>";
  if ($IsVolunteerAtWork) {
    $tt = sql_get_enum("members", "Status"); // Get the different available status
    $maxtt = count($tt);
    echo "\n<tr>";
    echo "<td>Status <select name=Status>\n";
    echo "<option value=\" - undefined - \"> - undefined - </option>";
    for ($ii = 0; $ii < $maxtt; $ii++) {
      echo "<option value=\"", $tt[$ii], "\"";
      if ($tt[$ii] == $MemberStatus)
        echo " selected";
      echo ">", $tt[$ii], "</option>\n";
    }
    echo "</select>\n</td>\n";
    echo "<td colspan=2 align=center>";
    echo "<input type=\"submit\" id=\"submit\">\n";
    echo "</td>";
  } else {
    echo "\n<tr><td colspan=3 align=center>";
    echo "<input type=\"submit\" id=\"submit\" onclick=\"return confirm('", str_replace("\n", "", ww('UpdateMandatoryConfirmQuestion')), "');\">\n";
    echo "</td>";
  }

  echo "\n</table>\n";
  echo "</form>\n";

  require_once "footer.php";
}

function DisplayUpdateMandatoryDone($Message) {
  global $title, $IsVolunteerAtWork;
  $title = ww('UpdateMandatoryPage');

  require_once "header.php";

  Menu1($title, ww('UpdateMandatoryPage')); // Displays the top menu

  Menu2("", ww('UpdateMandatoryPage')); // Displays the second menu
  DisplayHeaderShortUserContent($title);

  echo "<br><br><center>", $Message, "</center>\n";
  require_once "footer.php";
}
?>
