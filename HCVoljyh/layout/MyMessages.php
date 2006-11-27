<?php
require_once("Menus.php") ;

function DisplayMyMessages($TMess,$Title,$option,$FromTo="") {
  global $title ;
  $title=$Title ;
  include "header.php" ;

  MessagesMenu("MyMessage.php?".$option,ww("MyMessage")) ;
	echo "<center>" ;
  echo "<h1>",$Title,"</h1>" ;
  echo "<table width=70%>\n" ;


	$max=count($TMess) ;
	if ($max>0) {
    echo "<tr><td colspan=3></td>" ;
	  for ($ii=0;$ii<$max;$ii++) {
		  echo "<tr>" ;
			echo "<td>" ;
			echo $TMess[$ii]->created ;
		  echo "</td>" ;
			echo "<td>" ;
			echo ww($FromTo,LinkWithUsername($TMess[$ii]->Username)) ;
		  echo "</td>" ;
			echo "<td>" ;
			echo $TMess[$ii]->Message ;
		  echo "</td>" ;
		}
	}
	
  echo "</table>\n" ;
  echo "</center>\n" ;
  include "footer.php" ;
}
?>
