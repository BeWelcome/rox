<?php
require_once ("menus.php");
function DisplayAdminView($username, $name, $description, $TDatas, $TDatasVol, $rright, $lastaction) {
	global $countmatch;
	global $title;
	global $AdminRightScope;

	require_once "header.php";
	Menu1("", $title); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"], $title); // Displays the second menu

	DisplayHeaderShortUserContent($title);

  echo "          <div class=\"info highlight\">\n";
	if ($lastaction != "") {
		echo "$lastaction<br>";
	}
	echo "            <p>Your Scope is for <strong>", $AdminRightScope, "</strong> <a href=\"admin/adminrights.php?action=helplist\">help</a></p>\n";

	$max = count($TDatasVol);
	$count = 0;

  echo "            <form method=post method=\"".$_SERVER["PHP_SELF"]."\">\n";
	echo "              <table class=\"admin\" width=70%>\n";
	echo "                <tr>\n";
	echo "                  <td class=\"label\">Username: </td>\n";
	echo "                  <td><input type=text name=username value=\"", $username, "\"></td>\n";
	echo "                  <td>\n";
	echo "                    <input type=hidden name=action value=find>\n";
	echo "                    <input type=submit name=submit value=find>\n";
	echo "                  </td>\n";
	echo "                </tr>\n";
	echo "                <tr>\n";
	echo "                  <td class=\"label\">Right: </td>\n";
	echo "                  <td>\n";
	echo "                    <select name=Name >\n";
	$max = count($TDatas);
    if ($AdminRightScope == "\"All\"") {
	  echo "                      <option value=\"\">-All-</option>\n";
	}
	for ($ii = 0; $ii < $max; $ii++) {
		echo "                      <option value=\"" . $TDatas[$ii]->Name . "\"";
		if ($TDatas[$ii]->Name == $name)
			echo " selected=\"selected\" ";
		echo ">", $TDatas[$ii]->Name;
		echo "</option>\n";
	}
	echo "                    </select>\n";
	echo "                  </td>\n";
	echo "                  <td align=left >";
	if ($description != "") {
		echo "<strong>", $name, "</strong> :<div style=\"font-size:12px; color:gray;\">";
		echo str_replace("\n", "<br>", $description);
		echo "</div>";
	}
	echo "                  </td>\n";
	echo "                </tr>\n";
	echo "              </table>\n";
	echo "            </form>\n";
	echo "            <hr />\n";
		
	$max = count($TDatasVol);
	for ($ii = 0; $ii < $max; $ii++) {
		$rr = $TDatasVol[$ii];
		$count++;
		echo "            <form method=post method=\"".$_SERVER["PHP_SELF"]."\">\n";
		echo "              <input type=hidden name=IdItemVolunteer value=", $rr->id, ">\n";
		echo "              <input type=hidden name=action value=update>\n";
		echo "              <input type=hidden name=username value=\"", $rr->Username, "\">\n";
		echo "              <table class=\"admin\" width=80%>\n";
		if ($username == "") {
		  echo "            <table class=\"admin\" width=80%>\n";
			echo "              <tr>\n";
			echo "                <td>", $rr->Username, "</td>\n";
		}
		echo "                <tr>\n";
		echo "                  <td class=\"label\">Right: </td>\n";
		echo "                  <td><input type=text name=Name readonly value=\"", $rr->Name, "\"></td>\n";
		echo "                </tr>\n";
		echo "                <tr>\n";
		echo "                  <td class=\"label\">Level: </td>\n";
		echo "                  <td><input type=text name=Level value=", $rr->Level, "></td>\n";
		echo "                </tr>\n";
		echo "                <tr>\n";
		echo "                  <td class=\"label\">Scope: </td>\n";
		echo "                  <td><textarea name=Scope rows=1 cols=70>", $rr->Scope, "</textarea></td>\n";
		echo "                </tr>\n";
		echo "                <tr>\n";
		echo "                  <td class=\"label\">Comment: </td>\n";
		echo "                  <td><textarea name=Comment rows=3 cols=70>", $rr->Comment, "</textarea></td>\n";
		echo "                </tr>\n";
		echo "                <tr>\n";
		echo "                  <td colspan=\"3\" valign=center align=center><input type=submit name=submit value=\"update\"></td>\n";
	  echo "                </tr>\n";
	  echo "              </table>\n";
		echo "            </form>\n";
		if (HasRight("Right", $rr->Name)) {
			echo " <a href=\"" . $_SERVER["PHP_SELF"] . "?IdItemVolunteer=", $TDatasVol[$ii]->id, "\" onclick=\"return confirm('Your really want to delete right " . $rr->Name . " for " . $rr->Username . " ?');\">del</a>";
		}
		echo "            <hr />\n";
		echo "            <br />\n";
	}

	if ($username != "") { // If a username is selected propose to add him a right
		echo "            <form method=post  method=\"".$_SERVER["PHP_SELF"]."\">";
		echo "              <table class=\"admin\" width=80%>\n";
		echo "                <tr>\n";
		echo "                  <td class=\"label\">Username: </td>\n";
		echo "                  <td><input type=text readonly name=username value=\"", $username, "\"></td>\n ";
		echo "                </tr>\n";
		echo "                <tr>\n";
		echo "                  <td class=\"label\">Right: </td>\n";
		echo "                  <td>\n";
		$max = count($TDatas);
		echo "                    <select name=Name>\n";
		for ($ii = 0; $ii < $max; $ii++) {
			echo "                      <option value=\"", $TDatas[$ii]->Name, "\">", $TDatas[$ii]->Name, "</option>\n";
		}
		echo "                    </select></td>\n";
		echo "                </tr>\n";
		echo "                <tr>\n";
		echo "                  <td class=\"label\">Level: </td>\n";
		echo "                  <td><input type=text name=Level></td>\n";
		echo "                </tr>\n";
		echo "                <tr>\n";
		echo "                  <td class=\"label\">Scope: </td>\n";
		echo "                  <td><textarea name=Scope rows=1 cols=70></textarea></td>\n";
		echo "                </tr>\n";
		echo "                <tr>\n";
		echo "                  <td class=\"label\">Comment: </td>\n";
		echo "                  <td><textarea name=Comment rows=3 cols=70></textarea></td>\n";
		echo "                </tr>\n";
		echo "                <tr>\n";
		echo "                  <td colspan=\"3\" valign=center align=center>\n";
		echo "                    <input type=hidden name=action value=add>\n";
		echo "                    <input type=submit name=submit value=add>\n";
		echo "                  </td>\n";            
		echo "                </tr>\n";
		echo "              </table>\n";
		echo "            </form>\n";
	}
	echo "          </div>\n";
	require_once "footer.php";
} // DisplayAdmin($username,$name,$TDatas,$TDatasVol,$rright,$lastaction,$scope) {

function DisplayHelpRights($TDatas,$AdminRightScope) {
	global $countmatch;
	global $title;
	global $AdminRightScope;

	require_once "header.php";
	Menu1("", $title); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"], $title); // Displays the second menu

	DisplayHeaderShortUserContent($title);

	// TODO: check the meaning of the next row. $lastaction is not defined
	if ($lastaction != "") {
		echo "$lastaction<br>";
	}
	echo "<p>Your Scope is for <b>", $AdminRightScope, "</b> <a href=\"admin/adminrights.php\">adminrights</a></p>";

	// TODO: check the meaning of the next row. $TDatasVol is not defined
	$max = count($TDatasVol);
	$count = 0;

	echo "<center>\n<table width=90% cellpadding=2 cellspacing=3 border=1>\n";
	echo "<form method=post method=\"".$_SERVER["PHP_SELF"]."\">";
	echo "<tr><td>Right</td><td>Description</td>";
	$max = count($TDatas);
	for ($ii = 0; $ii < $max; $ii++) {
		echo "<tr><td>",$TDatas[$ii]->Name,"</td><td>",str_replace("\n","<br>",$TDatas[$ii]->Description),"</td>";
	}
	echo "</table>\n";
	echo "</center>";
	require_once "footer.php";
} // DisplayHelpRights() 

?>
