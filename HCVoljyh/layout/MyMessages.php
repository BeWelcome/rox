<?php
require_once("Menus.php") ;

function DisplayMyMessages($TMess,$Title,$action,$FromTo="") {
  global $title ;
  $title=$Title ;
  include "header.php" ;

  MessagesMenu("MyMessages.php?action=".$action,ww("MyMessage")) ;
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
			
			if ($TMess[$ii]->WhenFirstRead=="0000-00-00 00:00:00") { // if message is not read propose link to read it
			  $text=substr($TMess[$ii]->Message,0,15)." ..." ;
			  echo "<a href=".$_SERVER["PHP_SELF"]."?action=ShowMessage&IdMess=".$TMess[$ii]->IdMess.">",$text,"</a>" ;
			}
			else {
			  if ($TMess[$ii]->SpamInfo!='NotSpam') { // if message is suspected of beeing spam display a flag
				  echo "<font color=red><b>SPAM ?</b></font> " ;
				}
			  echo $TMess[$ii]->Message ;
			}
		  echo "</td>" ;
			echo "<td>" ;
			echo "\n<form method=post>\n" ;
			echo "<input type=hidden name=action value=del>\n" ;
			echo "<input type=hidden name=IdMess value=",$TMess[$ii]->IdMess,">\n" ;
			echo "<input type=submit name=submit value=\"",ww("delmessage"),"\" onclick=\"return confirm('",ww("confirmdeletemessage"),"');\">" ;
			echo "</form>\n" ;
// test if has spam mark and propose to remove it
			if ((($action=="NotRead")and($TMess[$ii]->SpamInfo!='NotSpam'))or($action=="Spam")) {
			  echo "\n<form method=post>\n" ;
			  echo "<input type=hidden name=action value=notspam>\n" ;
			  echo "<input type=hidden name=IdMess value=",$TMess[$ii]->IdMess,">\n" ;
			  echo "<input type=submit name=submit value=\"",ww("marknospam"),"\" onclick=\"return confirm('",ww("confirmmarknospam"),"');\">" ;
			  echo "</form>\n" ;
			}

// propose to mark as spam or to reply if it is a received message
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
