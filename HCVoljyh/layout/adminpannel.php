<?php
require_once("Menus.php") ;
function DisplayPannel($TData,$lastaction="") {
  global $countmatch ;
  global $title ;
  $title="Admin Pannel" ;
  include "header.php" ;
  mainmenu("") ;
	
	if ($lastaction!="") {
	  echo "$lastaction<br>" ;
	}
	echo "<center>" ;
	

	echo "</center>" ;
	
	
	$max=count($TData) ;
	$count=0 ;
	
	echo "<center>\n" ;
	echo "<table width=100%>\n" ;
	echo "\n<tr><th>Variable</th><th>Value</th><th>Comment</th><th>Action</th>" ;
	
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
	
	echo "<a href=\"".$_SERVER["PHP_SELF"]."?action=phpinfo\">phpinfo</a><br>" ;
	

	echo "</center>" ;
  include "footer.php" ;
} // DisplayPannel


