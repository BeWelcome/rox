<?php
require_once ("menus.php");

function DisplayWhoIsOnLine($TData) {
	global $title;
	$title = ww('WhoIsOnLinePage');
	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"], ww('WhoIsOnLinePage')); // Displays the second menu

	echo "\n<div id=\"main\">\n";
	echo "  <div id=\"teaser\">";
	echo "					<h3>", ww('WhoIsOnLinePage'), "</h3>\n";
	echo "\n  </div>\n";
	echo "</div>\n";
	
	ShowAds(); // Show the Ads

	echo "		<div id=\"col3\">\n";
	echo "			<div id=\"col3content\">\n";
	echo "				<div class=\"info\">\n";

	$iiMax = count($TData);
	echo "<table>";
	for ($ii = 0; $ii < $iiMax; $ii++) {
		$m = $TData[$ii];
		echo "<tr align=left>";
		echo "<td valign=center align=center>";
		if (($m->photo != "") and ($m->photo != "NULL")) {
			echo "<div id=\"topcontent-profile-photo\">\n";
		    echo LinkWithPicture($m->Username,$m->photo);
//			echo "<a href=\"", $m->photo, "\" title=\"", str_replace("\r\n", " ", $m->phototext), "\">\n<img src=\"" . $m->photo . "\" height=\"100px\" ></a>\n<br>";
			echo "</div>";
		}
		echo "</td>";
		echo "<td valign=center>",LinkWithUsername($m->Username), "</td>";
		echo " <td valign=center>", $m->countryname, "</td> ";
		echo "<td valign=center>";
		//    echo $m->ProfileSummary;
		if (IsAdmin()) {
			echo $m->lastactivity;
		}

		echo "</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "					<div class=\"clear\" />\n";

	echo "\n         </div>\n"; // Class info 

	require_once "footer.php";
	;
}
?>
