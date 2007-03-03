<?php
require_once ("menus.php");
function DisplayError($ErrorMessage = "No Error Message") {
	global $title, $errcode;
	$title = ww('ErrorPage');

	include "header.php";

	Menu1("error.php", ww('MainPage')); // Displays the top menu
	Menu2($_SERVER["PHP_SELF"]); // Display the second menu
	DisplayHeaderWithColumns($errcode); // Display the heade

	echo "<table bgcolor=#ffffcc >";
	echo "<TR><td>", $ErrorMessage, "</TD><br>";
	echo "</table>";

	include "footer.php";
	exit (0); // To be sure that member don't go further after an error
}
?>
