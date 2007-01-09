<?php
require_once("Menus_micha.php") ;
function DisplayMessages($TMess,$lastaction="") {
  global $countmatch ;
  global $title ;
  $title="Admin mail checking" ;
  include "header_micha.php" ;
	
	Menu1() ; // Displays the top menu

	Menu2($_SERVER["PHP_SELF"]) ;
	
echo "\n<div id=\"maincontent\">\n" ;
echo "  <div id=\"topcontent\">" ;
echo "					<h3>","Admin checker"," </h3>\n" ;
echo "\n  </div>\n" ;
echo "</div>\n" ;

echo "\n  <div id=\"columns\">\n" ;
echo "		<div id=\"columns-low\">\n" ;


ShowActions() ; // Show the actions
ShowAds() ; // Show the Ads

echo "\n    <!-- middlenav -->"; 

echo "     <div id=\"columns-middle\">\n" ;
  echo "					<div id=\"content\">" ;
  echo "						<div class=\"info\">" ;
	
	if ($lastaction!="") {
	  echo "$lastaction<br>" ;
	}
	
	$max=count($TMess) ;
	$count=0 ;
	
	echo "<center>\n" ;
	echo "<table width=100%>\n" ;
	if ($max==0) {
	  echo "<tr><td align=center>",ww("NobodyHasYetVisitatedThisProfile"),"</td>" ;
	}
	else {
	  echo "\n<tr><th>Sender</th><th>Receiver</th><th>Message</th><th>Action</th><th>SpamInfo</th>" ;
	}
	
	echo "<form method=post>\n" ;
  echo "<input type=hidden name=action value=check>" ;
	for ($ii=0;$ii<$max;$ii++) {
	  $rr=$TMess[$ii] ;
		$count++ ;
		echo "<tr>" ;
		echo "<td>" ;
		echo LinkWithUsername($rr->Username_sender) ;
		echo "</td>" ;
		echo "<td>" ;
		echo LinkWithUsername($rr->Username_receiver) ;
		echo "</td>" ;
		echo "<td>" ;
		echo "<textarea cols=80 rows=5 readonly>" ;
		echo $rr->Message ;
		echo "</textarea>" ;
		echo "</td>" ;
		echo "<td>" ;
		echo "Approve <input type=hidden name=IdMess_".$ii." value=".$rr->id.">" ;
		echo "Approve <input type=checkbox name=Approve_".$ii." >&nbsp;&nbsp;&nbsp;" ;
		$checked="" ;
		$SpamInfo="" ;
		if ($rr->SpamInfo!="NotSpam") {
		  $checked="checked" ;
		} 
		echo "Mark Spam <input type=checkbox name=Mark_Spam_".$ii." $checked>" ;
		echo "</td>" ;
		echo "<td>" ;
		echo $rr->SpamInfo ;
		echo "</td>" ;
	}
  echo "<tr><td colspan=3 align=center></td><td align=center><input type=submit name=submit value=submit></td>" ;
	echo "</form>" ;
	echo "\n</table><br>\n" ;
	

	echo "</center>" ;
  echo "					<div class=\"clear\" />\n" ;

	echo "\n         </div>\n"; // Class info 
  echo "       </div>\n";  // content
  echo "     </div>\n";  // columns-midle
	

  echo "   </div>\n";  // columns-low
  echo " </div>\n";  // columns


  include "footer.php" ;

} // DisplayAdminRights($username,$rightname,$TRights,$TRightsVol,$rright,$lastaction,$scope) {


