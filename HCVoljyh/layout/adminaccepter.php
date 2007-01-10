<?php
require_once("Menus_micha.php") ;

function ShowList($TData) {
  $max=count($TData) ;
	$count=0 ;
	echo "\n<table width=\"60%\">\n" ;
	for ($ii=0;$ii<$max;$ii++) {
	  $m=$TData[$ii] ;
		$count++ ;
	  echo "<tr><td colspan=2>",LinkWithUsername($m->Username),"</td><td colspan=2>",$m->ProfileSummary,"</td>\n" ;
		echo "<td rowspan=3>" ;
    if ($m->Status!="Active") echo "<a href=\"adminaccepter.php?cid=",$m->id,"&action=accept\">accept</a><br>" ;
    if ($m->Status!="needmore") echo "<a href=\"adminaccepter.php?cid=",$m->id,"&action=needmore\">need more</a><br>" ;
		echo "<a href=\"contactmember.php?cid=",$m->id,"\">contact</a><br>" ;
		echo "<a href=\"updatemandatory.php?cid=",$m->id,"\">update mandatory</a>" ;
		echo "</td>" ;
	  echo "<tr><td>",$m->HouseNumber,"</td><td colspan=2>",$m->StreetName,"</td><td>",$m->Zip,"</td>\n" ;
	  echo "<tr><td colspan=4><font color=gray><b>",$m->countryname," > ",$m->regionname," > ",$m->cityname,"</b></font></td>\n" ;
	  echo "<tr><td colspan=4><font color=green><b><i>",$m->FeedBack,"</i></b></font></td><td></td>\n" ;
	  echo "<tr><td colspan=5><hr></td>\n" ;
	}
	echo "<tr><td align=left colspan=2>Total</td><td align=left colspan=2>$count</td>" ;
	echo "\n</table><br>\n" ;
} // end of ShowList


function DisplayAdminAccepter($Taccepted,$Tmailchecking,$Tpending,$TtoComplete,$lastaction="") {
  global $countmatch ;
  global $title ;
  $title="Accept members" ;
  global $AccepterScope ;

  include "header_micha.php" ;
	
	Menu1("",ww('MainPage')) ; // Displays the top menu

	Menu2("adminaccepter.php",ww('MainPage')) ; // Displays the second menu


echo "\n<div id=\"maincontent\">\n" ;
echo "  <div id=\"topcontent\">" ;
echo "					<h3> ",$title ;
	if ($lastaction!="") {
	  echo ": $lastaction" ;
    echo "</h3>\n" ;
  }
	else {
	  echo " your Scope :", $AccepterScope ;
	}
echo "\n  </div>\n" ;
echo "</div>\n" ;
	
echo "					<div class=\"user-content\">" ;

  echo "<center>" ;

	echo "<h3> Members to accept</h3>" ;
	ShowList($Tpending) ;

	echo "<h3> Members who have to complete their profile</h3>" ;
	ShowList($TtoComplete) ;

	echo "<h3> Members who have not yet confirmed their email</h3>" ;
	ShowList($Tmailchecking) ;

	echo "<h3> Allready accepted</h3>" ;
	ShowList($Taccepted) ;
  echo "</center>" ;
	

echo "					</div>" ; // user-content
	

  include "footer.php" ;
} // end of DisplayAdminAccepter($Taccepted,$Tmailchecking,$Tpending)
