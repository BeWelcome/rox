<?php
require_once("Menus_micha.php") ;

function DisplayCountries($TList) {
  global $title ;
  $title=ww('MembersByCountries') ;
  include "header_micha.php" ;
	
	Menu1("membersbycountries.php",ww('MembersByCountries')) ; // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]) ;

echo "\n<div id=\"maincontent\">\n" ;
echo "  <div id=\"topcontent\">" ;
echo "					<h3>",ww('MembersByCountries'),"</h3>\n" ;
echo "\n  </div>\n" ;
echo "</div>\n" ;

echo "\n  <div id=\"columns\">\n" ;
//menumember("member.php?cid=".$m->id,$m->id,$NbComment) ;
echo "		<div id=\"columns-low\">\n" ;

ShowActions() ; // Show the Actions
ShowAds() ; // Show the Ads

echo "		<div id=\"columns-middle\">\n" ;
echo "			<div id=\"content\">\n" ;
echo "				<div class=\"info\">\n" ;

  echo "<ul>\n" ;

	$iiMax=count($TList) ;
	for ($ii=0;$ii<$iiMax;$ii++) {
    echo "<li>" ;
		echo $TList[$ii]->CountryName,">";
		echo $TList[$ii]->RegionName,">";
		echo $TList[$ii]->CityName," ";
		echo LinkWithUsername($TList[$ii]->Username) ;
    echo "</li>\n" ;
	}
  echo "</ul>\n" ;
	
echo "\n         </div>\n"; // Class info 
echo "       </div>\n";  // content
echo "     </div>\n";  // columns-midle
	

echo "   </div>\n";  // columns-low
echo " </div>\n";  // columns

  include "footer.php" ;
}
?>
