<?php
require_once ("menus.php");

function DisplayResults($TList, $searchtext = "") {
	global $title;
	$title = ww('quicksearchresults', $searchtext);
	include "header.php";

	Menu1("", ww('QuickSearchPage')); // Displays the top menu

	Menu2("quicksearch.php", ww('QuickSearchPage')); // Displays the second menu

	DisplayHeaderShortUserContent($title);
	
	echo "\n<br><center>\n";
	echo "<table>\n";
	echo "<tr valign=center>";
	echo "<th align=left>", ww("Username"), "</th>";
	echo "<th>", ww("ProfileSummary"), "</th>";
	echo "<th>", ww('quicksearchresults', $searchtext), "</th>";

	$iiMax = count($TList);
	for ($ii = 0; $ii < $iiMax; $ii++) {
		echo "<tr valign=center>";
		echo "<td align=left>", LinkWithUsername($TList[$ii]->Username);
		echo "</td>";
		echo "<td>";
		if ($TList[$ii]->ProfileSummary > 0)
			echo FindTrad($TList[$ii]->ProfileSummary);
		echo "</td>";
		echo "<td>", $TList[$ii]->sresult;
		echo "</td>";
	}
	echo "</table>\n";

	echo "</center>\n";
	include "footer.php";
}
?>
