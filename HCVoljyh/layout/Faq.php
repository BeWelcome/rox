<?php
require_once("Menus.php") ;

function DisplayFaq() {
  global $title ;
  $title=ww('FaqPage') ;
  include "header.php" ;

  mainmenu("Faq.php",ww('MainPage')) ;
  echo "<center><H1> page under construction</H1></center>\n" ;
  echo "</center>\n" ;
  include "footer.php" ;
}
?>
