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
	echo "<tr><td>",ww("YourMessageFor",LinkWithUsername($m->Username)),"</td><td colspan=2 width=\"60%\"><textarea name=Message rows=15 cols=80>",$Message,"</textarea>" ;
	echo "<tr><td colspan=3>",ww("IamAwareOfSpamCheckingRules"),"</td><td colspan=1>",ww("IAgree"),"<input type=checkbox name=IamAwareOfSpamCheckingRules></td>" ;
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
