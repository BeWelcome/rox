<?php
require_once("Menus_micha.php") ;

function DisplayFeedback($tlist) {
  global $title ;
  $title=ww('FeedbackPage') ;
  include "header_micha.php" ;
	
	Menu1("feedback.php",ww('MainPage')) ; // Displays the top menu

echo "\n<div id=\"maincontent\">\n" ;
echo "  <div id=\"topcontent\">" ;
echo "					<h3> </h3>\n" ;
echo "\n  </div>\n" ;
echo "</div>\n" ;

echo "\n  <div id=\"columns\">\n" ;
//menumember("member.php?cid=".$m->id,$m->id,$NbComment) ;
echo "		<div id=\"columns-low\">\n" ;

echo "    <!-- leftnav -->\n"; 
echo "     <div id=\"columns-left\">\n"; 
echo "       <div id=\"content\">\n"; 
echo "         <div class=\"info\">\n"; 
echo "           <h3> </h3>"; 

echo "           <ul>"; 
echo "           </ul>"; 
echo "         </div>"; // Class info 
echo "       </div>\n";  // content
echo "     </div>\n";  // columns-left

echo "     <div id=\"columns-right\">\n" ;
echo "       <ul>" ;
echo "         <li class=\"label\">",ww("Ads"),"</li>" ;
echo "         <li></li>" ;
echo "       </ul>\n" ;
echo "     </div>\n" ; // columns rights

echo "		<div id=\"columns-middle\">\n" ;
echo "			<div id=\"content\">\n" ;
echo "				<div class=\"info\">\n" ;

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

echo "\n         </div>\n"; // Class info 
echo "       </div>\n";  // content
echo "     </div>\n";  // columns-midle
	

echo "   </div>\n";  // columns-low
echo " </div>\n";  // columns


echo "					<div class=\"user-content\">\n" ;
  include "footer.php" ;
echo "					\n</div>\n" ;
}
?>
