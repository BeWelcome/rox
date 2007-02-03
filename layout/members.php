<?php
require_once ("Menus.php");

function DisplayMembers($TData) {
	global $title;
	$title = ww('MembersPage' . " " . $_POST['Username']);
	include "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("members.php", ww('MembersPage')); // Displays the second menu

	DisplayHeaderWithColumns(); // Display the header

	$iiMax = count($TData);
	echo "<table>";
	for ($ii = 0; $ii < $iiMax; $ii++) {
		$m = $TData[$ii];
		echo "<tr align=left>";
		echo "<td valign=center align=center>";
		if (($m->photo != "") and ($m->photo != "NULL")) {
			echo "<div id=\"topcontent-profile-photo\">\n";
            echo LinkWithPicture($m->Username,$m->photo) ;
			echo "<br>" ;
			echo "</div>";
		}
		echo "</td>";
		echo "<td valign=center>", LinkWithUsername($m->Username), "</td>";
		echo " <td valign=center>", $m->countryname, "</td> ";
		echo "<td valign=center>";
		echo $m->ProfileSummary;

		echo "</td>";
		echo "</tr>";
	}
	echo "</table>";

	include "footer.php";
}
?>
