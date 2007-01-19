<?php
require_once ("Menus.php"); // load the menu routines

function DisplayHelloWorld2($Data) {
	$title = "hello world two"; // set the title of the page

	include "header.php"; // Load the headers routines
	Menu1("", ""); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"], $title); // Displays the second menu

	DisplayHeaderShortUserContent(); // Set the header type here, its a simple one

	echo "<br><center><H2>";
	echo ww("HelloWorldFor", $Data->Username); // HelloWorlddFor is a translatable 
	// word like "Hello World for %s" in english 
	// or "Bonjour le monde à %s" in French, 
	// or "Hallo Welt für %s in German etc, etc
	// here %s will be replaced by the username

	echo "!</H2></center>"; /// here is the output 
	echo "<br><br>click on the French and then on the German flag and see how the language change !";

	include "footer.php"; // This close the header
} // end of DisplayHelloWorld2
?>