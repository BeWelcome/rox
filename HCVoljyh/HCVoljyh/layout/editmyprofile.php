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
  echo "<table width=50%>\n<tr><td bgcolor=#ffff66>",ww("WarningYouAreWorkingIn",$rCurLang->Name,$rCurLang->Name),"</td>\n</table>\n" ;
  echo "<table width=80%>\n" ;
  
  echo "<tr><td align=center  bgcolor=#ffffcc colspan=3 valign=center>" ;
	if ($photo!="") {
		echo "\n<table bgcolor=#ffffcc width=100%>" ;
		echo "\n<tr><td>" ;
	  echo "<img src=\"".$photo."\" height=200 alt=\"$phototext\"><br>" ;
		echo "</td>" ;
		echo "<td valign=center>" ;
		echo "\n <form method=post action=myphotos.php>\n <input type=hidden name=cid value=",$m->id,">\n <input type=submit value=\"",ww("ModifyYourPhotos"),"\">\n </form>\n" ;
		echo "</td>" ;
		echo "\n<tr><td valign=center colspan=2><font size=1>",$phototext,"</font>" ;
		echo "</td>" ;
	  echo "\n</table>\n" ;
	}
	else {
	  echo "no photo" ;
		echo "\n<form method=post action=myphotos.php>\n<input type=hidden name=cid value=",$m->id,">\n<input type=submit value=\"",ww("AddYourPhoto"),"\">\n</form>\n" ;
	}
  echo "</td>" ;

  echo "\n<form method=post action=editmyprofile.php>" ;
	if (IsAdmin()) { // admin can alter other profiles so in case it was not his own we must create a parameter
    echo "<input type=hidden name=cid value=",$m->id,">" ;
	}
	echo "<input type=hidden name=action value=update>" ;

  echo "\n<tr><td>" ;
  echo ww('FirstName') ;
  echo "</td>" ;
  echo "<td width=50%>" ;
	echo MemberReadCrypted($m->FirstName),"</td><td align=left>",ww("cryptedhidden")," <input type=checkbox name=IsHidden_FirstName " ;
	if (IsCrypted($m->FirstName)) echo " checked" ;
	echo "></td> " ;

  echo "\n<tr><td>" ;
  echo ww('SecondName') ;
  echo "</td>" ;
  echo "<td colspan=1>" ;
	echo MemberReadCrypted($m->SecondName),"</td><td align=left>",ww("cryptedhidden")," <input type=checkbox name=IsHidden_SecondName " ;
	if (IsCrypted($m->SecondName)) echo " checked" ;
	echo "></td> " ;

  echo "\n<tr><td>" ;
  echo ww('LastName') ;
  echo "</td>" ;
  echo "<td colspan=1>" ;
	echo strtoupper(MemberReadCrypted($m->LastName)),"</td><td align=left>",ww("cryptedhidden")," <input type=checkbox name=IsHidden_LastName " ;
	if (IsCrypted($m->LastName)) echo " checked" ;
	echo "></td> " ;

  echo "\n<tr><td>" ;
  echo ww('ProfileHomePhoneNumber') ;
  echo "</td>" ;
  echo "<td colspan=2>" ;
	echo "<input type=text name=HomePhoneNumber value=\"",MemberReadCrypted($m->HomePhoneNumber),"\"> ",ww("cryptedhidden"),"<input type=checkbox name=IsHidden_HomePhoneNumber " ;
	if (IsCrypted($m->HomePhoneNumber)) echo " checked" ;
	echo "></td> " ;

  echo "<td colspan=2>" ;
  echo "\n<tr><td>" ;
  echo ww('ProfileCellPhoneNumber') ;
  echo "</td>" ;
  echo "<td colspan=2>" ;
	echo "<input type=text name=CellPhoneNumber value=\"",MemberReadCrypted($m->CellPhoneNumber),"\"> ",ww("cryptedhidden"),"<input type=checkbox name=IsHidden_CellPhoneNumber " ;
	if (IsCrypted($m->CellPhoneNumber)) echo " checked" ;
	echo "></td> " ;
  echo "<td colspan=2>" ;
	

  echo "\n<tr><td>" ;
  echo ww('ProfileWorkPhoneNumber') ;
  echo "</td>" ;
  echo "<td colspan=2>" ;
	echo "<input type=text name=WorkPhoneNumber value=\"",MemberReadCrypted($m->WorkPhoneNumber),"\"> ",ww("cryptedhidden"),"<input type=checkbox name=IsHidden_WorkPhoneNumber " ;
	if (IsCrypted($m->WorkPhoneNumber)) echo " checked" ;
	echo "></td> " ;
  echo "<td colspan=2>" ;
	
  echo "\n<tr><td>" ;
  echo "SKYPE :" ;
  echo "</td>" ;
  echo "<td colspan=2>" ;
	echo "<input type=text name=chat_SKYPE value=\"",MemberReadCrypted($m->chat_SKYPE),"\"> ",ww("cryptedhidden"),"<input type=checkbox name=IsHidden_chat_SKYPE " ;
	if (IsCrypted($m->chat_SKYPE)) echo " checked" ;
	echo "></td> " ;
	
  echo "\n<tr><td>" ;
  echo "ICQ :" ;
  echo "</td>" ;
  echo "<td colspan=2>" ;
	echo "<input type=text name=chat_ICQ value=\"",MemberReadCrypted($m->chat_ICQ),"\"> ",ww("cryptedhidden"),"<input type=checkbox name=IsHidden_chat_ICQ " ;
	if (IsCrypted($m->chat_ICQ)) echo " checked" ;
	echo "></td>  " ;
	
  echo "\n<tr><td>" ;
  echo "MSN :" ;
  echo "</td>" ;
  echo "<td colspan=2>" ;
	echo "<input type=text name=chat_MSN value=\"",MemberReadCrypted($m->chat_MSN),"\"> ",ww("cryptedhidden"),"<input type=checkbox name=IsHidden_chat_MSN " ;
	if (IsCrypted($m->chat_MSN)) echo " checked" ;
	echo "></td>  " ;
	
  echo "\n<tr><td>" ;
  echo "AOL :" ;
  echo "</td>" ;
  echo "<td colspan=2>" ;
	echo "<input type=text name=chat_AOL value=\"",MemberReadCrypted($m->chat_AOL),"\"> ",ww("cryptedhidden"),"<input type=checkbox name=IsHidden_chat_AOL " ;
	if (IsCrypted($m->chat_AOL)) echo " checked" ;
	echo "></td> ";
	
  echo "\n<tr><td>" ;
  echo "YAHOO :" ;
  echo "</td>" ;
  echo "<td colspan=2>" ;
	echo "<input type=text name=chat_YAHOO value=\"",MemberReadCrypted($m->chat_YAHOO),"\"> ",ww("cryptedhidden"),"<input type=checkbox name=IsHidden_chat_YAHOO " ;
	if (IsCrypted($m->chat_YAHOO)) echo " checked" ;
	echo "></td> ";
			
  echo "\n<tr><td>" ;
  echo ww("chat_others")," :" ;
  echo "</td>" ;
  echo "<td colspan=2>" ;
	echo "<input type=text name=chat_Others value=\"",MemberReadCrypted($m->chat_Others),"\"> ",ww("cryptedhidden"),"<input type=checkbox name=IsHidden_chat_Others " ;
	if (IsCrypted($m->chat_Others)) echo " checked" ;
	echo "></td> ";
	

  echo "\n<tr><td>" ;
  echo ww('Location') ;
  echo "</td>" ;
  echo "<td>" ;
	echo $cityname,"<br>" ;
	echo $regionname,"<br>" ;
	echo $countryname,"<br>" ;
  echo "</td>" ;

  echo "<tr><td>" ;
  echo ww('ProfileSummary') ;
  echo ":</td>" ;
  echo "<td colspan=2><textarea name=ProfileSummary cols=70 rows=8>" ;
  if ($m->ProfileSummary>0) echo FindTrad($m->ProfileSummary) ;
  echo "</textarea></td>" ;

  echo "<tr><td>" ;
  echo ww('ProfileOccupation') ;
  echo ":</td>" ;
  echo "<td colspan=2><input type=text name=Occupation value=\"" ;
  if ($m->Occupation>0) echo FindTrad($m->Occupation) ;
  echo "\"></td>" ;

  echo "<tr><td>" ;
  echo ww('MotivationForHospitality') ;
  echo ":</td>" ;
  echo "<td colspan=2><textarea name=MotivationForHospitality cols=70 rows=6>" ;
  if ($m->MotivationForHospitality>0) echo FindTrad($m->MotivationForHospitality) ;
  echo "</textarea></td>" ;
	
/* todo process this with the address
  echo "<tr><td>" ;
  echo ww('GettingHere') ;
  echo ":</td>" ;
  echo "<td colspan=2><textarea name=IdGettingThere cols=70 rows=6>" ;
  if ($m->IdGettingThere>0) echo FindTrad($m->IdGettingThere) ;
  echo "</textarea></td>" ;
*/
	
  echo "<tr><td>" ;
  echo ww('Website') ;
  echo ":</td>" ;
  echo "<td colspan=2><textarea name=WebSite cols=70 rows=1 >",$m->WebSite,"</textarea></td>" ;


	$max=count($TGroups) ;
	if ($max>0) {
    echo "\n<tr><th colspan=3><br><br>",ww("MyGroups",$m->Username),"</th>" ;
	  for ($ii=0;$ii<$max;$ii++) {
		  echo "\n<tr><td colpsan=2>",ww("Group_".$TGroups[$ii]->Name),"</td>","<td>" ;
			echo "<textarea cols=70 rows=6 name=\"","Group_".$TGroups["$ii"]->Name,"\">" ;
      if ($TGroups[$ii]->Comment>0) echo FindTrad($TGroups[$ii]->Comment) ;
			echo "</textarea>" ;
		  echo "</td>" ;
		}
	}

  echo "<tr><td>" ;
  echo ww('ProfileOrganizations') ;
  echo ":</td>" ;
  echo "<td colspan=2><textarea name=Organizations cols=70 rows=6>" ;
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
  echo ww('ProfileNumberOfGuests') ;
  echo ":</td>" ;
  echo "<td colspan=2><input name=MaxGuest type=text size=3 value=\"",$m->MaxGuest ;
  echo "\"></td>" ;
	
  echo "<tr><td>" ;
  echo ww('ProfileMaxLenghtOfStay') ;
  echo ":</td>" ;
  echo "<td colspan=2><input name=MaxLenghtOfStay type=text size=70 value=\"" ;
  if ($m->MaxLenghtOfStay>0) echo FindTrad($m->MaxLenghtOfStay) ;
  echo "\"></td>" ;
	
  echo "<tr><td>" ;
  echo ww('ProfileILiveWith') ;
  echo ":</td>" ;
  echo "<td colspan=2><input name=ILiveWith type=text size=70 value=\"" ;
  if ($m->ILiveWith>0) echo FindTrad($m->ILiveWith) ;
  echo "\"></td>" ;
	

  echo "<tr><td>" ;
  echo ww('ProfileAdditionalAccomodationInfo') ;
  echo ":</td>" ;
  echo "<td colspan=2><textarea name=AdditionalAccomodationInfo cols=70 rows=6>" ;
	if ($m->AdditionalAccomodationInfo>0) {
      echo FindTrad($m->AdditionalAccomodationInfo) ;
	}
  echo "</textarea></td>" ;


  $max=count($m->TabRestrictions) ;

  echo "<tr><td>" ;
  echo ww('ProfileRestrictionForGuest') ;
  echo ":</td>" ;
  echo "<td colspan=2>\n<ul>" ;
	for ($ii=0;$ii<$max;$ii++) {
	  echo "<input type=checkbox name=\"check_".$m->TabRestrictions[$ii]."\" " ;
	  if (strpos($m->Restrictions,$m->TabRestrictions[$ii])!==false) echo "checked" ;
		echo ">" ;
	  echo "&nbsp;&nbsp;",ww("Restriction_".$m->TabRestrictions[$ii]),"<br>\n" ;
	}
  echo "</ul></td>" ;

	
  echo "<tr><td>" ;
  echo ww('ProfileOtherRestrictions') ;
  echo ":</td>" ;
  echo "<td colspan=2><ul><textarea name=OtherRestrictions cols=70 rows=3>" ;
	if ($m->OtherRestrictions>0) {
      echo FindTrad($m->OtherRestrictions) ;
	}
  echo "</textarea></ul></td>" ;

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
  echo "</form>\n" ;
  echo "</table>\n" ;
  echo "</center>\n" ;
  include "footer.php" ;
}
?>
