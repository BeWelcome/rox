<?php
require_once("Menus.php") ;

function DisplayMain($m,$CurrentMessage="") {
  global $title ;
  $title=ww('WelcomePage'." ".$_POST['Username']) ;
  include "header.php" ;
	
	Menu1("",ww('MainPage')) ; // Displays the top menu

	Menu2("main.php",ww('MainPage')) ; // Displays the second menu


	DisplayHeaderWithColumns(ww('MainPage'),"",VolMenu()) ; // Display the header

  if ($CurrentMessage!="") {
	  echo $CurrentMessage ;
		echo "<br>\n" ;
	}


  echo "<center>" ;
  echo "You are logged as ",LinkWithUsername($m->Username)."<br>";
	echo ww("MainPageTextContent") ;
	echo "</center>\n" ;
  

  include "footer.php" ;
}
?>
