<?php
require_once("Menus.php") ;
function DisplayLogin() {
  global $title ;
  $title=ww('LoginPage') ;
  include "header.php" ;
  mainmenu("Login.php",ww('login')) ;
  echo "<H1>",ww('Login'),"</H1>\n" ;
  echo "<center><form method=POST action=Main.php>\n<table>" ;
	echo "<tr><td colspan=2>",ww("thisisadraft"),"</td>" ;
  echo "<input type=hidden name=action value=login>" ;
  echo "<tr><td>",ww("username"),"</td><td><input name=Username type=text value='",GetParam("Username"),"'></td>" ;
  echo "<tr><td>",ww("password"),"</td><td><input type=password name=password></td>" ;
  echo "<tr><td colspan=2 align=center><input type=submit value='submit'></td>" ;
  echo "\n</form>\n</table>" ;
  echo "</center>" ;
	echo "<br>" ;
	echo "<br>" ;
	echo "<br>" ;
	echo "<br>" ;
	echo "<center>" ;
	echo ww("NotYetMember") ;
	echo "<br>" ;
	echo "<br>" ;
	echo ww("SignupLink") ;
	echo "</center>" ;
  include "footer.php" ;
	return ;
}
?>
