<?php
require_once ("Menus_micha.php");

function DisplayWhoIsOnLine($TData) {
	global $title;
	$title = ww('WhoIsOnLinePage');
	include "header_micha.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"], ww('WhoIsOnLinePage')); // Displays the second menu

	echo "\n<div id=\"maincontent\">\n";
	echo "  <div id=\"topcontent\">";
	echo "					<h3>", ww('WhoIsOnLinePage'), "</h3>\n";
	echo "\n  </div>\n";
	echo "</div>\n";

	echo "\n  <div id=\"columns\">\n";
	//menumember("member.php?cid=".$m->id,$m->id,$NbComment) ;
	echo "		<div id=\"columns-low\">\n";

	echo "    <!-- leftnav -->\n";
	echo "     <div id=\"columns-left\">\n";
	echo "       <div id=\"content\">\n";
	echo "         <div class=\"info\">\n";
	echo "           <h3></h3>";

	echo "           <ul>";
	echo "           </ul>";
	echo "         </div>"; // Class info 
	echo "       </div>\n"; // content
	echo "     </div>\n"; // columns-left

	echo "     <div id=\"columns-right\">\n";
	echo "       <ul>";
	echo "         <li class=\"label\">", ww("Ads"), "</li>";
	echo "         <li></li>";
	echo "       </ul>\n";
	echo "     </div>\n"; // columns rights

	echo "		<div id=\"columns-middle\">\n";
	echo "			<div id=\"content\">\n";
	echo "				<div class=\"info\">\n";

	$iiMax = count($TData);
	echo "<table>";
	for ($ii = 0; $ii < $iiMax; $ii++) {
		$m = $TData[$ii];
		echo "<tr align=left>";
		echo "<td valign=center align=center>";
		if (($m->photo != "") and ($m->photo != "NULL")) {
			echo "<div id=\"topcontent-profile-photo\">\n";
			echo "<a href=\"", $m->photo, "\" title=\"", str_replace("\r\n", " ", $m->phototext), "\">\n<img src=\"" . $m->photo . "\" height=\"100px\" ></a>\n<br>";
			echo "</div>";
		}
		echo "</td>";
		echo "<td valign=center>",LinkWithPicture($m->Username,$m->photo), "</td>";
		echo " <td valign=center>", $m->countryname, "</td> ";
		echo "<td valign=center>";
		//    echo $m->ProfileSummary ;
		if (IsAdmin()) {
			echo $m->lastactivity;
		}

		echo "</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "					<div class=\"clear\" />\n";

	echo "\n         </div>\n"; // Class info 
	echo "       </div>\n"; // content
	echo "     </div>\n"; // columns-midle

	echo "   </div>\n"; // columns-low
	echo " </div>\n"; // columns

	include "footer.php";
	;
}
?>
