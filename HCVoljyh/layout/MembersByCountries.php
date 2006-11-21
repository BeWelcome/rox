<?php
require_once("Menus.php") ;
function DisplayCountries($Tcountry) {
  global $title ;
  $title=ww('MembersByCountries') ;
  include "header.php" ;

  mainmenu("MembersByCountries.php",ww('MembersByCountries')) ;
  echo "\n<center>\n" ;
  echo "<table>\n" ;
	echo "<tr><th colspan=3>",ww('MembersByCountries'),"</th>" ;


	$iiMax=count($Tcountry) ;
	for ($ii=0;$ii<$iiMax;$ii++) {
    echo "<tr><td>" ;
    echo $Tcountry[$ii]->Name ;
    echo "</td>" ;
    echo "<td>" ;
    echo $Tcountry[$ii]->Count ;
    echo "</td>" ;
    echo "<td valign=center>" ;
		echo "<form method=post style=\"display:inline\">" ;
	  echo "<input type=hidden name=action value=SelectCountry> " ;
    echo "<input type=hidden name=IdCountry value=\"",$Tcountry[$ii]->id,"\"> " ;
    echo " <input type=submit name=submit value=\"submit\">" ;
    echo "</form>\n" ;
    echo "</td>" ;
	}
  
  echo "</table>\n" ;
	
  echo "</center>\n" ;
  include "footer.php" ;
}

function DisplayCountry($TitleTable,$TList) {
  global $title ;
  $title=ww('MembersByCountries') ;
  include "header.php" ;

  mainmenu("MembersByCountries.php",ww('MembersByCountries')) ;
  echo "\n<center>\n" ;
  echo "<table>\n" ;
	echo "<tr><th align=center>",$TitleTable,"</th>" ;

	$iiMax=count($TList) ;
	for ($ii=0;$ii<$iiMax;$ii++) {
    echo "<tr><td>" ;
    echo "<a href=Member.php?cid=".$TList[$ii]->Username.">",$TList[$ii]->Username,"</a>" ;
    echo "</td>" ;
	}
  echo "</table>\n" ;
	
  echo "</center>\n" ;
  include "footer.php" ;
}
?>
