<?php
require_once("Menus_micha.php") ;
function DisplayMyVisitors($TData,$Username) {
  global $title,$_SYSHCVOL ;
  $title=ww('MyVisitors') ;
  include "header_micha.php" ;
	
	Menu1() ; // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]) ;
	
echo "\n<div id=\"maincontent\">\n" ;
echo "  <div id=\"topcontent\">" ;
echo "					<h3>",ww("VisitorsFor",$Username)," </h3>\n" ;
echo "\n  </div>\n" ;
echo "</div>\n" ;

echo "\n  <div id=\"columns\">\n" ;
//menumember("member.php?cid=".$m->id,$m->id,$NbComment) ;
echo "		<div id=\"columns-low\">\n" ;


echo "\n    <!-- leftnav -->"; 
echo "     <div id=\"columns-left\">\n"; 
echo "       <div id=\"content\">"; 
echo "         <div class=\"info\">\n";
echo "           <h3>Actions</h3>\n"; 
echo "           <ul>\n"; 

echo "           </ul>\n";
 
echo "         </div>\n"; 
echo "       </div>\n"; 
echo "     </div>\n"; 

echo "\n    <!-- rightnav -->"; 
echo "     <div id=\"columns-right\">\n" ;
echo "       <ul>" ;
echo "         <li class=\"label\">",ww("Ads"),"</li>" ;
echo "         <li></li>" ;
echo "       </ul>\n" ;
echo "     </div>\n" ;

echo "\n    <!-- middlenav -->"; 

echo "     <div id=\"columns-middle\">\n" ;
  echo "					<div id=\"content\">" ;
  echo "						<div class=\"info\">" ;
	$iiMax=count($TData) ;
  echo "<table>";
	if ($iiMax==0) {
	  echo "<tr><td align=center>",ww("NobodyHasYetVisitatedThisProfile"),"</td>" ;
	}
	for ($ii=0;$ii<$iiMax;$ii++) {
	  $rr=$TData[$ii] ;
		echo "<tr align=left>" ;
		echo "<td valign=center align=center>" ;
		if (($rr->photo!="") and ($rr->photo!="NULL")) {
      echo "<div id=\"topcontent-profile-photo\">\n" ;
      echo "<a href=\"",$rr->photo,"\" title=\"",str_replace("\r\n"," ",$rr->phototext),"\">\n<img src=\"".$rr->photo."\" height=\"100px\" ></a>\n<br>" ;
      echo "</div>" ;
		}
		echo "</td>" ;
		echo "<td valign=center>",LinkWithUsername($rr->Username),"</td>" ;
		echo " <td valign=center>",$rr->countryname,"</td> " ;
		echo "<td valign=center>" ;
    if ($rr->ProfileSummary>0) echo FindTrad($rr->ProfileSummary) ;
		
		echo "</td>" ;
		echo "<td>" ;
		echo $rr->datevisite ;
		echo "</td>" ;
		echo "</tr>" ;
	}
	echo "</table>";
  echo "					<div class=\"clear\" />\n" ;

	echo "\n         </div>\n"; // Class info 
  echo "       </div>\n";  // content
  echo "     </div>\n";  // columns-midle
	

  echo "   </div>\n";  // columns-low
  echo " </div>\n";  // columns


  echo "					<div class=\"user-content\">\n" ;
  include "footer.php" ;
  echo "					</div>\n" ;

}
?>
