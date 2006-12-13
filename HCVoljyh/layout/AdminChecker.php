<?php
require_once("Menus.php") ;
function DisplayMessages($TMess) {
  global $countmatch ;
  global $title ;
  $title="Admin mail checking" ;
  include "header.php" ;
  mainmenu("AdminChecker.php") ;
	
	if ($lastaction!="") {
	  echo "$lastaction<br>" ;
	}
	echo "<center>" ;

	echo "</center>" ;
	echo "Your Scope is for <b>",$scope,"</b><br>"  ;
	
	
	$max=count($TMess) ;
	$count=0 ;
	
	echo "<center>\n" ;
	echo "<table width=80%>\n" ;
	echo "\n<tr><th>Sender</th><th>Receiver</th><th>Message</th><th>SpamInfo</th><th>Action</th>" ;
	
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
		echo $rr->SpamInfo ;
		echo "</td>" ;
		echo "<td>" ;
		echo "<form method=post>\n" ;
		echo "<input type=hidden name=IdMess_".$ii." value=".$rr->id.">" ;
		echo "<input type=hidden name=action value=approve>\n" ;
		echo "<input type=submit name=submit value=approve>\n" ;
		echo "</form>" ;
		echo "<form method=post>\n" ;
		echo "<input type=hidden name=IdMess_".$ii." value=".$rr->id.">" ;
		echo "<input type=hidden name=action value=markspam>\n" ;
		echo "<input type=submit name=submit value=markspam>\n" ;
		echo "</form>" ;
		echo "</td>" ;
	}
	echo "\n</table><br>\n" ;
	

	echo "</center>" ;
  include "footer.php" ;
} // DisplayAdminRights($username,$rightname,$TRights,$TRightsVol,$rright,$lastaction,$scope) {


