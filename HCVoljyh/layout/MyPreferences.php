<?php
require_once("Menus.php") ;
function DisplayMyPreferences($TPref,$IdMember) {
  global $title ;
  $title=ww('MyPreferences') ;
  include "header.php" ;

  mainmenu("MyPreferences.php",ww('MyPreferences')) ;
  echo "\n<center>\n" ;
  echo "<br><form method=post><table cellpadding=10 cellspacing=10>" ;
	echo "<input type=hidden name=cid value=$IdMember>" ;
	echo "<input type=hidden name=action value=Update>" ;

	$iiMax=count($TPref) ;
	for ($ii=0;$ii<$iiMax;$ii++) {
	  $rr=$TPref[$ii] ;
    echo "<tr><td>" ;
    echo ww($rr->codeName) ;
    echo "</td>" ;
    echo "<td>" ;
    echo ww($rr->codeDescription) ;
    echo "</td>" ;
    echo "<td>" ;
		
		if ($rr->Value!="") {
		  $Value=$rr->Value ; 
		}
		else {
		  $Value=$rr->DefaultValue ;
		}
		echo eval($rr->EvalString) ;
    echo "</td>" ;
  } // end of for ii
	
	echo "\n<tr><td align=center colspan=3><input type=submit></td>";
  echo "</table>\n" ;
  echo "</form>\n" ;
	
  echo "</center>\n" ;
  include "footer.php" ;

}
?>
