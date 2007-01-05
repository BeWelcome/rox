<?php
require_once("Menus_micha.php") ;
function DisplayMyPreferences($TPref,$m) {
  global $title ;
  $title=ww('MyPreferences') ;
  include "header_micha.php" ;
	
	Menu1() ; // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]) ;
	
// Header of the profile page
  require_once("profilepage_header.php") ;

echo "	<div id=\"columns\">" ;
menumember("member.php?cid=".$m->id,$m->id,$m->NbComment) ;
echo "		<div id=\"columns-low\">" ;
/*
echo "\n    <!-- leftnav -->"; 
echo "     <div id=\"columns-left\">\n"; 
echo "       <div id=\"content\">"; 
echo "         <div class=\"info\">"; 


echo "           <ul>"; 
echo "           </ul>"; 
echo "         </div>"; 
echo "       </div>\n"; 
echo "     </div>\n"; 
*/

echo "\n    <!-- rightnav -->"; 
echo "     <div id=\"columns-right\">\n" ;
echo "       <ul>" ;
echo "         <li class=\"label\">",ww("Ads"),"</li>" ;
echo "         <li></li>" ;
echo "       </ul>\n" ;
echo "     </div>\n" ;

echo "\n    <!-- middlenav -->"; 

  echo "					<div id=\"content\">" ;
  echo "						<div class=\"info\">" ;
	echo "						<form method=\"post\" action=\"\" id=\"preferences\">" ;


  echo "<table id=\"preferencesTable\">" ;
	echo "<input type=hidden name=cid value=$m->id>" ;
	echo "<input type=hidden name=action value=Update>" ;

	$iiMax=count($TPref) ;
	for ($ii=0;$ii<$iiMax;$ii++) {
	  $rr=$TPref[$ii] ;
    echo "<tr><td>" ;
    echo "<p class=\"preflabel\">",ww($rr->codeName),"</p>" ;
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
	echo "<p class=\"preflabel\">",ww("PreferencePublicProfile"),"</p>" ;
  echo "</td>" ;
  echo "<td>" ;
	echo ww("PreferencePublicProfileDesc") ;
  echo "</td>" ;
  echo "<td>" ;
	if (isset($m->TPublic->IdMember)) $Value="Yes" ; // Public profile is not in preference table but in memberspublicprofiles
	else $Value="No" ; 
  echo "\n<select name=PreferencePublicProfile  class=\"prefsel\">" ;
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
	
echo "					</div>" ;
echo "				</div>" ;
echo "			</div>" ;
echo "		</div>" ;

echo "					<div class=\"user-content\">" ;
  include "footer.php" ;
echo "					</div>" ;

}
?>
