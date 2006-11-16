<?php
function DisplayMain($m) {
  global $title ;
  $title=ww('WelcomePage'." ".$_POST['Username']) ;
  include "header.php" ;

  echo "<H1>",ww('MainPage'),"</H1>\n" ;
  mainmenu("Main.php",ww('MainPage')) ;
  echo "<center>" ;
  echo "You are logged as <a href=\"Member.php?cid=".$m->id."\">".$m->Username."</a><br>";
  echo "This is the main page for Logged people<br>" ;
  echo "</center>" ;
  include "footer.php" ;
}
?>
