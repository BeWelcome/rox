<?php
require_once("Menus_micha.php") ;
// Warning this page is not a good sample for layout
// it contain too much logic/algorithm - May be the signup page is to be an exception ?-

function DisplaySignupFirstStep($Username="",$FirstName="",$SecondName="",$LastName="",$Email="",$EmailCheck="",$pIdCountry=0,$pIdRegion=0,$pIdCity=0,$HouseNumber="",$StreetName="",$Zip="",$ProfileSummary="",$SignupFeedback="",$Gender="",$password="",$secpassword="",$SignupError="",$BirthDate="",$HideBirthDate="No",$HideGender="No") {
  global $title ;
  $title=ww('Signup') ;

  include "header_micha.php" ;
	
	Menu1("",ww('MainPage')) ; // Displays the top menu
?>
  <SCRIPT SRC="lib/select_area.js" TYPE="text/javascript"></SCRIPT>
<?php



echo "<div id=\"maincontent\">\n" ;
echo "  <div id=\"columns\">\n" ;
echo "		<div id=\"columns-low\">\n" ;
echo "		<div id=\"signup\">\n" ;


echo "<!-- signup header goes here -->\n" ;
echo "<p id=\"signupheader\">" ;
echo ww("BeWelcomesignup") ;
echo "</p>\n" ;
	
//echo "					<div class=\"user-content\">" ;
	$IdCountry=$pIdCountry ;
	$IdRegion=$pIdRegion ;
	$IdCity=$pIdCity;
  $scountry=ProposeCountry($IdCountry,"signup") ;
  $sregion=ProposeRegion($IdRegion,$IdCountry,"signup") ;
  $scity=ProposeCity($IdCity,$IdRegion,"signup") ;

echo "<!-- signup info goes here -->\n" ;
echo "<p id=\"signupinfo\">\n" ;
echo "<h3 class=\"signupboxes\">Welcome to the signup page.<br />\n" ;
echo "</h3>\n" ;
	if ($SignupError!="") {
    echo ww("SignupPleaseFixErrors"),":<br><font color=red>",$SignupError,"</font>" ;
	}
	else {
    echo ww('SignupIntroduction') ;
	}
echo "</p>\n" ;

  echo "<form method=post name=\"signup\" action=\"signup.php\">\n" ;
	echo "<input type=hidden name=action value=SignupFirstStep>\n" ;
	echo "<table  class=\"signuptables\">\n" ;

	echo "\n<tr><td class=\"signuplabels\"><h3>",ww('SignupUsername'),"</h3>","<p class=\"signupvisible\">",ww('GreenVisible'),"</p>","</td><td><input name=Username type=text value=\"$Username\" class=\"signupborders\">" ;
	echo  "<a href=\"#\" onclick=\"return false;\">?<span>";
	echo ww('SignupUsernameDescription') ;
	echo "</span></a>","</td>\n" ;
	echo "<td>Choose your username, may only contain letters and/or digits<td>\n" ;

	echo "\n<tr><td><h3>",ww('SignupPassword'),"</h3>","<p class=\"signuphidden\">",ww('RedHidden'),"</p>","</td><td><input name=password type=password value=\"$password\" class=\"signupborders\">" ;
	echo  "<a href=\"#\" onclick=\"return false;\">?<span>";
	echo ww('SignupPasswordDescription') ;
	echo "</span></a>","</td>\n" ;
	echo "<td>Choose a password, minimum 8 characters</td>\n" ;

	echo "\n<tr><td><h3>",ww('SignupCheckPassword'),"</h3>","<p class=\"signuphidden\">",ww('RedHidden'),"</p>","</td><td><input name=secpassword type=password value=\"$secpassword\" class=\"signupborders\">" ;
	echo  "<a href=\"#\" onclick=\"return false;\">?<span>";
  echo "enter EXACTLY the same password as per above" ;
	echo "</span></a>";
	echo "</td>\n" ;
	echo "<td></td>\n" ;
  echo "\n</table>\n" ;
	
	
	
	echo "<table  class=\"signuptables\">\n" ;
	echo "\n<tr><td class=\"signuplabels\">",ww('SignupName'),"<p class=\"signuphidden\">",ww('RedHidden'),"</p>","</td>\n" ;
	echo "<td class=\"signupinputs\"><input name=FirstName type=text value=\"$FirstName\" class=\"signupname\" >\n" ;
	echo "<input name=SecondName type=text value=\"$SecondName\" class=\"signupname\">\n" ;
	echo "<input name=LastName type=text value=\"$LastName\" class=\"signupname\">\n" ; 
	echo  "<a href=\"#\" onclick=\"return false;\">?<span>";
  echo ww('SignupNameDescription') ;
	echo "</span></a>";
	echo "</td>\n" ;
	echo "<td>Enter your real name: first name, second name, last name.</td>\n" ;

	echo "\n<tr><td><h3>",ww('Gender'),"</h3></td>" ;
	echo "<td>" ;
	echo "<select name=Gender>\n" ;
	echo "<option value=\"\"></option>" ; // set to not initialize at beginning
/*	
	echo "<option value=\"IDontTell\"" ;
	if ($Gender=="IDontTell") echo " selected" ; 
	echo ">",ww("IDontTell"),"</option>" ;
*/ 

	echo "<option value=\"male\"" ;
	if ($Gender=="male") echo " selected" ; 
	echo ">",ww("male"),"</option>" ; 

	echo "<option value=\"female\"" ;
	if ($Gender=="female") echo " selected" ; 
	echo ">",ww("female"),"</option>" ;
	echo "</select> \n" ;
	echo " ",ww("Hidden")," \n<input type=checkbox Name=HideGender" ;
	if ($HideGender=='Yes') echo " checked" ;
  echo ">\n" ;
	echo  "<a href=\"#\" onclick=\"return false;\">?<span>";
	echo ww('SignupGenderDescription') ;
	echo "</span></a>","</td>\n" ;
	echo "<td>Select your gender, you can choose to hide it</td>\n" ;
	
	echo "\n<tr><td><h3>",ww('SignupBirthDate'),"</h3></td>" ;
	echo "<td>" ;
  echo "<input name=BirthDate type=text value=\"$BirthDate\" class=\"signupname\" >" ;
	echo " ",ww("Hidden")," \n<input type=checkbox Name=HideBirthDate" ;
	if ($HideBirthDate=='Yes') echo " checked" ;
  echo ">\n" ;
	echo  "<a href=\"#\" onclick=\"return false;\">?<span>";
	echo ww('SignupBirthDateDescription') ;
	echo "</span></a>","</td>\n" ;
	echo "<td>Please provide your Birth date in the form DD-MM-AAAA</td>\n" ;
  echo "\n</table>\n" ;
	
	
	
	echo "<table  class=\"signuptables\">\n" ;
	echo "\n<tr><td class=\"signuplabels\">",ww('SignupEmail'),"<p class=\"signuphidden\">",ww('RedHidden'),"</p>","</td>" ;
	echo "<td class=\"signupinputs\"><input name=Email type=text value=\"$Email\" class=\"signupname\" >" ; 
	echo  "<a href=\"#\" onclick=\"return false;\">?<span>";
  echo ww('SignupEmailDescription') ;
	echo "</span></a>";
	echo "</td>\n" ;
	echo "<td>Enter the emailaddress by which you will be contacted by other members and volunteers.</td>\n" ;

	echo "\n<tr><td><h3>",ww('SignupEmailCheck'),"</h3></td>" ;
	echo "<td>" ;
  echo "<input name=EmailCheck type=text value=\"",$EmailCheck,"\" class=\"signupname\" >" ;
//	echo  "<a href=\"#\" onclick=\"return false;\">?<span>";
//	echo ww('SignupBirthDateDescription') ;
//	echo "</span></a>" ;
  echo "</td>\n" ;
	echo "<td>Retype the email, to make sure it's correct.</td>\n" ;
  echo "\n</table>\n" ;

	echo "<table  class=\"signuptables\">\n" ;
	echo "<td class=\"signuplabels\"><h3>Location</h3><p class=\"signupvisible\">",ww("GreenVisible"),"</p></td>" ;
	echo "<td class=\"signupinputs\">" ;
	echo $scountry," ",$sregion," ",$scity ;
	echo "</td>" ;
	echo "<td>Select here the country, region and city where you live</td>" ;
	
	echo "\n<tr><td><h3>",ww('SignupHouseNumber'),"</h3><p class=\"signuphidden\">",ww('RedHidden'),"</p></td>" ;
	echo "<td>" ;
  echo "<input name=HouseNumber type=text value=\"$HouseNumber\" class=\"signupname\" >" ;
	echo  "<a href=\"#\" onclick=\"return false;\">?<span>";
	echo ww('SignupHouseNumberDescription') ;
	echo "</span></a>" ;
  echo "</td>\n" ;
	echo "<td>Enter your house or appartment number.</td>\n" ;

	echo "\n<tr><td><h3>",ww('SignupStreetName'),"</h3><p class=\"signuphidden\">",ww('RedHidden'),"</p></td>" ;
	echo "<td>" ;
  echo "<input name=StreetName type=text value=\"$StreetName\" class=\"signupname\" >" ;
	echo  "<a href=\"#\" onclick=\"return false;\">?<span>";
	echo ww('SignupStreetNameDescription') ;
	echo "</span></a>" ;
  echo "</td>\n" ;
	echo "<td>Enter the name of the street you live on.</td>\n" ;

	echo "\n<tr><td><h3>",ww('SignupZip'),"</h3><p class=\"signuphidden\">",ww('RedHidden'),"</p></td>" ;
	echo "<td>" ;
  echo "<input name=Zip type=text value=\"$Zip\"  class=\"signupname\" >" ;
	echo  "<a href=\"#\" onclick=\"return false;\">?<span>";
	echo ww('SignupZipDescription') ;
	echo "</span></a>" ;
  echo "</td>\n" ;
	echo "<td>Enter your zipcode.</td>\n" ;

  echo "\n</table>\n" ;
	
	
	
	echo "<table  class=\"signuptables\">\n" ;
	echo "<tr><td><h3>",ww('SignupProfileSummary'),"</h3><p class=\"signupvisible\">",ww('GreenVisible'),"</p></td>" ;
	echo "<td class=\"signupinputs\"><textarea class=\"signuptexts\" name=\"ProfileSummary\">",$ProfileSummary,"</textarea>" ;
	echo  "<a href=\"#\" onclick=\"return false;\">?<span>";
	echo ww('ProfileSummaryDescription') ;
	echo "</span></a>" ;
	echo "</td>" ;
	echo "<td>Please fill in a profile summary.</td>\n" ;
	
	echo "<tr><td><h3>",ww('SignupFeedback'),"</h3><p class=\"signuphidden\">",ww('RedHidden'),"</p></td>" ;
	echo "<td class=\"signupinputs\"><textarea class=\"signuptexts\" name=\"SignupFeedback\">",$SignupFeedback,"</textarea>" ;
	echo  "<a href=\"#\" onclick=\"return false;\">?<span>";
	echo ww('SignupFeedbackDescription') ;
	echo "</span></a>" ;
	echo "</td>" ;
	echo "<td>Leave feedback of any problems, explanations about address or identity, or any comments and critique you may have.</td>\n" ;
  echo "\n</table>\n" ;
	
	
	echo "<table  class=\"signuptables\">\n" ;
	echo "\n<tr><td class=\"signuplabels\">\n",ww("SignupTermsAndConditions"),"</td>" ;
	echo "<td id=\"signupterms\"><textarea readonly>",str_replace("<br />","",ww('SignupTerms')),"</textarea></td>\n" ;
	echo "<tr>" ;
	echo "<td id=\"signupagree\" >",ww('IAgreeWithTerms')," <input type=checkbox name=Terms></td>\n" ;
	echo "<td id=\"signupagree\" >"," <input type=\"submit\" onclick=\"return confirm('",str_replace("<br />","",ww('SignupConfirmQuestion')),"');\"  id=\"signupsubmit\" >\n";
	echo "</td>";
  
  echo "\n</table>\n" ;
  echo "</form>\n" ;

echo "   </div>\n";  // columns-low
echo " </div>\n";  // columns


  include "footer.php" ;
}

function DisplaySignupResult($Message) {
  global $title;
  $title=ww('SignupConfirmedPage')  ;

  include "header.php" ;

//	Menu1("error.php",ww('MainPage')) ; // Displays the top menu
//	Menu2($_SERVER["PHP_SELF"]) ; // Display the second menu

	Menu1("",ww("SignupConfirmedPage")) ; // Displays the top menu
  DisplayHeaderShortUserContent(ww("SignupConfirmedPage")) ;

  echo "<table bgcolor=#ffffcc >" ;
  echo "<TR><td>",$Message,"</TD><br>" ;
  echo "</table>" ;


  include "footer.php" ;
	exit(0) ; // To be sure that member don't go further after 
}

?>
