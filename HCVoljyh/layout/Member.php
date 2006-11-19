<?php
function DisplayMember($m,$photo="",$phototext="",$photorank=0) {
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
  echo $m->Username ;
  echo "</td>" ;
  echo "<td align=center>" ;
	if ($photo!="") {
	  echo "photo<br>" ;
	  echo "<img src=\"".$photo."\" height=200 alt=\"$phototext\"><br>" ;
		echo "<font size=1>",$phototext,"</font><br>" ;
		echo "<a href=\"$PHP_SELF?cid=".$m->id."&photorank=$photorank&previouspic=1\">",ww("previouspicture"),"</a>&nbsp;&nbsp" ;
		echo "<a href=\"$PHP_SELF?cid=".$m->id."&photorank=$photorank&nextpic=1\">",ww("nextpicture"),"</a>" ;
	}
  echo "</td>" ;

  echo "<tr><td>" ;
  echo ww('ProfileSummary') ;
  echo "</td>" ;
  echo "<td colspan=2>" ;
  echo FindTrad($m->ProfileSummary) ;
  echo "</td>" ;

  echo "</table>\n" ;
  echo "</center>\n" ;
  include "footer.php" ;
}
?>
