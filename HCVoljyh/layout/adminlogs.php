<?php
require_once("Menus_micha.php") ;

function DisplayAdminLogs($TData) {
  global $title ;
  $title="Admin logs" ;
  include "header_micha.php" ;
	
	Menu1("",ww('MainPage')) ; // Displays the top menu

	Menu2("adminlogs.php",ww('MainPage')) ; // Displays the second menu


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

$max=count($TData) ;
  echo "<table>" ;
  echo "<tr><th>Username</th><th>type</th><th>Str</th><th>created</th><th>ip</th>\n" ;
	for ($ii=0;$ii<$max;$ii++) {
	  $logs=$TData[$ii] ;
    echo "<tr><td>$logs->Username</td><td>$logs->Type</td><td>$logs->Str</td><td>$logs->created</td><td>$logs->IpAddress</td>" ;
	}
	echo "</table>\n" ;
	echo "<hr>" ;
  echo "<table>\n" ;
	echo "<form method=post>" ;
	echo "<tr><td>Username=<input type=text name=Username value=\"",GetParam(Username),"\"></td>" ;
	echo "<td>Type=<input type=text name=type value=\"",GetParam(type),"\"></td>" ;
	echo "<td>IpAddress=<input type=text name=ip value=\"",GetParam(ip),"\"></td>" ;
	echo "<tr><td colspan=3 align=center>" ;
	echo "<input type=submit>" ;
	echo "</td> ";
	echo "</form>" ;
	echo "</table>\n" ;

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
