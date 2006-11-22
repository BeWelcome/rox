<?php
require_once("Menus.php") ;
function DisplayAdminAccepter($Taccepted,$Ttoaccept,$Tmailchecking,$Tpending,$TtoComplete,$lastaction="") {
  global $countmatch ;
  global $title ;
  $title="Accept members" ;
  include "header.php" ;
  mainmenu("AdminAccepter.php") ;
	
	echo "<center>" ;

	if ($lastaction!="") {
	  echo "<h2>$lastaction</h2>" ;
	}
	
	
	$max=count($Tpending) ;
	$count=0 ;
	
	echo "<h3> Members to accept</h3>" ;
	echo "\n<table width=40%>\n" ;
	for ($ii=0;$ii<$max;$ii++) {
	  $m=$Tpending[$ii] ;
		$count++ ;
	  echo "<tr><td><a href=Member.php?cid=",$m->Username,"><b>",$m->Username,"</b></a></td><td>" ;
		if ($m->ProfileSummary>0) echo FindTrad($m->ProfileSummary);
		echo "</td>\n" ;
		echo "<td>" ;
		echo "<form method=post>" ;
		echo "<input type=hidden name=action value=accept>" ;
		echo "<input type=hidden name=cid value=",$m->id,">" ;
		echo "<input type=submit name=submit value=accept>" ;
		echo "</form>" ;
		echo "<form method=post>" ;
		echo "<input type=hidden name=action value=tocomplete>" ;
		echo "<input type=hidden name=cid value=",$m->id,">" ;
		echo "<input type=submit name=submit value=\"need more\">" ;
		echo "</form>" ;
		echo "</td>" ;
	}
	echo "<tr><td align=right>Total</td><td align=left>$count</td>" ;
	echo "\n</table><br>\n" ;
	
	$max=count($Taccepted) ;
	$count=0 ;
	echo "<h3> Allready accepted</h3>" ;
	echo "\n<table width=40%>\n" ;
	for ($ii=0;$ii<$max;$ii++) {
	  $m=$Taccepted[$ii] ;
		$count++ ;
	  echo "<tr><td><a href=Member.php?cid=",$m->Username,"><b>",$m->Username,"</b></a></td><td>" ;
		if ($m->ProfileSummary>0) echo FindTrad($m->ProfileSummary);
		echo "</td>\n" ;
	}
	echo "<tr><td align=right>Total</td><td align=left>$count</td>" ;
	echo "\n</table><br>\n" ;
	
	$max=count($Tmailchecking) ;
	$count=0 ;
	echo "<h3> Members who have not yet confirmed their email</h3>" ;
	echo "\n<table width=40%>\n" ;
	for ($ii=0;$ii<$max;$ii++) {
	  $m=$Tmailchecking[$ii] ;
		$count++ ;
	  echo "<tr><td><a href=Member.php?cid=",$m->Username,"><b>",$m->Username,"</b></a></td><td>" ;
		if ($m->ProfileSummary>0) echo FindTrad($m->ProfileSummary);
		echo "</td>\n" ;
	}
	echo "<tr><td align=right>Total</td><td align=left>$count</td>" ;
	echo "\n</table>\n" ;

	$max=count($TtoComplete) ;
	$count=0 ;
	echo "<h3> Members who have to complete their profile</h3>" ;
	echo "\n<table width=40%>\n" ;
	for ($ii=0;$ii<$max;$ii++) {
	  $m=$TtoComplete[$ii] ;
		$count++ ;
	  echo "<tr><td><a href=Member.php?cid=",$m->Username,"><b>",$m->Username,"</b></a></td><td>" ;
		if ($m->ProfileSummary>0) echo FindTrad($m->ProfileSummary);
		echo "</td>\n" ;
		echo "<td>" ;
		echo "<form method=post>" ;
		echo "<input type=hidden name=action value=accept>" ;
		echo "<input type=hidden name=cid value=",$m->id,">" ;
		echo "<input type=submit name=submit value=accept>" ;
		echo "</form>" ;
		echo "</td>" ;
	}
	echo "<tr><td align=right>Total</td><td align=left>$count</td>" ;
	echo "\n</table><br>\n" ;
	
	

	echo "</center>" ;
  include "footer.php" ;
} // end of DisplayAdminAccepter($Taccepted,$Ttoaccept,$Tmailchecking,$Tpending)


