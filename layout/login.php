<?php
require_once ("menus.php");
function DisplayLogin($nextlink = "") {
	global $title;
	$title = ww('LoginPage');
	require_once "header.php";

	Menu1("login.php", ww('login')); // Displays the top menu

	Menu2("");

	DisplayHeaderShortUserContent(); // Display the header

  echo "        <div class=\"info\">\n";
	echo "          <form method=POST action=login.php>\n";
	echo "          <table>\n";
	echo "            <tr>\n";
	echo "              <td colspan=2>",  "</td>\n";
	echo "                <input type=hidden name=action value=login>\n";
	echo "                <input type=hidden name=nextlink value=\"" . $nextlink . "\">\n";
	echo "              </tr>\n";
	echo "            <tr>\n";
	echo "              <td>", ww("username"), "</td>\n";
	echo "              <td><input name=Username type=text value='", GetParam("Username"), "'></td>\n";
	echo "            <tr>\n";
	echo "              <td>", ww("password"), "</td>\n";
	echo "              <td><input type=password name=password></td>\n";
	echo "            </tr>\n";
	echo "            <tr>\n";
	echo "              <td colspan=2 align=center><input type=submit value='submit'></td>\n";
	echo "            </tr>\n";
	echo "          </table>\n";	
	echo "          </form>\n";
  echo "\n";
	echo "          <p>";
	echo ww("NotYetMember");
	echo "<br />";
	echo ww("SignupLink");
	echo "</p>\n";
	echo "</div>\n";

	require_once "footer.php";
	return;
}
?>
