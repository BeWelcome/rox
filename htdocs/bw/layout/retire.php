<?php
require_once ("menus.php");
function DisplayForm($m) {
	global $title;
	$title = ww('RetirePage');
	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("retire.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderShortUserContent("retire.php","",""); // Display the header

	echo "<div class=\"info\">\n";

	echo "<form method=post action=retire.php>\n";
	echo "<input type=hidden name=action value=retire>\n";
	echo "<p>",ww("retire_explanation",$m->FullName),"</p>\n";
	echo "<p>",ww("retire_membercanexplain"),"<p><textarea name=reason cols=60 rows=5></textarea><br>\n" ;
	echo "<p><input type=checkbox name=Complete_retire ";
	echo " onclick=\"return confirm ('".ww("retire_WarningConfirmWithdraw")."');\"> ",ww("retire_fulltickbox")," </p>\n";
	echo "<p align=center><input type=submit  onclick=\"return confirm('".ww("retire_WarningConfirmRetire")."');\"></p>\n";
	echo "</div>\n";

	require_once "footer.php";

} // DisplayForm


function DisplayResults($m,$Message) {
	global $title;
	$title = ww('RetirePage');
	require_once "header.php";

	Menu1("retire.php", ww('MainPage')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);

	DisplayHeaderWithColumns(ww("RetirePage")); // Display the header
	
	echo "<div class=\"info\">\n";
   echo "<p>",$Message,"</p>";
	echo "</div>\n";
	require_once "footer.php";
} // end of DisplayResults


?>
