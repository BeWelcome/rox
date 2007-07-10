<?php
require_once ("menus.php");
function DisplayLostPasswordForm($CurrentError) {
	global $title;
	$title = ww('LostPasswordPage');
	require_once "header.php";

	Menu1("", ww('LostPasswordPage')); // Displays the top menu
	Menu2($_SERVER["PHP_SELF"]);

	DisplayHeaderShortUserContent(ww("ChangePasswordPage")); // Display the header

	echo "<div class=\"info\">";
	if ($CurrentError != "") {
		echo $CurrentError;
	}
	
	echo "<form method=\"post\">\n";
	echo "<p>",ww("localpasswordrule"),"</p>\n";
	echo "  <input type=hidden name=action value=sendpassword>\n";
	echo "<p><td>", ww("UserNameOrEmail"), "</p>\n";
	echo "<p><input type=text name=UserNameOrEmail></p>\n";
	echo "<p><input type=submit id=submit name=submit value=submit></p>\n";
	echo "</form>\n";
	echo "</div>\n";

	require_once "footer.php";
}

function DisplayResult( $Result = "") {
	global $title;
	$title = ww('LostPasswordPage', $m->Username);
	require_once "header.php";

	Menu1("", ww('LostPasswordPage')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);

	DisplayHeaderWithColumns(ww("LostPasswordPage")); // Display the header

	echo "<center>";

	echo "<table width=50%><tr><td><h4>";
	echo $Result;
	echo "</h4></td></table>\n";
	echo "</center>";

	require_once "footer.php";

} // end of display result

?>
