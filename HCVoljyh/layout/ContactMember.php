<?php
require_once("Menus.php") ;
// $iMes contain eventually the previous messaeg number
function DisplayContactMember($m,$Message="",$iMes=0,$Warning="") {
  global $title ;
  $title=ww('ContactMemberPageFor',$m->Username) ;
  include "header.php" ;

  ProfileMenu("ContactMember.php",ww('MainPage'),$m->id) ;
	echo "<center>" ;
  echo "<H1>Contact ",$m->Username,"</H1>\n" ;
	
	if ($Warning!="") {
	  echo "<br><br><table width=50%><tr><td><h4><font color=red>" ;
		echo $Warning ;
	  echo "</font></h4></td></table>\n" ;
	}
	
	echo "<form method=post>" ;
	echo "<input type=hidden name=action value=sendmessage>" ;
	echo "<input type=hidden name=cid value=\"".$m->id."\">\n" ;
	echo "<input type=hidden name=iMes value=\"".$iMes."\">\n" ;
  echo "<table>\n" ;
	echo "<tr><th colspan=3>",ww("contactmember","<a href=\"Member.php?cid=".$m->Username."\">".$m->Username."</a>"),"</th>";
	echo "<tr><td>",ww("YourMessageFor",$m->Username),"</td><td colspan=2 width=\"70%\"><textarea name=Message rows=15 cols=70>",$Message,"</textarea>" ;
	echo "<tr><td>",ww("IamAwareOfSpamCheckingRules"),"</td><td colspan=2>",ww("IAgree"),"<input type=checkbox name=IamAwareOfSpamCheckingRules></td>" ;
	echo "<tr><td align=center colspan=3><input type=submit name=submit value=submit> <input type=submit name=action value=\"",ww("SaveAsDraft"),"\"></td>" ;
  echo "</table>\n" ;
	echo "</form>" ;
  echo "</center>\n" ;
  include "footer.php" ;
}


function DisplayResult($m,$Message="",$Result="") {
  global $title ;
  $title=ww('ContactMemberPageFor',$m->Username) ;
  include "header.php" ;

  ProfileMenu("ContactMember.php",ww('MainPage'),$m->id) ;
	echo "<center>" ;
  echo "<H1>Contact ",$m->Username,"</H1>\n" ;
	
  echo "<br><br><table width=50%><tr><td><h4>" ;
	echo $Result ;
  echo "</h4></td></table>\n" ;

	
} // end of display result
?>
