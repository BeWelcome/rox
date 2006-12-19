<?php
require_once("Menus.php") ;
function DisplayError($ErrorMessage="No Error Message") {
  global $title,$errcode ;
  $title=ww('ErrorPage') ;
  include "header.php" ;

  echo "<H1>",ww('ErrorPage'),"</H1>\n" ;
  mainmenu("main.php",ww('MainPage')) ;
  echo "<center><table bgcolor=#ffffcc >" ;
  echo "<TR><td>Error<br>".$errcode."</TD><td>",$ErrorMessage,"</TD><br>" ;
  echo "</table></center>" ;
  include "footer.php" ;
}
?>
