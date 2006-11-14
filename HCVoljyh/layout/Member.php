<?php
function DisplayMember() {
  global $title ;
  $title=ww('ProfilePage'." ".$m->Username) ;
  include "header.php" ;

  echo "<center><H1>",$m->Username,"</H1></center>\n" ;
  mainmenu("Main.php",ww('MainPage')) ;
  echo "\n<center>\n" ;
  echo "<table>\n" ;

  echo "<tr><td>" ;
  echo ww('Username') ;
  echo "</td>" ;
  echo "<td>" ;
  echo $m->Username ;
  echo "</td>" ;

  echo "<tr><td>" ;
  echo ww('ProfileSummary') ;
  echo "</td>" ;
  echo "<td>" ;
  echo FindTrad($m->ProfileSummary) ;
  echo "</td>" ;

  echo "</table>\n" ;
  echo "</center>\n" ;
  include "footer.php" ;
}
?>
