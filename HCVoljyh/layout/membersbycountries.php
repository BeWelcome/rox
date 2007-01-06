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

echo "    <!-- leftnav -->\n"; 
echo "     <div id=\"columns-left\">\n"; 
echo "       <div id=\"content\">\n"; 
echo "         <div class=\"info\">\n"; 
echo "           <h3>Action</h3>"; 

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

echo "					<div class=\"user-content\">" ;
  include "footer.php" ;
echo "					</div>" ; // user-content
}
?>
