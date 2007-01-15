<?php
require_once("Menus.php") ;

function DisplayCountries($TList) {
  global $title ;
  $title=ww('MembersByCountries') ;
  include "header.php" ;
	
	Menu1("membersbycountries.php",ww('MembersByCountries')) ; // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]) ;

	DisplayHeaderWithColumns(ww('MembersByCountries')) ; // Display the header
	
	
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
	
  include "footer.php" ;
}
?>
