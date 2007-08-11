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

function DisplaySignupFirstStep($Username = "", $FirstName = "", $SecondName = "", $LastName = "", $Email = "", $EmailCheck = "", $pIdCountry = 0, $pIdCity = 0, $HouseNumber = "", $StreetName = "", $Zip = "", $ProfileSummary = "", $SignupFeedback = "", $Gender = "", $password = "", $secpassword = "", $SignupError = "", $BirthDate = "", $HideBirthDate = "No", $HideGender = "No",$CityName="") {
	global $title;
	$title = ww('Signup');

	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu
	Menu2($_SERVER["PHP_SELF"]); // Displays the second menu
	
	DisplayHeaderShortUserContent(ww("Signup Page")); // Display the header
  $strconfirm=str_replace("<br />", " ", addslashes(ww("SignupConfirmQuestion"))) ;
  $strconfirm=str_replace("\r\n", " ", $strconfirm) ;
?>
  <SCRIPT SRC="lib/select_area.js" TYPE="text/javascript"></SCRIPT>

<?php

echo "\n<script type=\"text/javascript\">\n" ;
echo "<!--\n" ;
echo "  function check_form() {\n" ;

echo "	   if (!document.signup.Terms.checked) { \n";
echo "        alert(\"",ww("SignupMustacceptTerms"),"\");\n";
echo "        return(false);\n";
echo "    }\n" ;

echo "    if (confirm('", $strconfirm, "')) {\n" ;
echo "        document.signup.submit() ;\n" ;
echo "    }\n" ;
echo "  }\n" ;
echo "// -->\n" ;
echo "</script>\n" ;  


/*

	echo "<div id=\"maincontent\">\n";
	echo "  <div id=\"columns\">\n";
	echo "		<div id=\"columns-low\">\n";
	echo "		<div id=\"signup\">\n";

	echo "<!-- signup header goes here -->\n";
	echo "<p id=\"signupheader\">";
	echo ww("BeWelcomesignup");
	echo "</p>\n";
*/
	//echo "					<div class=\"user-content\">";
	$IdCountry = $pIdCountry;
	$IdCity = $pIdCity;
	$scountry = ProposeCountry($IdCountry, "signup");
	$scity = ProposeCity($IdCity, 0, "signup",$CityName,$IdCountry);

  echo "        <div class=\"info\">\n";
  echo "<!-- signup introduction goes here -->\n";
	echo "<h3 class=\"signupboxes\">".ww("WelcomeToSignup")."<br />\n";
	echo "</h3>\n";
	if ($SignupError != "") {
		echo ww("SignupPleaseFixErrors"), ":<br><font color=red>", $SignupError, "</font>";
	} else {
		echo ww('SignupIntroduction');
	}
	echo "</p>\n";

	echo "<form method=post name=\"signup\" action=\"signup.php\">\n";
	echo "<input type=hidden name=action value=SignupFirstStep>\n";

	echo "<table  class=\"signuptables\">\n";
	echo "<td class=\"signuplabels\"><h3>",ww("Location"),"</h3><p class=\"signupvisible\">", ww("GreenVisible"), "</p></td>";
	echo "<td class=\"signupinputs\">";
	echo $scountry, " ";
	echo "<input type=hidden name=IdRegion value=0>"; // kept for transition compatibility
	if ($IdCountry!=0) {
	    echo "\n<br>" . ww("City")." <input type=text name=CityName value=\"".$CityName."\" onChange=\"change_region('signup')\">" ;
	}
	echo $scity;
	echo "</td>";
	echo "<td>",ww("SignupIdCityDescription "),"</td>";

	echo "\n<tr><td><h3>", ww('SignupHouseNumber'), "</h3><p class=\"signuphidden\">", ww('RedHidden'), "</p></td>";
	echo "<td>";
	echo "<input name=HouseNumber type=text value=\"$HouseNumber\" class=\"signupname\" >";
	echo "<a href=\"#\" onclick=\"return false;\">?<span>";
	echo ww('SignupHouseNumberDescription');
	echo "</span></a>";
	echo "</td>\n";
	echo "<td>",ww("SignupProvideHouseNumber"),"</td>\n";

	echo "\n<tr><td><h3>", ww('SignupStreetName'), "</h3><p class=\"signuphidden\">", ww('RedHidden'), "</p></td>";
	echo "<td>";
	echo "<input name=StreetName type=text value=\"$StreetName\" class=\"signupname\" >";
	echo "<a href=\"#\" onclick=\"return false;\">?<span>";
	echo ww('SignupStreetNameDescription');
	echo "</span></a>";
	echo "</td>\n";
	echo "<td>",ww("SignupStreetNameDescription"),"</td>\n";

	echo "\n<tr><td><h3>", ww('SignupZip'), "</h3><p class=\"signuphidden\">", ww('RedHidden'), "</p></td>";
	echo "<td>";
	echo "<input name=Zip type=text value=\"$Zip\"  class=\"signupname\" >";
	echo "<a href=\"#\" onclick=\"return false;\">?<span>";
	echo ww('SignupZipDescription');
	echo "</span></a>";
	echo "</td>\n";
	echo "<td>",ww("SignupZipDescriptionShort"),"</td>\n";

	echo "\n</table>\n";


	echo "<table  class=\"signuptables\">\n";

	echo "\n<tr><td class=\"signuplabels\"><h3>", ww('SignupUsername'), "</h3>", "<p class=\"signupvisible\">", ww('GreenVisible'), "</p>", "</td><td><input name=Username type=text value=\"$Username\" class=\"signupborders\">";
	echo "<a href=\"#\" onclick=\"return false;\">?<span>";
	echo ww('SignupUsernameDescription');
	echo "</span></a>", "</td>\n";
	echo "<td>",ww("SignupUsernameShortDesc"),"<td>\n";

	echo "\n<tr><td><h3>", ww('SignupPassword'), "</h3>", "<p class=\"signuphidden\">", ww('RedHidden'), "</p>", "</td><td><input name=password type=password value=\"$password\" class=\"signupborders\">";
	echo "<a href=\"#\" onclick=\"return false;\">?<span>";
	echo ww('SignupPasswordDescription');
	echo "</span></a>", "</td>\n";
	echo "<td>",ww("SignupPasswordChoose"),"</td>\n";

	echo "\n<tr><td><h3>", ww('SignupCheckPassword'), "</h3>", "<p class=\"signuphidden\">", ww('RedHidden'), "</p>", "</td><td><input name=secpassword type=password value=\"$secpassword\" class=\"signupborders\">";
	echo "<a href=\"#\" onclick=\"return false;\">?<span>";
	echo "enter EXACTLY the same password as per above";
	echo "</span></a>";
	echo "</td>\n";
	echo "<td></td>\n";
	echo "\n</table>\n";

	echo "<table  class=\"signuptables\">\n";
	echo "\n<tr><td class=\"signuplabels\">", ww('SignupName'), "<p class=\"signuphidden\">", ww('RedHidden'), "</p>", "</td>\n";
//	echo "<td class=\"signupinputs\">",ww("FirstName"),"<br><input name=FirstName type=text value=\"$FirstName\" class=\"signupname\" >\n";
//	echo "",ww("SignupSecondName"),"<br><input name=SecondName type=text value=\"$SecondName\" class=\"signupname\">\n";
//	echo ww("LastName"),"<br><input name=LastName type=text value=\"$LastName\" class=\"signupname\">\n";
   echo "<td>\n<table>";
	echo "<tr><td class=\"signupinputs\">",ww("FirstName"),"</td><td class=\"signupinputs\">",ww("SignupSecondNameOptional"),"</td><td class=\"signupinputs\">",ww("LastName"),"</td>\n"; 
	echo "<tr><td style=\"font-size:2;\"><input name=FirstName type=text value=\"$FirstName\" class=\"signupname\" size=16></td>\n";
	echo "<td class=\"signupinputs\"><input name=SecondName type=text value=\"$SecondName\" class=\"signupname\" size=16></td>\n";
	echo "<td class=\"signupinputs\"><input name=LastName type=text value=\"$LastName\" class=\"signupname\" size=16></td>\n";
	echo "<td rowspan=2 valign=center><a href=\"#\" onclick=\"return false;\">?<span>";
	echo ww('SignupNameDescription');
	echo "</span></a></td>\n";
	echo "</table>\n</td>\n";
	echo "<td>",ww("SignupNameGuide"),"</td>\n";

	echo "\n<tr><td><h3>", ww('Gender'), "</h3></td>";
	echo "<td>";
	echo "<select name=Gender>\n";
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
	echo "</select> \n";
	echo " ", ww("Hidden"), " \n<input type=checkbox Name=HideGender";
	if ($HideGender == 'Yes')
		echo " checked";
	echo ">\n";
	echo "<a href=\"#\" onclick=\"return false;\">?<span>";
	echo ww('SignupGenderDescription');
	echo "</span></a>", "</td>\n";
	echo "<td></td>\n";

	echo "\n<tr><td><h3>", ww('SignupBirthDate'), "</h3></td>";
	echo "<td>";
	echo "<input name=BirthDate type=text value=\"$BirthDate\" class=\"signupname\" >";
	echo " ", ww("AgeHidden"), " \n<input type=checkbox Name=HideBirthDate";
	if ($HideBirthDate == 'Yes')
		echo " checked";
	echo ">\n";
	echo "<a href=\"#\" onclick=\"return false;\">?<span>";
	echo ww('SignupBirthDateDescription');
	echo "</span></a>", "</td>\n";
	echo "<td>",ww("SignupBirthDateShape"),"</td>\n";
	echo "\n</table>\n";

	echo "<table  class=\"signuptables\">\n";
	echo "\n<tr><td class=\"signuplabels\">", ww('SignupEmail'), "<p class=\"signuphidden\">", ww('RedHidden'), "</p>", "</td>";
	echo "<td class=\"signupinputs\"><input name=Email type=text value=\"$Email\" class=\"signupname\" >";
	echo "<a href=\"#\" onclick=\"return false;\">?<span>";
	echo ww('SignupEmailDescription');
	echo "</span></a>";
	echo "</td>\n";
	echo "<td>",ww("SignupEmailShortDesc"),"</td>\n";

	echo "\n<tr><td><h3>", ww('SignupEmailCheck'), "</h3></td>";
	echo "<td>";
	echo "<input name=EmailCheck type=text value=\"", $EmailCheck, "\" class=\"signupname\" >";
	//	echo  "<a href=\"#\" onclick=\"return false;\">?<span>";
	//	echo ww('SignupBirthDateDescription');
	//	echo "</span></a>";
	echo "</td>\n";
	echo "<td>",ww("SignupRetypeEmailShortDesc"),"</td>\n";
	echo "\n</table>\n";


	echo "<table class=\"signuptables\">\n";
	echo "<tr><td><h3>", ww('SignupProfileSummary'), "</h3><p class=\"signupvisible\">", ww('GreenVisible'), "</p></td>";
	echo "<td class=\"signupinputs\"><textarea class=\"signuptexts\" name=\"ProfileSummary\">", $ProfileSummary, "</textarea>";
	echo "<a href=\"#\" onclick=\"return false;\">?<span>";
	echo ww('ProfileSummaryDescription');
	echo "</span></a>";
	echo "</td>\n";
	echo "<td width=\"30%\"></td>";

	echo "<tr><td><h3>", ww('SignupFeedback'), "</h3><p class=\"signuphidden\">", ww('RedHidden'), "</p></td>";
	echo "<td class=\"signupinputs\"><textarea class=\"signuptexts\" name=\"SignupFeedback\">", $SignupFeedback, "</textarea>";
	echo "<a href=\"#\" onclick=\"return false;\">?<span>";
	echo ww('SignupFeedbackDescription');
	echo "</span></a>";
	echo "</td>\n";
	echo "<td width=\"30%\"></td>";
	echo "\n</table>\n";

	echo "<table  class=\"signuptables\">\n";
	echo "\n<tr><td class=\"signuplabels\">\n", ww("SignupTermsAndConditions"), "</td>";
	echo "<td id=\"signupterms\"><textarea readonly>", str_replace("<br />", "", ww('SignupTerms')), "</textarea></td>\n";
	echo "<tr>";
	echo "<td id=\"signupagree\" >", ww('IAgreeWithTerms'), " <input type=checkbox name=Terms></td>\n";
	echo "<td id=\"signupagree\" >", " <input type=\"button\" onclick=\"check_form();\"  value=\"",ww("SubmitForm"),"\" id=\"signupsubmit\" >\n";
	echo "</td>";

	echo "\n</table>\n";
	echo "</form>\n";

	echo "        </div>\n"; // end info

	require_once "footer.php";
}

function DisplaySignupResult($Message) {
	global $title;
	$title = ww('SignupConfirmedPage');

	require_once "header.php";

	//	Menu1("error.php",ww('MainPage')); // Displays the top menu
	//	Menu2($_SERVER["PHP_SELF"]); // Display the second menu

	Menu1("", ww("SignupConfirmedPage")); // Displays the top menu
	DisplayHeaderShortUserContent(ww("SignupConfirmedPage"));

  echo "<div class=\"info\">\n";
	echo "<table bgcolor=#ffffcc >";
	echo "<TR><td>", $Message, "</TD><br>";
	echo "</table>";
  echo "</div>\n";

	require_once "footer.php";
	exit (0); // To be sure that member don't go further after 
}
?>
