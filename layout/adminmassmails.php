<?php
require_once ("menus.php");

function DisplayAdminMassMails($TData) {
	global $title;
	$title = "Admin Mass Mails";
	include "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("admin/adminmassmails.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderWithColumns($title); // Display the header

	$max = count($TData);
	$max=0 ;
	echo "<table>";
	if ((GetParam(Username) == "") or (GetParam(Username2) != "")) {
		echo "<tr><th>Username</th><th>type</th><th>Str</th><th>created</th><th>ip</th>\n";
	} else {
		echo "<tr><th colspan=4 align=center> Logs for ", LinkWithUsername(GetParam(Username)), "</th>\n";
	}
	for ($ii = 0; $ii < $max; $ii++) {
		$logs = $TData[$ii];
		echo "<tr>";
		if ((GetParam(Username) == "") or (GetParam(Username2) != "")) {
			echo "<td>";
			echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?Username=" . $logs->Username . "&type=" . $logs->Type . "\">" . $logs->Username . "</a>";
			echo "</td>";
		}
		echo "<td>";
		echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?Username=" . GetParam(Username) . "&type=" . $logs->Type . "\">" . $logs->Type . "</a>";
		//		echo $logs->Type;
		echo "</td><td>";
		echo $logs->Str;
		echo "</td><td>$logs->created</td><td>&nbsp;&nbsp;&nbsp;";
		echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?Username=" . GetParam(Username) . "&ip=" . long2ip($logs->IpAddress) . "&type=" . GetParam(type) . "\">" . long2ip($logs->IpAddress) . "</a>";
		echo "</td>\n";
	}
	echo "</table>\n";
	echo "<hr>\n";
	echo "<table>\n";
	echo "<form method=post action=adminlogs.php>\n";
	echo "<tr><td>subject</td><td> <textarea name=subject  rows=1 cols=80>", GetParam(subject), "</textarea></td>";
	echo "<tr><td>body</td><td> <textarea name=body rows=10 cols=80>", GetParam(body), "</textarea></td>";
	echo "<tr><td>greetings</td><td> <textarea name=greetings rows=2 cols=80>", GetParam(greetings), "</textarea></td>";
	echo "\n<tr><td colspan=3 align=center>";
	echo "Send <input type=checkbox name=send> ";
	echo "<input type=submit>";
	echo "</td> ";
	echo "</form>\n";
	echo "</table>\n";

	include "footer.php";

}
function DisplayResults($TData,$Message) {
	global $title;
	$title = "Admin Mass Mails";
	include "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("admin/adminmassmails.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderWithColumns($Message); // Display the header

	include "footer.php";

}
?>

