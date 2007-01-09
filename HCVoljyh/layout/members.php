<?php
require_once("Menus_micha.php") ;

function DisplayMembers($TData) {
  global $title ;
  $title=ww('MembersPage'." ".$_POST['Username']) ;
  include "header_micha.php" ;
	
	Menu1("",ww('MainPage')) ; // Displays the top menu

	Menu2("members.php",ww('MembersPage')) ; // Displays the second menu


echo "\n<div id=\"maincontent\">\n" ;
echo "  <div id=\"topcontent\">" ;
echo "					<h3> </h3>\n" ;
echo "\n  </div>\n" ;
echo "</div>\n" ;

echo "\n  <div id=\"columns\">\n" ;
echo "		<div id=\"columns-low\">\n" ;

ShowActions() ; // Show the Actions
ShowAds() ; // Show the Ads

echo "		<div id=\"columns-middle\">\n" ;
echo "			<div id=\"content\">\n" ;
echo "				<div class=\"info\">\n" ;


	$iiMax=count($TData) ;
  echo "<table>";
	for ($ii=0;$ii<$iiMax;$ii++) {
	  $m=$TData[$ii] ;
		echo "<tr align=left>" ;
		echo "<td valign=center align=center>" ;
		if (($m->photo!="") and ($m->photo!="NULL")) {
      echo "<div id=\"topcontent-profile-photo\">\n" ;
      echo "<a href=\"",$m->photo,"\" title=\"",str_replace("\r\n"," ",$m->phototext),"\">\n<img src=\"".$m->photo."\" height=\"100px\" ></a>\n<br>" ;
      echo "</div>" ;
		}
		echo "</td>" ;
		echo "<td valign=center>",LinkWithUsername($m->Username),"</td>" ;
		echo " <td valign=center>",$m->countryname,"</td> " ;
		echo "<td valign=center>" ;
    echo $m->ProfileSummary ;
		
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


echo "					<div class=\"user-content\">" ;
  include "footer.php" ;
echo "					</div>" ; // user-content
;
}
?>
