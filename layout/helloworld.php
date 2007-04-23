<?php
require_once ("menus.php"); // load the menu routines

function DisplayHelloWorld() {
	$title = "Hello world page"; // set th etitle of the page (global variable)

	require_once "header.php"; // Load the headers routines
	Menu1("", ""); // Displays the top menu 

	Menu2($_SERVER["PHP_SELF"], $title); // Displays the second menu

	DisplayHeaderShortUserContent(); // Set the header type here, its a simple one

	echo "<br><center> <H2>Hello World of welcome !</H2></center>"; /// here is the output 

	require_once "footer.php"; // This close the header
} // end of DisplayHelloWorld
?>