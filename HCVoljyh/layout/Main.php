<?php
require_once("Menus.php") ;
function DisplayMain($m) {
  global $title ;
  $title=ww('WelcomePage'." ".$_POST['Username']) ;
  include "header.php" ;

  mainmenu("Main.php",ww('MainPage')) ;
  echo "<H1>",ww('MainPage'),"</H1>\n" ;
  echo "<center>" ;
	echo "<br>This is a draft to make some test<br><br>" ;  
  echo "You are logged as <a href=\"Member.php?cid=".$m->id."\">".$m->Username."</a><br>";
  echo "This is the main page for Logged people<br>" ;
  echo "</center>" ;
  include "footer.php" ;
}
?>
