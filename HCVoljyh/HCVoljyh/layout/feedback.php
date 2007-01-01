<?php
require_once("Menus.php") ;

function DisplayFeedback($tlist) {
  global $title ;
  $title=ww('FeedbackPage') ;
  include "header.php" ;

  mainmenu("feedback.php",ww('MainPage')) ;
  echo "<center><H1> page under construction</H1>\n" ;
	echo "will be soon available" ;
	echo "<table><form>" ;
	$max=count($tlist) ;
  echo "<tr><td colspan=3>",ww("FeedBackDisclaimer"),"</td>" ;
  echo "<tr><td colspan=1>",ww("FeedBackChooseYourCategory"),"</td>" ;
	echo "\n<td><select name=IdCategory\n>" ;

	for ($ii=0;$ii<$max;$ii++) {
	  echo "<option name=".$tlist[$ii]->id,">" ;
		echo ww("FeedBackName_".$tlist[$ii]->Name) ;
		echo "</option>\n" ;
	}
	echo "</select>\n</td>" ;
	echo "<tr><td>",ww("FeedBackEnterYourQuestion"),"</td>" ;
	echo "<td><textarea name=FeedbackQuestion cols=70 roms=6>","</textarea></td>" ;
	echo "<tr><td>",ww("FeedBackUrgentQuestion"),"</td>" ;
	echo "<td><input type=checkbox name=urgent></td>" ;
	if (!IsLogged()) {
	  echo "<tr><td>",ww("FeedBackEmailNeeded"),"</td>" ;
	  echo "<td><input type=text name=Email></td>" ;
	}
	else {
	  echo "<tr><td>",ww("FeedBackIWantAnAnswer"),"</td>" ;
	  echo "<td><input type=checkbox name=answerneededt></td>" ;
	}
	echo "<tr><td colspan=3 align=center><input type=submit name=submit value=submit></td>" ;
	echo "</form></table>" ;
  echo "</center>\n" ;
  include "footer.php" ;
}
?>
