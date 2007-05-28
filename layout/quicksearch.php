<?php
require_once ("menus.php");

function DisplayResults($TList, $searchtext = "") {
	global $title;
	$title = ww('quicksearchresults', $searchtext);
	require_once "header.php";

	Menu1("", ww('QuickSearchPage')); // Displays the top menu

	Menu2("quicksearch.php", ww('QuickSearchPage')); // Displays the second menu

	DisplayHeaderShortUserContent($title);
	
	$iiMax = count($TList);

	echo "\n<br><center>\n";
	echo "<table>\n";
	
	if ($iiMax>0) { // only display results if they are found entries
		 echo "<tr valign=center>";
		 echo "<th align=left>", ww("Username"), "</th>";
		 echo "<th>", ww("ProfileSummary"), "</th>";
		 echo "<th>", ww('quicksearchresults', $searchtext), "</th>";
	}

	for ($ii = 0; $ii < $iiMax; $ii++) {
		if (($ii==0) or ($TList[$ii]->Username!=$TList[$ii-1]->Username)) {  // don't display list with everytime the same username
			 echo "<tr ><td colspan=2></td>";
			 echo "<tr valign=center>";
			 echo "<td align=left>" ;
			 echo LinkWithUsername($TList[$ii]->Username);
			 echo "<br>",$TList[$ii]->CountryName ;
			 echo "</td>";
		}
		else {
			 echo "<tr><td></td>" ;
		}
		echo "<td>";
		if ($TList[$ii]->ProfileSummary > 0)
			echo FindTrad($TList[$ii]->ProfileSummary);
		echo "</td>";
		echo "<td>", $TList[$ii]->sresult;
		echo "</td>";
	}
	echo "</table>\n";
	
	if ($iiMax==0) {
		echo ww("SorryNoresults",$searchtext) ;
	}

	echo "</center>\n";
	require_once "footer.php";
}
?>
