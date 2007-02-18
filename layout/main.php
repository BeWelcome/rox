<?php
require_once ("Menus.php");

function DisplayMain($m, $CurrentMessage = "") {
	global $title;
	$title = ww('WelcomePage' . " " . $_POST['Username']);
	include "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("main.php", ww('MainPage')); // Displays the second menu

	$ListOfActions="<li><a href=\"editmyprofile.php\">" . ww('EditMyProfile') . "</a></li>\n" ;
	if ($m->NbContacts>0) {
	   $ListOfActions.= "<li><a href=\"mycontacts.php\">" . ww('DisplayAllContacts') . "</a></li>\n"  ;
	}
	$ListOfActions.= VolMenu() ;
	DisplayHeaderWithColumns(ww('MainPage'), "", $ListOfActions) ;

	if ($CurrentMessage != "") {
		echo $CurrentMessage;
		echo "<br>\n";
	}

	echo "<table><tr><td align=left>\n",ww("BetaNews"),"</td></tr>\n</table>\n" ; ;
	echo "\n<center>\n";
	echo "<br>" ;
	echo "You are logged as ", LinkWithUsername($m->Username) . "<br>\n";
	echo ww(17908); // This is the direc code of the main text , not to translate for now
	echo "\n</center>\n";

	include "footer.php";
}
?>
