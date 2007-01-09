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


ShowActions(VolMenu()) ; // Show the actions
ShowAds() ; // Show the Ads

echo "		<div id=\"columns-middle\">\n" ;
echo "			<div id=\"content\">\n" ;
echo "				<div class=\"info\">\n" ;

$max=count($TData) ;
  echo "<table>" ;
	if ((GetParam(Username)=="")or(GetParam(Username2)!="")) { 
    echo "<tr><th>Username</th><th>type</th><th>Str</th><th>created</th><th>ip</th>\n" ;
	}
	else {
    echo "<tr><th colspan=4 align=center> Logs for ",LinkWithUsername(GetParam(Username)),"</th>\n" ;
	}
	for ($ii=0;$ii<$max;$ii++) {
	  $logs=$TData[$ii] ;
    echo "<tr>" ;
	  if ((GetParam(Username)=="")or(GetParam(Username2)!="")) {
		  echo "<td>" ;
		  echo "<a href=\"".$_SERVER['PHP_SELF']."?Username=".$logs->Username."&type=".$logs->Type."\">".$logs->Username."</a>" ;
			echo "</td>" ;
		}
		echo "<td>" ;
		echo "<a href=\"".$_SERVER['PHP_SELF']."?Username=".GetParam(Username)."&type=".$logs->Type."\">".$logs->Type."</a>" ;
//		echo $logs->Type ;
		echo "</td><td>";
		echo $logs->Str ;
		echo "</td><td>$logs->created</td><td>&nbsp;&nbsp;&nbsp;" ;
		echo "<a href=\"".$_SERVER['PHP_SELF']."?Username=".GetParam(Username)."&ip=".long2ip($logs->IpAddress)."&type=".GetParam(type)."\">".long2ip($logs->IpAddress)."</a>" ;
		echo "</td>\n" ;
	}
	echo "</table>\n" ;
	echo "<hr>\n" ;
  echo "<table>\n" ;
	echo "<form method=post>\n" ;
	if (HasRight("Logs")>1) {
	  echo "<tr><td>Username <input type=text name=Username value=\"",GetParam(Username),"\"></td>" ;
	}
	else {
	  echo "\n<tr><td>Username <input type=text readonly name=Username value=\"",GetParam(Username),"\"></td>" ;
	}
	echo "<td>Type <input type=text name=type value=\"",GetParam(type),"\"></td>" ;
	echo "<td>Ip <input type=text name=ip value=\"",GetParam(ip),"\"></td>" ;
	echo "\n<tr><td colspan=3 align=center>" ;
	echo "<input type=submit>" ;
	echo "</td> ";
	echo "</form>\n" ;
	echo "</table>\n" ;

echo "\n         </div>\n"; // Class info 
echo "       </div>\n";  // content
echo "     </div>\n";  // columns-midle
	

echo "   </div>\n";  // columns-low
echo " </div>\n";  // columns


  include "footer.php" ;

}
?>
