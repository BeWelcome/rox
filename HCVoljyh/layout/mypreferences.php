<?php
require_once("Menus.php") ;
function DisplayMyPreferences($TPref,$TPublic,$IdMember) {
  global $title ;
  $title=ww('MyPreferences') ;
  include "header.php" ;

  mainmenu("mypreferences.php",ww('MyPreferences')) ;
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
	echo "<tr><td>" ;
	echo ww("PreferencePublicProfile") ;
  echo "</td>" ;
  echo "<td>" ;
	echo ww("PreferencePublicProfileDesc") ;
  echo "</td>" ;
  echo "<td>" ;
	if (isset($TPublic->IdMember)) $Value="Yes" ; // Public profile is not in preference table but in memberspublicprofiles
	else $Value="No" ; 
  echo "\n<select name=PreferencePublicProfile>" ;
  echo "<option value=Yes " ;
  if ($Value=="Yes") echo " selected " ;
  echo ">",ww("Yes"),"</option>\n" ;
  echo "<option value=No" ;
  if ($Value=="No") echo " selected " ;
  echo ">",ww("No"),"</option>\n" ;
  echo "</select>\n" ;  echo "</td>" ;
	
	echo "\n<tr><td align=center colspan=3><input type=submit></td>";
  echo "</table>\n" ;
  echo "</form>\n" ;
	
  echo "</center>\n" ;
  include "footer.php" ;

}
?>
