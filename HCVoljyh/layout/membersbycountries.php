<?php
require_once("Menus.php") ;

function DisplayCountries($TList) {
  global $title ;
  $title=ww('MembersByCountries') ;
  include "header.php" ;

  mainmenu("membersbycountries.php",ww('MembersByCountries')) ;
  echo "\n<br><center>\n" ;
  echo "<table>\n" ;

	$iiMax=count($TList) ;
	for ($ii=0;$ii<$iiMax;$ii++) {
    echo "<tr valign=center>" ;
		echo "<td>" ;
		echo $TList[$ii]->CountryName,"<br>";
		echo $TList[$ii]->RegionName,"<br>";
		echo $TList[$ii]->CityName,"<br>";
		echo "</td>" ;
		echo "<td>",LinkWithUsername($TList[$ii]->Username) ;
    echo "</td>" ;
	}
  echo "</table>\n" ;
	
  echo "</center>\n" ;
  include "footer.php" ;
}
?>
