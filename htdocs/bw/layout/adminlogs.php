<?php
require_once ("menus.php");


// This function returns the param to link to the url
function ParamUrl() {
	$strurl="&Username=".GetStrParam("Username") ;
	$strurl.="&Type=".GetStrParam("Type") ;
	$strurl.="&ip=".GetStrParam("ip") ;
	$strurl.="&andS1=".GetStrParam("andS1") ;
	$strurl.="&andS2=".GetStrParam("andS2") ;
	$strurl.="&NotandS1=".GetStrParam("NotandS1") ;
	$strurl.="&NotandS2=".GetStrParam("NotandS2") ;
	return($strurl) ;
} // end of ParamUrl

// This function provide a pagination
function _Pagination($maxpos) {
    $curpos=GetParam("start_rec",0) ; // find current pos (0 if not)
		$width=GetParam("limitcount",100); // Number of records per page
		$PageName=$_SERVER["PHP_SELF"] ;
		
// Find the url parameters
		$strurl="action=Find".ParamUrl() ; ;
		$strurl.="&OrderBy=".GetStrParam("OrderBy") ;
		
//		echo "width=",$width,"<br>" ;
//		echo "curpos=",$curpos,"<br>" ;
//		echo "maxpos=",$maxpos,"<br>" ;
		echo "\n<center>" ;
		$countlink=0 ;
		for ($ii=0;$ii<$maxpos;$ii=$ii+$width) {
				$i1=$ii ;
				$i2=min($ii+$width,$maxpos) ;


				$countlink++ ;
				if ($countlink>20) {
				   echo "<a href=\"",$PageName,"?".$strurl."&start_rec=",$i1,"\"> ....</a> " ;
				   break ; // do not put too much links
				}

				if (($curpos>=$i1) and ($curpos<$i2)) { // mark in bold if it is the current position
					 echo "<b>" ;
				}
				echo "<a href=\"",$PageName,"?".$strurl."&start_rec=",$i1,"\">",$i1+1,"..",$i2,"</a> " ;
				if (($curpos>=$i1) and ($curpos<$i2)) { // end of mark in bold if it is the current position
					 echo "</b>" ;
				}
		}
		echo "</center>\n" ;
} // end of function Pagination


function DisplayAdminLogs($TData,$maxpos=0) {
	global $title;
	$title = "Admin logs";
	require_once "header.php";

//	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("admin/adminlogs.php", ww('MainPage')); // Displays the second menu

	echo "    <div id=\"main\">\n";
	echo "      <div id=\"teaser\">\n";
	echo "        <h1> Admin logs  </h1>\n";
	echo "      </div>\n";
	

	ShowLeftColumn("",VolMenu())  ; // Show the Actions
	ShowAds(); // Show the Ads
	
	// middle column
	echo "\n";
	echo "      <div id=\"col3\"> \n"; 
	echo "        <div id=\"col3_content\" class=\"clearfix\"> \n"; 

	echo "          <div class=\"info\">\n";
	

	$max = count($TData);
   $info_styles = array(0 => "              <tr class=\"blank\" align=\"left\" valign=\"center\">\n", 1 => "              <tr class=\"highlight\" align=\"left\" valign=\"center\">\n");

	echo "          <table cellspacing=\"10\" cellpadding=\"10\" style=\"font-size:11px;\">\n";
	echo "            <tr>\n";
	if ((GetStrParam(Username) == "") or (GetStrParam(Username2) != "")) {
		echo "              <th>Username</th>\n";
		echo "              <th>type</th>\n";
		echo "              <th>Str</th>\n";
		echo "              <th>created</th>\n";
		echo "              <th>ip</th>\n";
	} else {
		echo "              <th colspan=4 align=center> Logs for ", LinkWithUsername(fUsername(GetStrParam(Username))), "</th>\n";
	}
	echo "</tr>\n";
	for ($ii = 0; $ii < $max; $ii++) {
		$logs = $TData[$ii];
   	echo $info_styles[($ii%2)]; // this display the <tr>
		if ((GetStrParam(Username) == "") or (GetStrParam(Username2) != "")) {
			echo "<td>";
			echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?Username=" . $logs->Username . "&type=" . $logs->Type . "\">" . $logs->Username . "</a>";
			echo "</td>";
		}
		echo "<td>";
		echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?Username=" . GetStrParam(Username) . "&type=" . $logs->Type . "\">" . $logs->Type . "</a>";
		//		echo $logs->Type;
		echo "</td>";
		echo "<td>";
		echo $logs->Str;
		echo "</td>";
		echo "<td>$logs->created</td><td>&nbsp;";
		echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?Username=" . GetStrParam(Username) . "&ip=" . long2ip($logs->IpAddress) . "&type=" . GetStrParam(type) . "\">" . long2ip($logs->IpAddress) . "</a>";
		echo "</td>";
		echo "</tr>\n";
	}
	echo "          </table>\n<br>";
	if ($max>0) echo	_Pagination($maxpos) ;

	echo "          <hr>\n";
	echo "          <table>\n";
	echo "            <form method=post action=adminlogs.php>\n";
	if (HasRight("Logs") > 1) {
		echo "              <tr>\n";
		echo "                <td>Username</td><td><input type=text name=Username value=\"", GetStrParam(Username), "\"></td>\n";
	} else {
		echo "              <tr>\n";
		echo "                <td>Username</td><td><input type=text readonly name=Username value=\"", GetStrParam(Username), "\"></td>";
	}
	echo "                <td>Type</td><td><input type=text name=type value=\"", GetStrParam(type), "\"></td>\n";
	echo "                <td>Ip</td><td><input type=text name=ip value=\"", GetStrParam(ip), "\"></td>\n";
	echo "              </tr>\n";
	echo "              <tr><td>    Having</td><td><input type=text name=andS1 value=\"",GetStrParam("andS1"),"\"></td></tr>" ;
	echo "				<tr><td>and Having</td><td><input type=text name=andS2 value=\"",GetStrParam("andS2"),"\"></td></tr>" ;
	echo "				<tr><td>and not Having</td><td><input type=text name=NotandS1 value=\"",GetStrParam("NotandS1"),"\"></td></tr>" ;
	echo "				<tr><td>and not Having</td><td><input type=text name=NotandS2 value=\"",GetStrParam("NotandS2"),"\"></td></tr>" ;
	echo "                <tr><td colspan=2 align=center>";
	echo "<input type=submit id=submit>";
	echo "</td>\n";
	echo "              </tr>\n";
	echo "            </form>\n";
	echo "          </table>\n";
	echo "        </div>\n";

	require_once "footer.php";

}
?>
