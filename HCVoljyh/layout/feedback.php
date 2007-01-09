<?php
require_once("Menus_micha.php") ;

function DisplayFeedback($tlist) {
  global $title ;
  $title=ww('FeedbackPage') ;
  include "header_micha.php" ;
	
	Menu1("feedback.php",ww('MainPage')) ; // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]) ;

echo "\n<div id=\"maincontent\">\n" ;
echo "  <div id=\"topcontent\">" ;
echo "					<h3>",ww("ContactUs"),"</h3>\n" ;
echo "\n  </div>\n" ;
echo "</div>\n" ;

echo "\n  <div id=\"columns\">\n" ;
echo "		<div id=\"columns-low\">\n" ;

ShowActions() ; // Show the actions
ShowAds() ; // Show the Ads

echo "		<div id=\"columns-middle\">\n" ;
echo "			<div id=\"content\">\n" ;
echo "				<div class=\"info\">\n" ;

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

echo "\n         </div>\n"; // Class info 
echo "       </div>\n";  // content
echo "     </div>\n";  // columns-midle
	

echo "   </div>\n";  // columns-low
echo " </div>\n";  // columns


  include "footer.php" ;
}
?>
