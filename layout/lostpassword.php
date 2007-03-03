<?php
require_once ("Menus.php");

function DisplayLostPasswordForm($CurrentError) {
	global $title;
	$title = ww('LostPasswordPage');
	include "header.php";

	Menu1("", ww('LostPasswordPage')); // Displays the top menu
	Menu2($_SERVER["PHP_SELF"]);

	DisplayHeaderWithColumns(ww("ChangePasswordPage")); // Display the header

	echo "<center>";
	if ($CurrentError != "") {
		echo $CurrentError;
	}
	echo ww("localpasswordrule"),"<br><br>" ;
	
	echo "<table>\n<form method=post>\n";
	echo "  <input type=hidden name=action value=sendpassword>\n";
	echo "<tr><td>", ww("UserNameOrEmail"), "</td><td><input type=text name=UserNameOrEmail></td>\n";
	echo "<tr><td colspan=2 align=center><input type=submit name=submit value=submit></td>\n";
	echo "</form>\n</table>" ;
	echo "</center>\n";

	include "footer.php";
}

function DisplayResult( $Result = "") {
	global $title;
	$title = ww('ContactMemberPageFor', $m->Username);
	include "header.php";

	Menu1("", ww('LostPasswordPage')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);

	DisplayHeaderWithColumns(ww("LostPasswordPage")); // Display the header

	echo "<center>";

	echo "<table width=50%><tr><td><h4>";
	echo $Result;
	echo "</h4></td></table>\n";
	echo "</center>";

	include "footer.php";

} // end of display result

?>
