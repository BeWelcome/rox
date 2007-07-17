<?php
require_once ("menus.php");
require_once ("profilepage_header.php");

function DisplayMyTranslators($TData, $m) {
	global $title, $_SYSHCVOL;
	$title = ww('MyTranslators');
	require_once "header.php";

	Menu1(); // Displays the top menu
	Menu2("mytranslators.php", ww('MainPage')); // Displays the second menu

	// Header of the profile page
	DisplayProfilePageHeader( $m );

	menumember("mytranslators.php?cid=" . $m->id, $m);
	ShowActions(""); // Show the Actions
	ShowAds(); // Show the Ads
	// open col3 (middle column)
	echo "    <div id=\"col3\"> \n"; 
	echo "      <div id=\"col3_content\" class=\"clearfix\"> \n";
	echo "			  <div class=\"info\">";

	$iiMax = count($TData);
	echo "<table>";
	if ($iiMax == 0) {
		echo "<tr><td align=center>", ww("YouHaveNoTranslatorsYes"), "</td>";
	}
	for ($ii = 0; $ii < $iiMax; $ii++) {
		$rr = $TData[$ii];
		echo "<tr align=left valign=center>";
		echo "<td align=center>";
		if (($rr->photo != "") and ($rr->photo != "NULL")) {
			echo "<div id=\"topcontent-profile-photo\">\n";
			echo LinkWithPicture($rr->Username,$rr->photo),"\n<br>";
			echo "</div>";
		}
		echo "</td>";
		echo "<td>", LinkWithUsername($rr->Username), "</td>";
		echo " <td>", $rr->countryname, "</td> ";
		echo "<td bgcolor=#ccff99><b> ";
		echo LanguageName($rr->IdLanguage);
		echo " </b></td>";
		echo "<td>";
		echo $rr->ProfileSummary;
		echo "</td>";
		echo "<td>";
		echo "<a href=\"mytranslators.php?action=del&IdTranslator=$rr->IdTranslator\">",ww("RemoveTranslator"),"</a>";
		echo "</td>";
		echo "</tr>";
	}
	echo "</table>";
	echo "<br>";
	echo ww("AddTranslatorsRules");
	echo "<center>";
	echo "<form action=mytranslators.php method=post>";
	echo ww("Username")," <input type=text name=Username value=\"".GetStrParam("Username"),"\">";


	echo " <select name=\"IdLanguage\">";
	echo "<option value=\"\" selected>-", ww("ChooseLanguageToGrant"), "-</option>\n";
	for ($jj = 0; $jj < count($m->TLanguages); $jj++) {
		echo "<option value=\"" . $m->TLanguages[$jj]->id . "\"";
		echo ">", $m->TLanguages[$jj]->EnglishName." / ".$m->TLanguages[$jj]->Name, "</option>\n";
	}
	echo "</select>\n<br>";

	echo "<input type=submit id=submit value=\"",ww("AddTranslator"),"\">"; 
	echo "<input type=hidden name=action value=\"add\">";
	echo "</form>"; 
	
	echo "</div>";



	require_once "footer.php";

} // end of DisplayMyTranslators
?>
