<?php
function DisplayMember($m) {
  global $title ;
  $title=ww('ProfilePageFor',$m->Username) ;
  include "header.php" ;

  echo "<center><H1>",$m->Username,"</H1></center>\n" ;
  ProfileMenu("Member.php",ww('MainPage'),$m->Username) ;
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
