<?php
require_once("Menus.php") ;

function DisplayFeedback($tlist) {
  global $title ;
  $title=ww('FeedbackPage') ;
  include "header.php" ;
	
	Menu1("feedback.php",ww('MainPage')) ; // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]) ;

	DisplayHeaderWithColumns(ww("ContactUs")) ; // Display the header
	
	echo "<table>\n<form>\n" ;
	$max=count($tlist) ;
  echo "<tr><td colspan=3>",ww("FeedBackDisclaimer"),"</td>\n" ;
  echo "<tr><td colspan=1>",ww("FeedBackChooseYourCategory"),"</td>" ;
	echo "\n<td><select name=IdCategory\n>" ;

	for ($ii=0;$ii<$max;$ii++) {
	  echo "<option name=".$tlist[$ii]->id,">" ;
		echo ww("FeedBackName_".$tlist[$ii]->Name) ;
		echo "</option>\n" ;
	}
	echo "</select>\n</td>\n" ;
	echo "<tr><td>",ww("FeedBackEnterYourQuestion"),"</td>" ;
	echo "<td><textarea name=FeedbackQuestion cols=70 roms=6>","</textarea></td>\n" ;
	echo "<tr><td>",ww("FeedBackUrgentQuestion"),"</td>" ;
	echo "<td><input type=checkbox name=urgent></td>" ;
	if (!IsLogged()) {
	  echo "<tr><td>",ww("FeedBackEmailNeeded"),"</td>" ;
	  echo "<td><input type=text name=Email></td>\n" ;
	}
	else {
	  echo "<tr><td>",ww("FeedBackIWantAnAnswer"),"</td>" ;
	  echo "<td><input type=checkbox name=answerneededt></td>\n" ;
	}
	echo "<tr><td colspan=3 align=center><input type=submit name=submit value=submit></td>\n" ;
	echo "</form>\n</table>\n" ;

  include "footer.php" ;
}
?>
