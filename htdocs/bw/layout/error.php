<?php
require_once ("menus.php");
function DisplayError($ErrorMessage = "No Error Message") {
	global $title, $errcode;
	$title = ww('ErrorPage');

	require_once "header.php";

	Menu1("error.php", ww('MainPage')); // Displays the top menu
	Menu2($_SERVER["PHP_SELF"]); // Display the second menu
	DisplayHeaderShortUserContent($errcode); // Display the heade

	echo "        <div class=\"info\">";
	echo "<p>", $ErrorMessage, "</p>";
	echo "        </div>";

	require_once "footer.php";
	exit (0); // To be sure that member don't go further after an error
}
?>
