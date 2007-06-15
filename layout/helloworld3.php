<?php
require_once ("menus.php"); // load the menu routines

function DisplayHelloWorld3($Data) {
	$title = "hello world three"; // set the title of the page
	require_once "header.php"; // Load the headers routines
	Menu1("", "");

	Menu2($_SERVER["PHP_SELF"], $title); // Displays the second menu

	DisplayHeaderShortUserContent(); // Set the header type here, its a simple one

	echo "<br><center><H2>";
	echo ww("HelloWorldFor", $Data->Username), "<br>"; // HelloWorlddFor is a translatable 
	// word like "Hello World for %s" in english 
	// or "Bonjour le monde � %s" in French, 
	// or "Hallo Welt f�r %s in German etc, etc
	// here %s will be replaced by the username

	echo "!</H2></center>"; /// here is the output 

	echo "<br><br>"; // Skip some lines

	echo ww("ProfileSummary"), " : "; // ProfileSummary will be translated according on current language (if translation is available)
	echo FindTrad($Data->ProfileSummary); // Will find the memberstrads text entry according to current language if available

	echo "\n"; // \n mean carraige return in the output page for easiest readibility

	echo "<br><br>"; // Skip some lines

	echo "<form method=post action=helloworld3.php>";
	echo "<input type=hidden name=action value=show_new_user>";
	echo ww("Username"), "<input type=text name=Username><br>";
	echo "<input type=submit>";
	echo "</form>";

	require_once "footer.php"; // This close the header
} // end of DisplayHelloWorld2
?>