<?php
require_once("Menus.php") ;
function DisplayLogin() {
  global $title ;
  $title=ww('WelcomePage') ;
  include "header.php" ;
  echo "<H1>",ww('Login'),"</H1>\n" ;
  mainmenu("Main.php",ww('login')) ;
  echo "<center><form method=POST>\n<table>" ;
	echo "<tr><td colspan=2>",ww("thisisadraft"),"</td>" ;
  echo "<input type=hidden name=action value=login>" ;
  echo "<tr><td>",ww("username"),"</td><td><input name=Username type=text value='",$_POST['Username'],"'></td>" ;
  echo "<tr><td>",ww("password"),"</td><td><input type=password name=password></td>" ;
  echo "<tr><td colspan=2 align=center><input type=submit value='",ww("submit"),"'></td>" ;
  echo "\n</form>\n</table>" ;
  echo "</center>" ;
  include "footer.php" ;
	return ;
}
?>
