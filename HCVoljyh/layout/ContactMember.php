<?php
require_once("Menus.php") ;

function DisplayContactMember($m) {
  global $title ;
  $title=ww('ProfilePageFor',$m->Username) ;
  include "header.php" ;

  ProfileMenu("ContactMember.php",ww('MainPage'),$m->id) ;
  echo "<center><H1>Contact ",$m->Username,"</H1></center>\n" ;
  echo "<center><H1> page under construction</H1></center>\n" ;
  echo "</center>\n" ;
  include "footer.php" ;
}
?>
