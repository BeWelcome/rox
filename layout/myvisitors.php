<?php
require_once ("menus.php");
function DisplayMyVisitors($TData, $m) {
	global $title, $_SYSHCVOL;
	$title = ww('MyVisitors');
	include "header.php";

	Menu1(); // Displays the top menu
	Menu2("mypreferences.php", ww('MainPage')); // Displays the second menu

	// Header of the profile page
	require_once ("profilepage_header.php");

	echo "	<div id=\"columns\">";
	menumember("myvisitors.php", $m->id, $m->NbComment);
	echo "		<div id=\"columns-low\">";
	// MAIN begin 3-column-part
	echo "    <div id=\"main\">";
	ShowActions(""); // Show the Actions
	ShowAds(); // Show the Ads

	// middle column
	echo "      <div id=\"col3\"> \n"; 
	echo "	    <div id=\"col3_content\" class=\"clearfix\"> \n"; 
	echo "          <div id=\"content\"> \n";
	echo "						<div class=\"info\">";

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

	echo "	</div>";
	echo "	</div>";
	echo "				</div>";
	echo "				<div class=\"clear\" />";
	echo "			</div>	";
	echo "			<div class=\"clear\" />	";
	echo "		</div>	";
	echo "		</div>	";
	echo "	</div>	";


	include "footer.php";

}
?>
