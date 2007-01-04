<?php
require_once("Menus.php") ;
function DisplayMain($m) {
  global $title ;
  $title=ww('WelcomePage'." ".$_POST['Username']) ;
  include "header.php" ;

  mainmenu("main.php",ww('MainPage')) ;
  echo "<H1>",ww('MainPage'),"</H1>\n" ;
  echo "<center>" ;
	echo "<br>This is a draft to make some test<br><br>there is a proble with utf /unicode which is still to be solve<br>" ;  
  echo "You are logged as ",LinkWithUsername($m->Username)."<br>";
  echo "This is the main page for Logged people<br>" ;
  echo "</center>" ;
  include "footer.php" ;
}
?>
