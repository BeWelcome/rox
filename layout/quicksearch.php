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

	echo "          <div class=\"info\">\n";
	echo "            <table border=\"0\">\n";
	
	if ($iiMax>0) { // only display results if they are found entries
		 echo "              <tr valign=\"center\">\n";
		 echo "                <th align=\"left\">", ww("Username"), "</th>\n";
		 echo "                <th>", ww("ProfileSummary"), "</th>\n";
		 echo "                <th>", ww('quicksearchresults', $searchtext), "</th>\n";
		 echo "              </tr>\n";
	}

	for ($ii = 0; $ii < $iiMax; $ii++) {
		if (($ii==0) or ($TList[$ii]->Username!=$TList[$ii-1]->Username)) {  // don't display list with everytime the same username
			 echo "              <tr valign=\"left\">\n";
			 echo "                <td class=\"memberlist\" align=left>\n" ;
			 echo "                  ",LinkWithUsername($TList[$ii]->Username);
			 echo "<br>",$TList[$ii]->CountryName ;
			 echo "\n";
			 echo "                </td>\n";
		}
		else {
			 echo "              <tr align=\"left\">\n";
			 echo "                <td></td>\n" ;
		}
		echo "                <td>";
		if ($TList[$ii]->ProfileSummary > 0)
			echo FindTrad($TList[$ii]->ProfileSummary);
		echo "                </td>\n";
		echo "                <td>", $TList[$ii]->sresult;
		echo "</td>\n";
		echo "               </tr>\n";
		
	}
	echo "            </table>\n";
	
	if ($iiMax==0) {
		echo ww("SorryNoresults",$searchtext) ;
	}

	require_once "footer.php";
}
?>
