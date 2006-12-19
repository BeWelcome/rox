<?php
require_once("Menus.php") ;

function DisplayEditMyProfile($m,$photo="",$phototext="",$photorank=0,$cityname,$regionname,$countryname,$profilewarning="",$TGroups) {

  global $title,$_SYSHCVOL ;
  $title=ww('EditMyProfilePageFor',$m->Username) ;
  include "header.php" ;

  mainmenu("editmyprofile.php",ww('MainPage'),$m->id) ;
	if ($profilewarning!="") {
    echo "<center><H1>",$profilewarning,"</H1></center>\n" ;
	}
	else {
    echo "<center><H1>",$m->Username,"</H1></center>\n" ;
	}
	$rCurLang=LoadRow("select * from languages where id=".$_SESSION['IdLanguage']) ;
  echo "\n<center>\n" ;
  echo "<table width=50%><tr><td bgcolor=#ffff66>",ww("WarningYouAreWorkingIn",$rCurLang->Name,$rCurLang->Name),"</td></table>\n" ;
  echo "<form method=post>" ;
  echo "<table width=50%>\n" ;
	if (IsAdmin()) { // admin can alter other profiles so in case it was not his own we must create a parameter
    echo "<input type=hidden name=cid value=",$m->id,">" ;
	}

	echo "<input type=hidden name=action value=update>" ;


  echo "\n<tr><td>" ;
  echo ww('FirstName') ;
  echo "</td>" ;
  echo "<td colspan=2>" ;
	echo PublicReadCrypted($m->FirstName) ;
	echo "\n<form method=post style=\"display:inline\" >\n<input type=hidden name=IdCrypt Value=".$m->FirstName."><input type=hidden name=cryptaction value=".ReverseCrypt($m->FirstName).">\n<input type=submit value=\"".ww("Action_".ReverseCrypt($m->FirstName))."\">\n</form>\n" ;
  echo "</td>" ;

  echo "\n<tr><td>" ;
  echo ww('SecondName') ;
  echo "</td>" ;
  echo "<td colspan=2>" ;
	echo PublicReadCrypted($m->SecondName) ;
	echo "\n<form method=post style=\"display:inline\" >\n<input type=hidden name=IdCrypt Value=".$m->SecondName."><input type=hidden name=cryptaction value=".ReverseCrypt($m->SecondName).">\n<input type=submit value=\"".ww("Action_".ReverseCrypt($m->SecondName))."\">\n</form>\n" ;
  echo "</td>" ;

  echo "\n<tr><td>" ;
  echo ww('LastName') ;
  echo "</td>" ;
  echo "<td colspan=2>" ;
	echo PublicReadCrypted($m->LastName) ;
	echo "\n<form method=post style=\"display:inline\" >\n<input type=hidden name=IdCrypt Value=".$m->LastName."><input type=hidden name=cryptaction value=".ReverseCrypt($m->LastName).">\n<input type=submit value=\"".ww("Action_".ReverseCrypt($m->LastName))."\">\n</form>\n" ;
  echo "</td>" ;

  echo "\n<tr><td>" ;
  echo ww('Location') ;
  echo "</td>" ;
  echo "<td><table><td>" ;
	echo $cityname,"<br>" ;
	echo $regionname,"<br>" ;
	echo $countryname,"<br>" ;
  echo "</td>" ;
  echo "<td align=center  bgcolor=#ffffcc rowspan=3 valign=center>" ;
	if ($photo!="") {
		echo "<table bgcolor=#ffffcc width=100%>" ;
		echo "<tr><td>" ;
	  echo "<img src=\"".$photo."\" height=200 alt=\"$phototext\"><br>" ;
		echo "</td>" ;
		echo "<td valign=center>" ;
		echo "<form method=post action=myphotos.php><input type=submit value=\"",ww("ModifyYourPhotos"),"\"></form>" ;
		echo "</td>" ;
		echo "<tr><td valign=center colspan=2><font size=1>",$phototext,"</font>" ;
		echo "</td></table>" ;
//		echo "\n<form style=\"display:inline\" method=post>\n<input type=hidden name=action value=previouspic><input type=hidden name=cid value=\"".$m->id."\"><input type=hidden name=photorank value=\"".$photorank."\"><input type=submit value=\"",ww("previouspicture"),"\">\n</form>" ;
		echo "&nbsp;&nbsp;" ;
//		echo "\n<form style=\"display:inline\" method=post>\n<input type=hidden name=action value=nextpicture><input type=hidden name=cid value=\"".$m->id."\"><input type=hidden name=photorank value=\"".$photorank."\"><input type=submit value=\"",ww("nextpicture"),"\">\n</form>\n" ;
	}
	else {
	  echo "no photo" ;
		echo "<form method=post action=myphotos.php><input type=submit value=\"",ww("AddYourPhoto"),"\"></form>" ;
	}
	echo "</td>" ;
	echo "</table>" ;
  echo "</td>" ;

  echo "<tr><td>" ;
  echo ww('ProfileSummary') ;
  echo ":</td>" ;
  echo "<td colspan=2><textarea name=ProfileSummary cols=70 row=6>" ;
  if ($m->ProfileSummary>0) echo FindTrad($m->ProfileSummary) ;
  echo "</textarea></td>" ;

  echo "<tr><td>" ;
  echo ww('ProfileOrganizations') ;
  echo ":</td>" ;
  echo "<td colspan=2><textarea name=Organizations cols=70 row=6>" ;
  if ($m->Organizations>0) echo FindTrad($m->Organizations) ;
  echo "</textarea></td>" ;

	if ($m->Accomodation!="") {
    echo "<tr><td>" ;
    echo ww("ProfileAccomodation") ;
    echo ":</td>" ;
    echo "<td colspan=2>" ;
	  $tt=$_SYSHCVOL['Accomodation'] ;
	  $max=count($tt) ;
		echo "<select name=Accomodation>\n" ;
	  for ($ii=0;$ii<$max;$ii++) {
	    echo "<option value=\"".$tt[$ii]."\"" ;
			if ($tt[$ii]==$m->Accomodation) echo " selected " ;
			echo ">",ww("Accomodation_".$tt[$ii]),"</option>\n" ;
		}
/*		
    echo "<table valign=center style=\"font-size:12;\">" ;
	  for ($ii=0;$ii<$max;$ii++) {
	    echo "<tr><td>",ww("Accomodation_".$tt[$ii]),"</td>" ;
		  echo "<td><input type=checkbox name=\"Accomodation_".$tt[$ii]."\"" ;
			if (in_array($tt[$ii],$tcurrent)) echo " checked " ;
		  echo "></td>" ;

	  }
	  echo "</table></td>" ;
		*/
		echo "</select>\n" ;
    echo "</td>" ;
	}

  echo "<tr><td>" ;
  echo ww('ProfileAdditionalAccomodationInfo') ;
  echo ":</td>" ;
  echo "<td colspan=2><textarea name=AdditionalAccomodationInfo cols=70 row=6>" ;
	if ($m->AdditionalAccomodationInfo>0) {
      echo FindTrad($m->AdditionalAccomodationInfo) ;
	}
  echo "</textarea></td>" ;
	
	$max=count($TGroups) ;
	if ($max>0) {
    echo "\n<tr><th colspan=3><br><br>",ww("MyGroups",$m->Username),"</th>" ;
	  for ($ii=0;$ii<$max;$ii++) {
		  echo "\n<tr><td colpsan=2>",ww("Group_".$TGroups[$ii]->Name),"</td>","<td>" ;
			echo "<textarea cols=70 row=6 name=\"","Group_".$TGroups["$ii"]->Name,"\">" ;
      if ($TGroups[$ii]->Comment>0) echo FindTrad($TGroups[$ii]->Comment) ;
			echo "</textarea>" ;
		  echo "</td>" ;
		}
	}

  echo "\n<tr><td>" ;
  echo ww('SignupBirthDate') ;
  echo "</td>" ;
  echo "<td colspan=2>" ;
	echo $m->BirthDate;
	echo "\n &nbsp;&nbsp;&nbsp;&nbsp; <input Name=HideBirthDate type=checkbox ";
	if ($m->HideBirthDate=="Yes") echo " checked " ;
	echo ">",ww("Hidden") ;
  echo "</td>" ;


	echo "\n<tr><td colspan=3 align=center><input type=submit name=submit value=submit></td>" ; 
  echo "</table>\n" ;
  echo "</form>\n" ;
  echo "</center>\n" ;
  include "footer.php" ;
}
?>
