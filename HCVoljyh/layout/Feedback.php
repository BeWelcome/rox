<?php
require_once("Menus.php") ;

function DisplayFeedback() {
  global $title ;
  $title=ww('FeedbackPage') ;
  include "header.php" ;

  mainmenu("Faq.php",ww('MainPage')) ;
  echo "<center><H1> page under construction</H1>\n" ;
	echo "will be soon available" ;
  echo "</center>\n" ;
  include "footer.php" ;
}
?>
