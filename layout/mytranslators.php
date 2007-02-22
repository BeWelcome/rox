<?php
require_once ("Menus.php");
function DisplayMyTranslators($TData, $m) {
	global $title, $_SYSHCVOL;
	$title = ww('MyTranslators');
	include "header.php";

	Menu1(); // Displays the top menu
	Menu2("mytranslators.php", ww('MainPage')); // Displays the second menu

	// Header of the profile page
	require_once ("profilepage_header.php");

	menumember("mytranslators.php?cid=" . $m->id, $m->id, $m->NbComment);
	echo "	\n<div id=\"columns\">\n";

	echo "		\n<div id=\"columns-low\">\n";
	ShowActions(""); // Show the Actions
	ShowAds(); // Show the Ads

	echo "\n    <!-- middlenav -->\n";

	echo "     <div id=\"columns-middle\">\n";
	echo "					<div id=\"content\">";
	echo "						<div class=\"info\">";

	$iiMax = count($TData);
	echo "<table>";
	if ($iiMax == 0) {
		echo "<tr><td align=center>", ww("YouHaveNoTranslatorsYes"), "</td>";
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
		echo LanguageName($rr->IdLanguage);
		echo "</td>";
		echo "<td>";
		echo "<a href=\"mytranslators.php?action=del&$IdTranslator=$rr->IdTranslator\">",ww("RemoveTranslator"),"</a>";
		echo "</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "<center>" ;
	echo ww("AddTranslatorsRules") ;
	echo "<form action=mytranslators.php method=post>" ;
	echo ww("Username")," <input type=text name=username value=\"".GetParam("username"),"\"><br>" ;
	echo "<input type=submit value=\"",ww("AddTranslator"),"\">" ; 
	echo "<input type=hidden name=action value=\"add\">" ; 
	echo "</form>" ; 
	echo "</center>" ;

	echo "					<div class=\"clear\" />\n";

	echo "					</div>\n"; // info
	echo "				</div>\n"; // content
	echo "			</div>\n"; // middle
	echo "		</div>\n"; // columns


	include "footer.php";

} // end of DisplayMyTranslators
?>
