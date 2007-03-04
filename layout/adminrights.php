<?php
require_once ("menus.php");
function DisplayAdminView($username, $name, $description, $TDatas, $TDatasVol, $rright, $lastaction) {
	global $countmatch;
	global $title;
	global $AdminRightScope;

	include "header.php";
	Menu1("", $title); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"], $title); // Displays the second menu

	DisplayHeaderShortUserContent($title);

	if ($lastaction != "") {
		echo "$lastaction<br>";
	}
	echo "Your Scope is for <b>", $AdminRightScope, "</b> <a href=\"admin/adminrights.php?action=helplist\">help</a><br>";

	$max = count($TDatasVol);
	$count = 0;

	echo "<center>\n<table width=70%>\n";
	echo "<form method=post method=\"".$_SERVER["PHP_SELF"]."\">";
	echo "<tr><td>Username</td><td><input type=text name=username value=\"", $username, "\"></td><td></td>";
	echo "<td rowspan=2 valign=center>";
	echo "<input type=hidden name=action value=find>";
	echo "<input type=submit name=submit value=find>";
	echo "</td>";
	echo "<tr><td>Right</td><td>";

	echo "\n<select name=Name >\n";
	$max = count($TDatas);
    if ($AdminRightScope == "\"All\"") {
	  echo "<option value=\"\">-All-</option>\n";
	}
	for ($ii = 0; $ii < $max; $ii++) {
		echo "<option value=\"" . $TDatas[$ii]->Name . "\"";
		if ($TDatas[$ii]->Name == $name)
			echo " selected ";
		echo ">", $TDatas[$ii]->Name;
		echo "</option>\n";
	}
	echo "</select>\n";
	echo "</td>";
	echo "<td align=left >";
	if ($description != "") {
		echo "<b>", $name, "</b> :<div style=\"font-size:12px; color:gray;\">";
		echo str_replace("\n", "<br>", $description);
		echo "</div>";
	}
	echo "</td>";
	echo "</form>";
	echo "</table>\n";
	echo "<table width=80%>\n";
	$max = count($TDatasVol);
	for ($ii = 0; $ii < $max; $ii++) {
		$rr = $TDatasVol[$ii];
		$count++;
		echo "<form method=post method=\"".$_SERVER["PHP_SELF"]."\">\n";
		echo "<input type=hidden name=IdItemVolunteer value=", $rr->id, ">";
		echo "<input type=hidden name=action value=update>\n";
		echo "<input type=hidden name=username value=\"", $rr->Username, "\">\n";
		if ($username == "") {
			echo "<tr><td>", $rr->Username;
			echo "</td>";
		}
		echo "<tr><td>Right <input type=text name=Name readonly value=\"", $rr->Name, "\">";
		echo "</td>";
		echo "<td>Level <input type=text name=Level value=", $rr->Level, "></td>";
		echo "<tr><td>scope</td><td><textarea name=Scope rows=1 cols=70>", $rr->Scope, "</textarea></td>";
		echo "<tr><td>Comment</td><td><textarea name=Comment rows=3 cols=70>", $rr->Comment, "</textarea></td>";
		echo "<td valign=center align=left>";
		echo "<input type=submit name=submit value=\"update\">";
		echo "</form>";
		if (HasRight("Right", $rr->Name)) {
			echo " <a href=\"" . $_SERVER["PHP_SELF"] . "?IdItemVolunteer=", $TDatasVol[$ii]->id, "\" onclick=\"return confirm('Your really want to delete right " . $rr->Name . " for " . $rr->Username . " ?');\">del</a>";
		}
		echo "</td>";
		echo "<tr><td colspan=3><hr></td>";
	}

	if ($username != "") { // If a username is selected propose to add him a right
		echo "\n<hr>\n</table><br>\n";
		echo "\n<table width=80%>\n";
		echo "<form method=post  method=\"".$_SERVER["PHP_SELF"]."\">";
		echo "<tr><td align=center colspan=2>";
		echo "Username <input type=text readonly name=username value=\"", $username, "\"> ";
		echo "Right ";
		$max = count($TDatas);
		echo "<select name=Name>\n";
		for ($ii = 0; $ii < $max; $ii++) {
			echo "<option value=\"", $TDatas[$ii]->Name, "\">", $TDatas[$ii]->Name, "</option>\n";
		}
		echo "</select>\n";
		echo "&nbsp;&nbsp;&nbsp;Level <input type=text name=Level></td>";
		echo "<td valign=center rowspan=4>";
		echo "<input type=hidden name=action value=add>";
		echo "<input type=submit name=submit value=add>";
		echo "</td>\n";
		echo "<tr><td>scope</td><td><textarea name=Scope rows=1 cols=70></textarea></td>";
		echo "<tr><td>Comment</td><td><textarea name=Comment rows=3 cols=70></textarea></td>\n";
		echo "</form>";
		echo "</table>\n";
	}
	echo "</center>";
	include "footer.php";
} // DisplayAdmin($username,$name,$TDatas,$TDatasVol,$rright,$lastaction,$scope) {

function DisplayHelpRights($TDatas,$AdminRightScope) {
	global $countmatch;
	global $title;
	global $AdminRightScope;

	include "header.php";
	Menu1("", $title); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"], $title); // Displays the second menu

	DisplayHeaderShortUserContent($title);

	if ($lastaction != "") {
		echo "$lastaction<br>";
	}
	echo "Your Scope is for <b>", $AdminRightScope, "</b> <a href=\"admin/adminrights.php\">adminrights</a><br>";

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
	include "footer.php";
} // DisplayHelpRights() 

?>
