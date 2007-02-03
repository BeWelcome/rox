<?php
require_once ("Menus.php");

function DisplayMain($m, $CurrentMessage = "") {
	global $title;
	$title = ww('WelcomePage' . " " . $_POST['Username']);
	include "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("main.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderWithColumns(ww('MainPage'), "", "<li><a href=\"editmyprofile.php\">" . ww('EditMyProfile') . "</a></li>\n" . VolMenu()); // Display the header

	if ($CurrentMessage != "") {
		echo $CurrentMessage;
		echo "<br>\n";
	}

	echo "\n<center>\n";
	echo "You are logged as ", LinkWithUsername($m->Username) . "<br>\n";
	echo ww(17908); // This is the direc code of the main text , not to translate for now
	echo "\n</center>\n";

	include "footer.php";
}
?>
