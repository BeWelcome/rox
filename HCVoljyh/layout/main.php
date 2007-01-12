<?php
require_once("Menus_micha.php") ;

function DisplayMain($m,$CurrentMessage="") {
  global $title ;
  $title=ww('WelcomePage'." ".$_POST['Username']) ;
  include "header_micha.php" ;
	
	Menu1("",ww('MainPage')) ; // Displays the top menu

	Menu2("main.php",ww('MainPage')) ; // Displays the second menu


echo "\n<div id=\"maincontent\">\n" ;
echo "  <div id=\"topcontent\">" ;
  echo "<h3>",ww('MainPage'),"</h3>\n" ;
echo "\n  </div>\n" ;
echo "</div>\n" ;

echo "\n  <div id=\"columns\">\n" ;
echo "		<div id=\"columns-low\">\n" ;

ShowActions("",true) ; // Show the Actions
ShowAds() ; // Show the Ads

echo "		<div id=\"columns-middle\">\n" ;
echo "			<div id=\"content\">\n" ;
echo "				<div class=\"info\">\n" ;

  if ($CurrentMessage!="") {
	  echo $CurrentMessage ;
		echo "<br>\n" ;
	}


  echo "<center>" ;
  echo "You are logged as ",LinkWithUsername($m->Username)."<br>";
	echo ww("MainPageTextContent") ;
	echo "</center>\n" ;
  
	echo "					<div class=\"clear\" />\n" ;
	
echo "\n         </div>\n"; // Class info 
echo "       </div>\n";  // content
echo "     </div>\n";  // columns-midle
	

echo "   </div>\n";  // columns-low
echo " </div>\n";  // columns


  include "footer.php" ;
}
?>
