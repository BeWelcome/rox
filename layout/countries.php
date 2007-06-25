<?php
require_once ("menus.php");

function DisplayCountries($TList) {
	global $title;
	$title = ww('Countries');
	require_once "header.php";

	Menu1("countries.php", ww('Countries')); // Displays the top menu

	Menu2("findpeople.php", ww('findpeoplePage')); // Displays the second menu

	echo "\n";
	echo "    <div id=\"main\">\n";
	echo "      <div id=\"teaser\">\n";
	echo "        <h1>", $Title, " </h1>\n";
	echo "      </div>\n";
	
	menufindmembers("countries.php" . $menutab, $Title);

	// middle column
	echo "\n";
	echo "      <div id=\"col3\"> \n"; 
	echo "        <div id=\"col3_content\" class=\"clearfix\"> \n"; 

	echo "          <div class=\"info\">\n";
	echo "            <ul class=\"floatbox\">\n";

	echo "<ul>\n";

	$iiMax = count($TList);
	for ($ii = 0; $ii < $iiMax; $ii++) {
		echo "              <li>";
		echo "<a href=regions.php?IdCountry=";
		echo $TList[$ii]->IdCountry, ">";
		echo $TList[$ii]->country;
		echo "</a> ";
		echo " <a href=\"findpeople.php?action=Find&IdCountry=",$TList[$ii]->IdCountry,"\">(";
		echo $TList[$ii]->cnt, ")</a>";
		echo "</li>\n";
	}
	echo "            </ul>\n";
	echo "          </div>\n";
  
	require_once "footer.php";
}
?>
