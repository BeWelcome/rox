<?php
require_once("Menus_micha.php") ;

function DisplayFaq($TList) {
  global $title ;
  $title=ww('FaqPage') ;
  include "header_micha.php" ;
	
	Menu1("faq.php",ww('FaqPage')) ; // Displays the top menu

echo "\n<div id=\"maincontent\">\n" ;
echo "  <div id=\"topcontent\">" ;

echo "\n  </div>\n" ;
echo "</div>\n" ;

echo "\n  <div id=\"columns\">\n" ;
//menumember("member.php?cid=".$m->id,$m->id,$NbComment) ;
echo "		<div id=\"columns-low\">\n" ;

echo "    <!-- leftnav -->\n"; 
echo "     <div id=\"columns-left\">\n"; 
echo "       <div id=\"content\">\n"; 
echo "         <div class=\"info\">"; 
echo "           <h3>Actions</h3>"; 


echo "           <ul>"; 
echo "               <li><a href=\"todo.php\">Add to my list</a></li>"; 
echo "               <li><a href=\"todo.php\">View forum posts</a></li>"; 
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

echo "					<h3>",ww("Faq"),"</h3>\n" ;


	$iiMax=count($TList) ;
  echo "<ul>";
	for ($ii=0;$ii<$iiMax;$ii++) {
    $Q=ww("FaqQ_".$TList[$ii]->QandA) ;
		echo "<li><a href=\"".$_SERVER["PHP_SELF"]."#",$ii,"\">",$Q,"</li>" ;
	}
	echo "</ul>";
  echo "					<div class=\"clear\" />" ;


  echo "<ul>";
	for ($ii=0;$ii<$iiMax;$ii++) {
    echo "					<div class=\"clear\" />" ;
    $Q=ww("FaqQ_".$TList[$ii]->QandA) ;
    $A=ww("FaqA_".$TList[$ii]->QandA) ;
		echo "<li><strong><a name=",$ii,"></a> ",$Q,"</strong></li>\n" ;
		echo "<li>",$A,"<hr></li>" ;
	}
	echo "</ul>";
	
echo "\n         </div>\n"; // Class info 
echo "       </div>\n";  // content
echo "     </div>\n";  // columns-midle
	

echo "   </div>\n";  // columns-low
echo " </div>\n";  // columns

echo "					<div class=\"user-content\">" ;
  include "footer.php" ;
echo "					</div>" ; // user-content
}?>
