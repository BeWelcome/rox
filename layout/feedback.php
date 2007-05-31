<?php
require_once ("menus.php");

function DisplayResults($Message) {
	global $title;
	$title = ww('FeedbackPage');
	require_once "header.php";

	Menu1("feedback.php", ww('MainPage')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);

	DisplayHeaderWithColumns(ww("ContactUs")); // Display the header
	
   echo $Message;
	require_once "footer.php";
} // end of DisplayResults

function DisplayFeedback($tlist,$IdCategory=0) {
	global $title;
	$title = ww('FeedbackPage');
	require_once "header.php";

	Menu1("feedback.php", ww('MainPage')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);

	DisplayHeaderWithColumns(ww("ContactUs")); // Display the header
   echo "<div class=\"info\">\n"; 
	 echo "<p>", ww("FeedBackDisclaimer"), "</p>\n";
	 echo "<form action=feedback.php method=post>\n";
	 $max = count($tlist);
	 //echo "</div>\n";
	 echo "\n";
	 echo "<div class=\"info highlight\">\n";
	 echo "  <h5>", ww("FeedBackChooseYourCategory"), "</h5>\n";
	 echo "  <p><select name=\"IdCategory\">\n";

	 for ($ii = 0; $ii < $max; $ii++) {
	 	 echo "<option value=" . $tlist[$ii]->id;
		 if ($IdCategory==$tlist[$ii]->id) echo " selected ";
		 echo  ">";
		 echo ww("FeedBackName_" . $tlist[$ii]->Name);
		 echo "</option>\n";
	 }
	 echo "</select>\n</p>\n";
	 echo "<h5>", ww("FeedBackEnterYourQuestion"), "</h5>";
	 echo "<p><textarea name=FeedbackQuestion cols=70 rows=9>", "</textarea></p>\n";
	 echo "<p><input type=checkbox name=urgent> " , ww("FeedBackUrgentQuestion"), "</p>";
	 if (!IsLoggedIn()) {
	 	 echo "<h5>", ww("FeedBackEmailNeeded"), "</h5>";
	 	 echo "<p><input type=\"text\" name=\"Email\" size=\"50\" /></p>";
	 } else {
	  echo "<p><input type=checkbox name=answerneeded> ", ww("FeedBackIWantAnAnswer"), "</p>";
	 }
	 echo "<p><input type=submit name=submit value=submit></p>\n";
	 echo "<input name=action type=hidden value=ask>\n";
	 echo "</div>\n";
	 echo "</form>\n";
	 echo "</div>\n";

	require_once "footer.php";
}
?>
