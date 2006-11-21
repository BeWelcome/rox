<?php
require_once("Menus.php") ;

function DisplayMember($m,$photo="",$phototext="",$photorank=0,$cityname,$regionname,$countryname) {
  global $title ;
  $title=ww('ProfilePageFor',$m->Username) ;
  include "header.php" ;

  ProfileMenu("Member.php",ww('MainPage'),$m->id) ;
  echo "<center><H1>",$m->Username,"</H1></center>\n" ;
  echo "\n<center>\n" ;
  echo "<table>\n" ;

  echo "<tr><td>" ;
  echo ww('Username') ;
  echo "</td>" ;
  echo "<td>" ;
  echo $m->Username,"<br>"  ;
	echo $cityname,"<br>" ;
	echo $regionname,"<br>" ;
	echo $countryname,"<br>" ;
  echo "</td>" ;
  echo "<td align=center>" ;
	if ($photo!="") {
	  echo "photo<br>" ;
	  echo "<img src=\"".$photo."\" height=200 alt=\"$phototext\"><br>" ;
		echo "<font size=1>",$phototext,"</font><br>" ;
		echo "\n<form style=\"display:inline\" method=post>\n<input type=hidden name=action value=previouspic><input type=hidden name=cid value=\"".$m->id."\"><input type=hidden name=photorank value=\"".$photorank."\"><input type=submit value=\"",ww("previouspicture"),"\">\n</form>" ;
		echo "&nbsp;&nbsp;" ;
		echo "\n<form style=\"display:inline\" method=post>\n<input type=hidden name=action value=nextpicture><input type=hidden name=cid value=\"".$m->id."\"><input type=hidden name=photorank value=\"".$photorank."\"><input type=submit value=\"",ww("nextpicture"),"\">\n</form>\n" ;
	}
  echo "</td>" ;

  echo "<tr><td>" ;
  echo ww('ProfileSummary') ;
  echo ":</td>" ;
  echo "<td colspan=2>" ;
  echo FindTrad($m->ProfileSummary) ;
  echo "</td>" ;

  echo "</table>\n" ;
  echo "</center>\n" ;
  include "footer.php" ;
}
?>
