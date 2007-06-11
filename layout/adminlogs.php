<?php
require_once ("menus.php");

function DisplayAdminLogs($TData) {
	global $title;
	$title = "Admin logs";
	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("admin/adminlogs.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderShortUserContent($title); // Display the header

	$max = count($TData);
	echo "        <div class=\"info\">\n";
	echo "          <table>\n";
	if ((GetParam(Username) == "") or (GetParam(Username2) != "")) {
		echo "            <tr>\n";
		echo "              <th>Username</th>\n";
		echo "              <th>type</th>\n";
		echo "              <th>Str</th>\n";
		echo "              <th>created</th>\n";
		echo "              <th>ip</th>\n";
		echo "            </tr>\n";
	} else {
		echo "            <tr>\n";
		echo "              <th colspan=4 align=center> Logs for ", LinkWithUsername(GetParam(Username)), "</th>\n";
	}
	for ($ii = 0; $ii < $max; $ii++) {
		$logs = $TData[$ii];
		echo "            <tr>\n";
		if ((GetParam(Username) == "") or (GetParam(Username2) != "")) {
			echo "              <td>";
			echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?Username=" . $logs->Username . "&type=" . $logs->Type . "\">" . $logs->Username . "</a>";
			echo "</td>\n";
		}
		echo "              <td>";
		echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?Username=" . GetParam(Username) . "&type=" . $logs->Type . "\">" . $logs->Type . "</a>";
		//		echo $logs->Type;
		echo "</td>\n";
		echo "              <td>";
		echo $logs->Str;
		echo "</td>\n";
		echo "              <td>$logs->created</td><td>&nbsp;&nbsp;&nbsp;";
		echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?Username=" . GetParam(Username) . "&ip=" . long2ip($logs->IpAddress) . "&type=" . GetParam(type) . "\">" . long2ip($logs->IpAddress) . "</a>";
		echo "</td>\n";
		echo "            </tr>\n";
		}
	echo "          </table>\n";
	echo "          <hr>\n";
	echo "          <table>\n";
	echo "            <form method=post action=adminlogs.php>\n";
	if (HasRight("Logs") > 1) {
		echo "              <tr>\n";
		echo "                <td>Username <input type=text name=Username value=\"", GetParam(Username), "\"></td>\n";
	} else {
		echo "              <tr>\n";
		echo "                <td>Username <input type=text readonly name=Username value=\"", GetParam(Username), "\"></td>";
	}
	echo "                <td>Type <input type=text name=type value=\"", GetParam(type), "\"></td>\n";
	echo "                <td>Ip <input type=text name=ip value=\"", GetParam(ip), "\"></td>\n";
	echo "              </tr>\n";
	echo "              <tr>\n";
	echo "                <td colspan=3 align=center>";
	echo "<input type=submit>";
	echo "</td>\n";
	echo "              </tr>\n";
	echo "            </form>\n";
	echo "          </table>\n";
	echo "        </div>\n";

	require_once "footer.php";

}
?>
