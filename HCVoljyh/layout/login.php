<?php
require_once("Menus_micha.php") ;
function DisplayLogin($nextlink="") {
  global $title ;
  $title=ww('LoginPage') ;
  include "header_micha.php" ;
	
	Menu1("login.php",ww('login')) ; // Displays the top menu

//	Menu2($_SERVER["PHP_SELF"]) ;
	

echo "	<div id=\"columns\">" ;
  echo "<H2>",ww('Login'),"</H2>\n" ;
  echo "<br><center><form method=POST action=login.php>\n<table>" ;
	echo "<tr><td colspan=2>",ww("thisisadraft"),"</td>\n" ;
  echo "<input type=hidden name=action value=login>\n" ;
  echo "<input type=hidden name=nextlink value=\"".$nextlink."\">\n" ;
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
echo "					</div>\n" ;
echo "					<div class=\"user-content\">" ;
  include "footer.php" ;
echo "					</div>" ;
	return ;
}
?>
