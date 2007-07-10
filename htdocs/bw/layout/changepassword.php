<?php
require_once ("menus.php");

function DisplayChangePasswordForm($CurrentError) {
	global $title;
	$title = ww('ChangePasswordPage');
	require_once "header.php";

	Menu1("", ww('ChangePasswordPage')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);

	echo "\n<div id=\"main\">\n";
	echo "  <div id=\"teaser\">";
	echo "					<h3>", ww("ChangePasswordPage"), "</h3>\n";
	echo "\n  </div>\n";
	echo "</div>\n";

	ShowActions(); // Show the actions
	ShowAds(); // Show the Ads

	echo "		<div id=\"col3\">\n";
	echo "			<div id=\"col3_content\">\n";
	echo "				<div class=\"info\">\n";

	echo "<center>";
	if ($CurrentError != "") {
		echo $CurrentError;
	}
	echo "<table>\n<form method=post>\n";
	echo "  <input type=hidden name=action value=changepassword>\n";
	echo "<tr><td>", ww("OldPassword"), "</td><td><input type=password name=OldPassword></td>\n";
	echo "<tr><td>", ww("NewPassword"), "</td><td><input type=password name=NewPassword></td>\n";
	echo "<tr><td>", ww("SignupCheckPassword"), "</td><td><input type=password name=SecPassword></td>\n";
	echo "<tr><td colspan=2 align=center><input type=submit id=submit name=submit value=submit></td>\n";
	echo "</form>\n</table></center>\n";

	echo "\n         </div>\n"; // Class info 

	require_once "footer.php";
}
?>
