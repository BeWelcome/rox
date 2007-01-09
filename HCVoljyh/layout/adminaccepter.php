<?php
require_once("Menus_micha.php") ;

function ShowList($TData) {
  $max=count($TData) ;
	$count=0 ;
	echo "\n<table width=40%>\n" ;
	for ($ii=0;$ii<$max;$ii++) {
	  $m=$TData[$ii] ;
		$count++ ;
	  echo "<tr><td colspan=2>",LinkWithUsername($m->Username),"</td><td colspan=2>",$m->ProfileSummary,"</td>\n" ;
	  echo "<tr><td>",$m->HouseNumber,"</td><td colspan=2>",$m->StreetName,"</td><td>",$m->Zip,"</td>\n" ;
	  echo "<tr><td colspan=3>",$m->countryname,">",$m->regionname,">",$m->cityname,"</td><td></td>\n" ;
	}
	echo "<tr><td align=right colspan=2>Total</td><td align=left colspan=2>$count</td>" ;
	echo "\n</table><br>\n" ;
} // end of ShowList


function DisplayAdminAccepter($Taccepted$Tmailchecking,$Tpending,$TtoComplete,$lastaction="") {
  global $countmatch ;
  global $title ;
  $title="Accept members" ;

  include "header_micha.php" ;
	
	Menu1("",ww('MainPage')) ; // Displays the top menu

	Menu2("adminaccepter.php",ww('MainPage')) ; // Displays the second menu


echo "\n<div id=\"maincontent\">\n" ;
echo "  <div id=\"topcontent\">" ;
echo "					<h3> Admin Accepter </h3>\n" ;
echo "\n  </div>\n" ;
echo "</div>\n" ;
	
echo "					<div class=\"user-content\">" ;
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
	  echo "<tr><td>",LinkWithUsername($m->Username),"</td><td>",$m->ProfileSummary,"</td>\n" ;
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
	
	echo "<h3> Allready accepted</h3>" ;
	ShowList($Taccepted) ;
	
	echo "<h3> Members who have not yet confirmed their email</h3>" ;
	ShowList($Tmailchecking) ;

	$max=count($TtoComplete) ;
	$count=0 ;
	echo "<h3> Members who have to complete their profile</h3>" ;
	ShowList($TtoComplete) ;
	echo "\n<table width=40%>\n" ;
	for ($ii=0;$ii<$max;$ii++) {
	  $m=$TtoComplete[$ii] ;
		$count++ ;
	  echo "<tr><td>",LinkWithUsername($m->Username),"</td><td>",$m->ProfileSummary,"</td>\n" ;
		echo "<td>" ;
		echo "<form method=post>" ;
		echo "<input type=hidden name=action value=accept>" ;
		echo "<input type=hidden name=cid value=",$m->id,">" ;
		echo "<input type=submit name=submit value=accept>" ;
		echo "</form>" ;
		echo "</td>" ;
	}
	echo "<tr><td align=right>Total</td><td align=left>$count</td>" ;
	echo "\n</table>\n" ;
echo "					</div>" ; // user-content
	
	

	echo "</center>" ;
  include "footer.php" ;
} // end of DisplayAdminAccepter($Taccepted,$Tmailchecking,$Tpending)


