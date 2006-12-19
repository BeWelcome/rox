<?php
require_once("Menus.php") ;

function DisplayFaq($TList) {
  global $title ;
  $title=ww('FaqPage') ;
  include "header.php" ;

  mainmenu("faq.php",ww('FaqPage')) ;
  echo "\n<br>\n" ;
  echo "<table>\n" ;

	$iiMax=count($TList) ;
	for ($ii=0;$ii<$iiMax;$ii++) {
    $Q=ww("FaqQ_".$TList[$ii]->QandA) ;
    echo "<tr>" ;
		echo "<td><a href=\"".$_SERVER["PHP_SELF"]."#",$ii,"\">",$Q,"</a>" ;
		echo "</td>" ;
	}
  echo "</table>\n" ;

  echo "<br><hr>";
	for ($ii=0;$ii<$iiMax;$ii++) {
    $Q=ww("FaqQ_".$TList[$ii]->QandA) ;
    $A=ww("FaqA_".$TList[$ii]->QandA) ;
		echo "<h4><a name=",$ii,"></a> ",$Q,"</h4>" ;
		echo $A,"<hr>" ;
	}
	
  include "footer.php" ;
}?>
