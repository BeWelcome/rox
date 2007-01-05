<?php
require_once("Menus_micha.php") ;

function DisplayMain($m) {
  global $title ;
  $title=ww('WelcomePage'." ".$_POST['Username']) ;
  include "header_micha.php" ;
	
	Menu1("",ww('MainPage')) ; // Displays the top menu

	Menu2("main.php",ww('MainPage')) ; // Displays the second menu


echo "\n<div id=\"maincontent\">\n" ;
echo "  <div id=\"topcontent\">" ;
echo "					<h3> </h3>\n" ;
echo "\n  </div>\n" ;
echo "</div>\n" ;

echo "\n  <div id=\"columns\">\n" ;
//menumember("member.php?cid=".$m->id,$m->id,$NbComment) ;
echo "		<div id=\"columns-low\">\n" ;

echo "    <!-- leftnav -->\n"; 
echo "     <div id=\"columns-left\">\n"; 
echo "       <div id=\"content\">\n"; 
echo "         <div class=\"info\">\n"; 
echo "           <h3>Action</h3>"; 

echo "           <ul>"; 
VolMenu() ; // Add volonteers menu according to rights
echo "           </ul>"; 
echo "         </div>"; // Class info 
echo "       </div>\n";  // content
echo "     </div>\n";  // columns-left

echo "     <div id=\"columns-right\">\n" ;
echo "       <ul>" ;
echo "         <li class=\"label\">",ww("Ads"),"</li>" ;
echo "         <li></li>" ;
echo "       </ul>\n" ;
echo "     </div>\n" ; // columns rights

echo "		<div id=\"columns-middle\">\n" ;
echo "			<div id=\"content\">\n" ;
echo "				<div class=\"info\">\n" ;

  echo "<H1>",ww('MainPage'),"</H1>\n" ;
  echo "<center>" ;
	echo "<br>This is a draft to make some test<br><br>there is a proble with utf /unicode which is still to be solve<br>" ;  
  echo "You are logged as ",LinkWithUsername($m->Username)."<br>";
  echo "This is the main page for Logged people<br>" ;

echo "\n         </div>\n"; // Class info 
echo "       </div>\n";  // content
echo "     </div>\n";  // columns-midle
	

echo "   </div>\n";  // columns-low
echo " </div>\n";  // columns


echo "					<div class=\"user-content\">" ;
  include "footer.php" ;
echo "					</div>" ; // user-content
;
}
?>
