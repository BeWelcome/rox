<?php
require_once ("Menus.php");

function DisplayIndexLogged($Username) {
	global $title;
	$title = ww('IndexPage') ;

	include "header.php";

	Menu1("", $title); // Displays the top menu
	Menu2("", ww('MainPage')); // Displays the second menu

	echo "<br><br><br><br><br><br><br><br><center><table width=\"60%\">" ;
	echo "\n<tr><td colspan=2 align=center>" ;
	echo "Hello <b>",$Username,"</b>\n";
	echo "</td>" ;
	echo "</table>\n" ;
	echo "</center>\n";

	echo "</center>\n";
	include "footer.php";
}

function DisplayNotLogged() {
	global $title;
	$title = ww('IndexPage') ;

	include "header.php";

	Menu1("", $title); // Displays the top menu
	Menu2("", $title); // Displays the second menu

    DisplayHeaderShortUserContent($title);

	echo "<center><table width=\"60%\">" ;
	echo "\n<tr><td colspan=2>" ;
	echo ww("AboutUsText");
	echo "</td>" ;
	echo "\n<tr align=center><td>" ;
	echo "<a href=\"login.php\">",ww("Login"),"</a>" ;
	echo "</td>" ;
	echo "<td>" ;
	echo " <a href=\"signup.php\">",ww("Signup"),"</a>" ;
	echo "</td>\n" ;
	echo "</table>\n" ;
	echo "</center>\n";
	include "footer.php";
}
?>
