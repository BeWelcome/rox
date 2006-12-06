<?php
require_once("Menus.php") ;

function DisplayMyMessages($TMess,$Title,$action,$FromTo="") {
  global $title ;
  $title=$Title ;
  include "header.php" ;

  MessagesMenu("MyMessage.php?".$action,ww("MyMessage")) ;
	echo "<center>" ;
  echo "<h3>",$Title,"</h3>" ;
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
			echo "<td>" ;
			echo "\n<form method=post>\n" ;
			echo "<input type=hidden name=action value=del>\n" ;
			echo "<input type=hidden name=IdMess value=",$TMess[$ii]->IdMess,">\n" ;
			echo "<input type=submit name=submit value=\"",ww("delmessage"),"\" onclick=\"return confirm('",ww("confirmdeletemessage"),"');\">" ;
			echo "</form>\n" ;
			if (($action=="NotRead")or($action=="Spam")) {
			  echo "\n<form method=post>\n" ;
			  echo "<input type=hidden name=action value=notspam>\n" ;
			  echo "<input type=hidden name=IdMess value=",$TMess[$ii]->IdMess,">\n" ;
			  echo "<input type=submit name=submit value=\"",ww("marknospam"),"\" onclick=\"return confirm('",ww("confirmmarknospam"),"');\">" ;
			  echo "</form>\n" ;
			}
			if (($action=="NotRead")or($action=="Received")) {
			  echo "\n<form method=post>\n" ;
			  echo "<input type=hidden name=action value=markspam>\n" ;
			  echo "<input type=hidden name=IdMess value=",$TMess[$ii]->IdMess,">\n" ;
			  echo "<input type=submit name=submit value=\"",ww("markspam"),"\" onclick=\"return confirm('",ww("confirmmarkspam"),"');\">" ;
			  echo "</form>\n" ;
			  echo "\n<form method=post>\n" ;
			  echo "<input type=hidden name=action value=reply>\n" ;
			  echo "<input type=hidden name=IdMess value=",$TMess[$ii]->IdMess,">\n" ;
			  echo "<input type=submit name=submit value=\"",ww("replymessage"),"\">" ;
			  echo "</form>\n" ;
			}
			echo "</td>" ;
		}
	}
	
  echo "</table>\n" ;
  echo "</center><br>\n" ;
  include "footer.php" ;
}
?>
