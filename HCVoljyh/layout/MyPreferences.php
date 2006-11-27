<?php
require_once("Menus.php") ;
function DisplayMyPreferences($TPref,$TPrefMember,$IdMember) {
  global $title ;
  $title=ww('MyPreferences') ;
  include "header.php" ;

  mainmenu("MyPreferences.php",ww('MyPreferences')) ;
  echo "\n<center>\n" ;
  echo "<br><form method=post><table>\n" ;
	echo "<input type=hidden name=cid value=$IdMember>" ;
	echo "<input type=hidden name=action value=update>" ;

	$iiMax=count($TPref) ;
	for ($ii=0;$ii<$iiMax;$ii++) {
    echo "<tr><td>" ;
    echo ww($TPref[$ii]->codeName) ;
    echo "</td>" ;
    echo "<td>" ;
    echo ww($TPref[$ii]->codeDescription) ;
    echo "</td>" ;
    echo "<td>" ;
		if (isset($TPrefMember[$TPref[$ii]->codeName])) {
		  $Value=$TPrefMember[$TPref[$ii]->codeName]->Value ;
		}
		else {
		  $Value=$TPref[$ii]->DefaultValue ;
		}
    echo "<input type=hidden name=codename Value='".$TPref[$ii]->codeName."'>" ; ;
    echo "<input type=text name=Value Value='".$Value."'>" ; ;
    echo "</td>" ;
		
  }
	echo "\n<tr><td align=center colspan=3><input type=submit name=submit></td>";
  
  echo "</table>\n" ;
  echo "</form>\n" ;
	
  echo "</center>\n" ;
  include "footer.php" ;
}
?>
