<?php
require_once("Menus_micha.php") ;

function DisplayFaq($TList) {
  global $title ;
  $title=ww('FaqPage') ;
  include "header_micha.php" ;
	
	Menu1("faq.php",ww('FaqPage')) ; // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]) ;

echo "\n<div id=\"maincontent\">\n" ;
echo "  <div id=\"topcontent\">" ;
echo "					<h3>",ww("Faq"),"</h3>\n" ;
echo "\n  </div>\n" ;
echo "</div>\n" ;

echo "\n  <div id=\"columns\">\n" ;
echo "		<div id=\"columns-low\">\n" ;

ShowActions($MenuAction) ; // Show the Actions
ShowAds() ; // Show the Ads

echo "		<div id=\"columns-middle\">\n" ;
echo "			<div id=\"content\">\n" ;
echo "				<div class=\"info\">\n" ;

	$iiMax=count($TList) ;
  echo "<ul>";
	for ($ii=0;$ii<$iiMax;$ii++) {
    $Q=ww("FaqQ_".$TList[$ii]->QandA) ;
		echo "<li><a href=\"".$_SERVER["PHP_SELF"]."#",$ii,"\">",$Q,"</li>" ;
	}
	echo "</ul>";
  echo "					<div class=\"clear\" />\n" ;


  echo "<ul>\n";
	for ($ii=0;$ii<$iiMax;$ii++) {
    echo "					<div class=\"clear\" />\n" ;
    $Q=ww("FaqQ_".$TList[$ii]->QandA) ;
    $A=ww("FaqA_".$TList[$ii]->QandA) ;
		echo "<li><strong><a name=",$ii,"></a> ",$Q,"</strong></li>\n" ;
		echo "<li>",$A,"<hr></li>\n" ;
	}
	echo "</ul>\n";
	
echo "\n         </div>\n"; // Class info 
echo "       </div>\n";  // content
echo "     </div>\n";  // columns-midle
	

echo "   </div>\n";  // columns-low
echo " </div>\n";  // columns

echo "					<div class=\"user-content\">" ;
  include "footer.php" ;
echo "					</div>" ; // user-content
}?>
