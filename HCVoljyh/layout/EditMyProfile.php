<?php
require_once("Menus.php") ;

function DisplayEditMyProfile($m,$photo="",$phototext="",$photorank=0,$cityname,$regionname,$countryname,$profilewarning="") {

  global $title,$_SYSHCVOL ;
  $title=ww('EditMyProfilePageFor',$m->Username) ;
  include "header.php" ;

  ProfileMenu("EditMyProfile.php",ww('MainPage'),$m->id) ;
	if ($profilewarning!="") {
    echo "<center><H1>",$profilewarning,"</H1></center>\n" ;
	}
	else {
    echo "<center><H1>",$m->Username,"</H1></center>\n" ;
	}
	$rCurLang=LoadRow("select * from languages where id=".$_SESSION['IdLanguage']) ;
  echo "\n<center>\n" ;
  echo "<table width=30%><tr><td bgcolor=#ffff66>",ww("WarningYouAreWorkingIn",$rCurLang->Name,$rCurLang->Name),"</td></table>\n" ;
  echo "<table width=50%>\n" ;
  echo "<form method=post>" ;
	if (IsAdmin()) { // admin can alter other profiles so in case it was not his own we must create a parameter
    echo "<input type=hidden name=cid value=",$m->id,">" ;
	}

	echo "<input type=hidden name=action value=update>" ;
  echo "<tr><td>" ;
  echo ww('Location') ;
  echo "</td>" ;
  echo "<td>" ;
	echo $cityname,"<br>" ;
	echo $regionname,"<br>" ;
	echo $countryname,"<br>" ;
  echo "</td>" ;
  echo "<td align=center  bgcolor=#ffffcc >" ;
	if ($photo!="") {
	  echo "photo<br>" ;
	  echo "<img src=\"".$photo."\" height=200 alt=\"$phototext\"><br>" ;
		echo "<table bgcolor=#ffffcc width=40%><tr><td ><font size=1>",$phototext,"</font></td></table><br>" ;
//		echo "\n<form style=\"display:inline\" method=post>\n<input type=hidden name=action value=previouspic><input type=hidden name=cid value=\"".$m->id."\"><input type=hidden name=photorank value=\"".$photorank."\"><input type=submit value=\"",ww("previouspicture"),"\">\n</form>" ;
		echo "&nbsp;&nbsp;" ;
//		echo "\n<form style=\"display:inline\" method=post>\n<input type=hidden name=action value=nextpicture><input type=hidden name=cid value=\"".$m->id."\"><input type=hidden name=photorank value=\"".$photorank."\"><input type=submit value=\"",ww("nextpicture"),"\">\n</form>\n" ;
	}
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
    echo ww('ProfileAccomodation') ;
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

	echo "<tr><td colspan=3 align=center><input type=submit name=submit value=submit></td>" ; 
  echo "</table>\n" ;
  echo "</center>\n" ;
  include "footer.php" ;
}
?>
