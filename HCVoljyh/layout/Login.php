<?php
function DisplayLogin() {
  global $title ;
  $title=ww('WelcomePage') ;
  include "header.php" ;
  echo "<H1>",ww('Login'),"</H1>\n" ;
  mainmenu("Main.php",ww('login')) ;
  echo "\n<form method=POST>" ;
  echo "\n<center><table>" ;
  echo "<input type=hidden name=action value=login>" ;
  echo "<tr><td>",ww("username"),"</td><td><input name=Username type=text value='",$_POST['Username'],"'></td>" ;
  echo "<tr><td>",ww("password"),"</td><td><input type=password name=password></td>" ;
  echo "<tr><td colspan=2 align=center><input type=submit value='",ww("submit"),"'></td>" ;
  echo "\n</table></form>" ;
  echo "</center>" ;
  include "footer.php" ;
	return ;
}
?>
