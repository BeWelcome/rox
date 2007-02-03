<?php
require_once ("Menus.php");

function DisplayFeedback($tlist,$Message="",$IdCategory=0) {
	global $title;
	$title = ww('FeedbackPage');
	include "header.php";

	Menu1("feedback.php", ww('MainPage')); // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]);

	DisplayHeaderWithColumns(ww("ContactUs")); // Display the header
	
	if ($Message!="") {
	    echo $Message ;
	}
	else {

		 echo "<table>\n<form action=feedback.php>\n";
		 $max = count($tlist);
		 echo "<tr><td colspan=3>", ww("FeedBackDisclaimer"), "</td>\n";
		 echo "<tr><td colspan=1>", ww("FeedBackChooseYourCategory"), "</td>";
		 echo "\n<td><select name=IdCategory\n>";

		 for ($ii = 0; $ii < $max; $ii++) {
		 	 echo "<option value=" . $tlist[$ii]->id;
			 if ($IdCategory==$tlist[$ii]->id) echo " selected " ;
			 echo  ">";
			 echo ww("FeedBackName_" . $tlist[$ii]->Name);
			 echo "</option>\n";
		 }
		 echo "</select>\n</td>\n";
		 echo "<tr><td>", ww("FeedBackEnterYourQuestion"), "</td>";
		 echo "<td><textarea name=FeedbackQuestion cols=70 rows=9>", "</textarea></td>\n";
		 echo "<tr><td>", ww("FeedBackUrgentQuestion");
		 echo " <input type=checkbox name=urgent></td>";
		 if (!IsLogged()) {
		 	 echo "<td>", ww("FeedBackEmailNeeded");
			 echo " <input type=text name=Email></td>\n";
		 } else {
		   	 echo "<td align=center>", ww("FeedBackIWantAnAnswer");
			 echo " <input type=checkbox name=answerneededt></td>\n";
		 }
		 echo "<tr><td colspan=3 align=center><input type=submit name=submit value=submit></td>\n";
		 echo "<input name=action type=hidden value=ask>\n" ;
		 echo "</form>\n</table>\n";
	}

	include "footer.php";
}
?>
