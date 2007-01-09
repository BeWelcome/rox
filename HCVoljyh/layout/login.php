<?php
require_once("Menus_micha.php") ;
function DisplayLogin($nextlink="") {
  global $title ;
  $title=ww('LoginPage') ;
  include "header_micha.php" ;
	
	Menu1("login.php",ww('login')) ; // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]) ;
	


echo "	\n<div id=\"columns\">\n" ;
echo "		\n<div id=\"columns-low\">\n" ;

echo "<div class=\"clear\" />" ;

echo "\n    <!-- rightnav -->"; 
echo "     <div id=\"columns-right\">\n" ;
echo "       <ul>" ;
echo "         <li class=\"label\">",ww("Ads"),"</li>" ;
echo "         <li></li>" ;
echo "       </ul>\n" ;
echo "     </div>\n" ;

echo "\n    <!-- middlenav -->"; 

echo "     <div id=\"columns-middle\">\n" ;
  echo "					<div id=\"content\">" ;
  echo "						<div class=\"info\">" ;
  echo "<form method=POST action=login.php>\n<table>" ;
	echo "<tr><td colspan=2>",ww("thisisadraft"),"</td>\n" ;
  echo "<input type=hidden name=action value=login>\n" ;
  echo "<input type=hidden name=nextlink value=\"".$nextlink."\">\n" ;
  echo "<tr><td>",ww("username"),"</td><td><input name=Username type=text value='",GetParam("Username"),"'></td>" ;
  echo "<tr><td>",ww("password"),"</td><td><input type=password name=password></td>" ;
  echo "<tr><td colspan=2 align=center><input type=submit value='submit'></td>" ;
  echo "\n</form>\n</table>\n" ;

	echo "<br>" ;
	echo "<br>" ;
	echo "<br>" ;
	echo "<br>" ;

	echo ww("NotYetMember") ;
	echo "<br>" ;
	echo "<br>" ;
	echo ww("SignupLink") ;

echo "					</div>" ;
echo "				</div>" ;
echo "			</div>" ;
echo "		</div>" ;


  include "footer.php" ;
	return ;
}
?>
