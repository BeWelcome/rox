<?php
require_once("Menus.php") ;
function DisplayGroupList($TGroup) {
  global $title ;
  $title=ww('GroupsList') ;
  include "header.php" ;

  mainmenu("Main.php",ww('Main')) ;
  echo "\n <br><center>\n" ;
  echo "<form method=post><table>\n" ;
	echo "<input type=hidden name=cid value=$IdMember>" ;
	echo "<input type=hidden name=action value=update>" ;

	$iiMax=count($TGroup) ;
	for ($ii=0;$ii<$iiMax;$ii++) {
    echo "<tr><td>" ;
    echo ww("Group_".$TGroup[$ii]->Name) ;
    echo "</td>" ;
    echo "<td>" ;
    echo ww("GroupDesc_".$TGroup[$ii]->Name) ;
    echo "</td>" ;
    echo "<td>" ;
		
  }
	echo "\n<tr><td align=center colspan=3><input type=submit name=submit></td>";
  
  echo "</table>\n" ;
  echo "</form>\n" ;
	
  echo "</center>\n" ;
  include "footer.php" ;
}
?>
