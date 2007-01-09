<?php
require_once("Menus_micha.php") ;
// Warning this page is not a good sample for layout
// it contain too much logic/algorithm - May be the signup page is to be an exception ?-

function DisplayUpdateMandatory($Username="",$FirstName="",$SecondName="",$LastName="",$pIdCountry=0,$pIdRegion=0,$pIdCity=0,$HouseNumber="",$StreetName="",$Zip="",$Gender="",$MessageError="",$BirthDate="",$HideBirthDate="No",$HideGender="No",$Email,$EmailCheck) {
  global $title ;
  $title=ww('UpdateMandatoryPage') ;

  include "header_micha.php" ;
	
	Menu1($title,ww('UpdateMandatoryPage')) ; // Displays the top menu
?>
  <SCRIPT SRC="lib/select_area.js" TYPE="text/javascript"></SCRIPT>
<?php


	Menu2("",ww('UpdateMandatoryPage')) ; // Displays the second menu


echo "<div id=\"maincontent\">\n" ;
echo "  <div id=\"topcontent\">" ;
echo "					<h3>",$title,"</h3>\n" ;
echo "  </div>\n" ;
echo "</div>\n" ;

echo "\n  <div id=\"columns\">\n" ;
echo "		<div id=\"columns-low\">\n" ;

ShowActions() ; // Show the actions
ShowAds() ; // Show the Ads
	
echo "					<div class=\"user-content\">" ;
	$IdCountry=$pIdCountry ;
	$IdRegion=$pIdRegion ;
	$IdCity=$pIdCity;
  $scountry=ProposeCountry($IdCountry) ;
  $sregion=ProposeRegion($IdRegion,$IdCountry) ;
  $scity=ProposeCity($IdCity,$IdRegion) ;

  echo "<form method=post name=\"updatemandatory\" action=\"updatemandatory.php\">\n" ;
	echo "<table  style=\"font-size: 12;\">\n" ;
	echo "<input type=hidden name=action value=updatemandatory>\n" ;
	if (GetParam("cid")!="") echo "<input type=hidden name=cid value=",GetParam("cid"),">\n" ;
	if ($MessageError!="") {
	  echo "\n<tr><th colspan=3>",ww("SignupPleaseFixErrors"),":<br><font color=red>",$MessageError,"</font></th>" ;
	} 
	else {
	  echo "\n<tr><th colspan=3 align=left>",ww('UpdateMandatoryIntroduction'),"</th>" ; 
	}
	echo "\n<tr><td colspan=3 align=center><hr></td>" ; 
	echo "\n<tr><td>",ww('SignupUsername'),"<br>",ww('GreenVisible'),"</td><td><input name=Username type=text value=\"$Username\" title=\"",ww('SignupUsernameDescription'),"\"></td><td style=\"font-size=2\">",ww('SignupUsernameDescription'),"</td>" ;
	echo "\n<tr><td>",ww('SignupName'),"<br>",ww('RedHidden'),"</td><td><input name=FirstName type=text value=\"$FirstName\" size=12> <input name=SecondName type=text value=\"$SecondName\" size=8> <input name=LastName type=text value=\"$LastName\" size=14></td><td style:\"font-size=2\">",ww('SignupNameDescription'),"</td>" ;
	echo "\n<tr><td colspan=3 align=center><hr></td>" ; 
	echo "\n<tr><td>",ww('SignupEmail'),"<br>",ww('RedHidden'),"</td><td><input name=Email type=text value=\"$Email\"> &nbsp;&nbsp;&nbsp;",ww('SignupEmailCheck')," <input name=EmailCheck type=text value=\"$EmailCheck\">" ;
	echo "</td><td>",ww('SignupEmailDescription'),"</td>" ; 
	echo "\n<tr><td colspan=3 align=center><hr></td>" ; 
	echo "\n<tr><td>",ww('SignupIdCity'),"</td><td>" ;
	echo $scountry," ",$sregion," ",$scity ;
	echo "</td><td>",ww('SignupIdCityDescription'),"</td>" ; 
	echo "\n<tr><td>",ww('SignupHouseNumber'),"</td><td><input name=HouseNumber type=text value=\"$HouseNumber\" size=8></td><td>",ww('SignupHouseNumberDescription'),"</td>" ; 
	echo "\n<tr><td>",ww('SignupStreetName'),"</td><td><input name=StreetName type=text value=\"$StreetName\" size=60></td><td>",ww('SignupStreetNameDescription'),"</td>" ; 
	echo "\n<tr><td>",ww('SignupZip'),"</td><td><input name=Zip type=text value=\"$Zip\"></td><td>",ww('SignupZipDescription'),"</td>" ; 
	echo "\n<tr><td colspan=3 align=center><hr></td>" ; 

	echo "\n<tr><td colspan=2>" ;
	echo ww("Gender")," " ;
	
	echo "<select name=Gender>" ;
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
	echo "</select>\n " ;
	echo " ",ww("Hidden")," \n<input type=checkbox Name=HideGender" ;
	if ($HideGender=='Yes') echo " checked" ;
  echo ">\n" ;
  echo "</td><td>",ww("SignupGenderDescription"),"</td>";

	echo "\n<tr><td colspan=3 align=center><hr></td>" ; 
	echo "\n<tr><td>",ww('SignupBirthDate'),"</td><td><input name=BirthDate type=text value=\"$BirthDate\" size=10>" ;
	echo " ",ww("Hidden")," \n<input type=checkbox Name=HideBirthDate" ;
	if ($HideBirthDate=='Yes') echo " checked" ;
  echo ">\n" ;
	echo "</td><td>",ww('SignupBirthDateDescription',ww('Hidden')),"</td>" ; 
	echo "\n<tr><td colspan=3 align=center><hr></td>" ; 

	echo "\n<tr><td colspan=3 align=center>" ; 
	echo "<input type=\"submit\" onclick=\"return confirm('",str_replace("\n","",ww('UpdateMandatoryConfirmQuestion')),"');\">\n";
	echo "</td>";
  
  echo "\n</table>\n" ;
  echo "</form>\n" ;
echo "					</div>" ; // user-content
	
	

echo "   </div>\n";  // columns-low
echo " </div>\n";  // columns

  include "footer.php" ;
}
?>
