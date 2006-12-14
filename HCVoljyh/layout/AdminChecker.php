<?php
require_once("Menus.php") ;
function DisplayMessages($TMess,$lastaction="") {
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
	echo "<table width=100%>\n" ;
	echo "\n<tr><th>Sender</th><th>Receiver</th><th>Message</th><th>Action</th><th>SpamInfo</th>" ;
	
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
  include "footer.php" ;
} // DisplayAdminRights($username,$rightname,$TRights,$TRightsVol,$rright,$lastaction,$scope) {


