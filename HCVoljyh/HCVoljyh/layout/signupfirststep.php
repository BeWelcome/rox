<?php
// Warning this page is not a good sample for layout
// it contain too much logic/algorithm - May be the signup page is to be an exception ?-

function DisplaySignupFirstStep($Username="",$FirstName="",$SecondName="",$LastName="",$Email="",$EmailCheck="",$pIdCountry=0,$pIdRegion=0,$pIdCity=0,$HouseNumber="",$StreetName="",$Zip="",$ProfileSummary="",$SignupFeedback="",$Gender="",$password="",$secpassword="",$SignupError="",$BirthDate="",$HideBirthDate="No") {
  global $title ;
  $title=ww('Signup') ;

  include "header.php" ;
?>
  <SCRIPT SRC="lib/select_area.js" TYPE="text/javascript"></SCRIPT>
<?php
	
	$IdCountry=$pIdCountry ;
	$IdRegion=$pIdRegion ;
	$IdCity=$pIdCity;
  $scountry=ProposeCountry($IdCountry) ;
  $sregion=ProposeRegion($IdRegion,$IdCountry) ;
  $scity=ProposeCity($IdCity,$IdRegion) ;

  echo "\n<center>\n" ;
  echo "<form method=post name=\"signup\" action=\"signup.php\">" ;
	echo "<table  style=\"font-size: 12;\">\n" ;
	echo "<input type=hidden name=action value=SignupFirstStep>" ;
	if ($SignupError!="") {
	  echo "<tr><th colspan=3>",ww("SignupPleaseFixErrors"),":<br><font color=red>",$SignupError,"</font></th>" ;
	} 
	else {
	  echo "<tr><th colspan=3>",ww('SignupIntroduction'),"</th>" ; 
	}
	echo "<tr><td colspan=3 align=center><hr></td>" ; 
	echo "<tr><td>",ww('SignupUsername'),"<br>",ww('GreenVisible'),"</td><td><input name=Username type=text value=\"$Username\"></td><td style:\"font-size=2\">",ww('SignupUsernameDescription'),"</td>" ;
	echo "<tr><td>",ww('SignupPassword'),"<br>",ww('RedHidden'),"</td><td><input name=password type=text value=\"$password\"> &nbsp;&nbsp;&nbsp;",ww("SignupCheckPassword")," <input name=secpassword type=text value=\"$secpassword\"></td><td style:\"font-size=2\">",ww('SignupPasswordDescription'),"</td>" ;
	echo "<tr><td>",ww('SignupName'),"<br>",ww('RedHidden'),"</td><td><input name=FirstName type=text value=\"$FirstName\" size=12> <input name=SecondName type=text value=\"$SecondName\" size=8> <input name=LastName type=text value=\"$LastName\" size=14></td><td style:\"font-size=2\">",ww('SignupNameDescription'),"</td>" ;
	echo "<tr><td colspan=3 align=center><hr></td>" ; 
	echo "<tr><td>",ww('SignupEmail'),"<br>",ww('RedHidden'),"</td><td><input name=Email type=text value=\"$Email\"> &nbsp;&nbsp;&nbsp;",ww('SignupEmailCheck')," <input name=EmailCheck type=text value=\"$EmailCheck\">" ;
	echo "</td><td>",ww('SignupEmailDescription'),"</td>" ; 
	echo "<tr><td colspan=3 align=center><hr></td>" ; 
	echo "<tr><td>",ww('SignupIdCity'),"</td><td>" ;
	echo $scountry," ",$sregion," ",$scity ;
	echo "</td><td>",ww('SignupIdCityDescription'),"</td>" ; 
	echo "<tr><td>",ww('SignupHouseNumber'),"</td><td><input name=HouseNumber type=text value=\"$HouseNumber\" size=8></td><td>",ww('SignupHouseNumberDescription'),"</td>" ; 
	echo "<tr><td>",ww('SignupStreetName'),"</td><td><input name=StreetName type=text value=\"$StreetName\" size=60></td><td>",ww('SignupStreetNameDescription'),"</td>" ; 
	echo "<tr><td>",ww('SignupZip'),"</td><td><input name=Zip type=text value=\"$Zip\"></td><td>",ww('SignupZipDescription'),"</td>" ; 
	echo "<tr><td colspan=3 align=center><hr></td>" ; 

	echo "<tr><td colspan=2>" ;
	echo ww("Gender")," " ;
	echo "<select name=Gender>" ;
	echo "<option value=\"\"></option>" ; // set to not initialize at beginning
	echo "<option value=\"IDontTell\"" ;
	if ($Gender=="IDontTell") echo " selected" ; 
	echo ">",ww("IDontTell"),"</option>" ; 

	echo "<option value=\"male\"" ;
	if ($Gender=="male") echo " selected" ; 
	echo ">",ww("male"),"</option>" ; 

	echo "<option value=\"female\"" ;
	if ($Gender=="female") echo " selected" ; 
	echo ">",ww("female"),"</option>" ;
	echo "</select>\n" ;
  echo "</td><td>",ww("SignupGenderDescription"),"</td>";

	echo "<tr><td colspan=3 align=center><hr></td>" ; 
	echo "<tr><td>",ww('SignupBirthDate'),"</td><td><input name=BirthDate type=text value=\"$BirthDate\" size=10>" ;
	echo " ",ww("Hidden")," \n<input type=checkbox Name=HideBirthDate" ;
	if ($HideBirthDate=='Yes') echo " checked" ;
  echo ">\n" ;
	echo "</td><td>",ww('SignupBirthDateDescription',ww('Hidden')),"</td>" ; 
	echo "<tr><td colspan=3 align=center><hr></td>" ; 

	echo "<tr><td colspan=2>",ww('SignupProfileSummary')," ",ww('GreenVisible'),"<br><textarea cols=60 row=4 name=ProfileSummary>",$ProfileSummary,"</textarea></td><td>",ww('ProfileSummaryDescription'),"</td>" ;
	echo "<tr><td colspan=2>",ww('SignupFeedback')," ",ww('RedHidden'),"<br><textarea cols=60 row=4 name=SignupFeedback>",$SignupFeedBack,"</textarea></td><td>",ww('SignupFeedbackDescription'),"</td>" ;

	echo "\n<tr><td align=center valign=center colspan=2>\n",ww("SignupTermsAndConditions"),"<br><textarea readonly cols=70 rows=4>",str_replace("<br />","",ww('SignupTerms')),"</textarea></td>" ;
	echo "<td align=center valign=center >",ww('IAgreeWithTerms'),"<input type=checkbox name=Terms> \n" ;
	echo "<input type=\"submit\" onclick=\"return confirm('",str_replace("<br />","",ww('SignupConfirmQuestion')),"');\">\n";
	echo "</td>";
  
  echo "</table>\n" ;
  echo "</form>\n" ;
	
  echo "</center>\n" ;
  include "footer.php" ;
}
?>
