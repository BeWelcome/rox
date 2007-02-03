<?php
require_once ("Menus.php");
function DisplayMyVisitors($TData, $Username) {
	global $title, $_SYSHCVOL;
	$title = ww('MyVisitors');
	include "header.php";

	Menu1(); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);

	DisplayHeaderWithColumns(ww("VisitorsFor", $Username)); // Display the header

	$iiMax = count($TData);
	echo "<table>";
	if ($iiMax == 0) {
		echo "<tr><td align=center>", ww("NobodyHasYetVisitatedThisProfile"), "</td>";
	}
	for ($ii = 0; $ii < $iiMax; $ii++) {
		$rr = $TData[$ii];
		echo "<tr align=left>";
		echo "<td valign=center align=center>";
		if (($rr->photo != "") and ($rr->photo != "NULL")) {
			echo "<div id=\"topcontent-profile-photo\">\n";
			echo LinkWithPicture($rr->Username,$rr->photo),"\n<br>";
			echo "</div>";
		}
		echo "</td>";
		echo "<td valign=center>", LinkWithUsername($rr->Username), "</td>";
		echo " <td valign=center>", $rr->countryname, "</td> ";
		echo "<td valign=center>";
		if ($rr->ProfileSummary > 0)
			echo FindTrad($rr->ProfileSummary);

		echo "</td>";
		echo "<td>";
		echo $rr->datevisite;
		echo "</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "					<div class=\"clear\" />\n";

	include "footer.php";

}
?>
