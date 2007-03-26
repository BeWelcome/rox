<?php
require_once ("menus.php");

function DisplayMembers($TData) {
	global $title;
	$title = ww('MembersPage' . " " . $_POST['Username']);
	include "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("members.php", ww('MembersPage')); // Displays the second menu

	DisplayHeaderWithColumns(); // Display the header

	$iiMax = count($TData);
	echo "\n<table border=\"1\" rules=\"rows\">\n";
	for ($ii = 0; $ii < $iiMax; $ii++) {
		$m = $TData[$ii];
		echo "<tr align=left valign=center>";
		echo "<td align=center>";
		if (($m->photo != "") and ($m->photo != "NULL")) {
			echo "<div id=\"topcontent-profile-photo\">\n";
            echo LinkWithPicture($m->Username,$m->photo);
			echo "<br>";
			echo "</div>";
		}
		echo "</td>";
		echo "<td>", LinkWithUsername($m->Username), "</td>";
		echo " <td>", $m->countryname, "</td> ";
		echo "<td>";
		echo $m->ProfileSummary;
		echo "</td>";
		echo "</tr>\n";
	}
	echo "</table>\n";

	include "footer.php";
}
?>
