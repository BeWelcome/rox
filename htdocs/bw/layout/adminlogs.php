<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330,
Boston, MA  02111-1307, USA.

*/


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

//    echo "width=",$width,"<br>" ;
//    echo "curpos=",$curpos,"<br>" ;
//    echo "maxpos=",$maxpos,"<br>" ;
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


function DisplayAdminLogs($tData, $username, $type, $ip, $andS1, $andS2, $notAndS1, $notAndS2, $maxpos) {

  global $title;
  $rTime=LoadRow("select now() as ss") ;
  $title = "Admin logs Server time: ".$rTime->ss;
  require_once "header.php";

  Menu1("","Admin Logs page"); // Displays the top menu

  Menu2("admin/adminlogs.php", ww('MainPage')); // Displays the second menu

  DisplayHeaderShortUserContent($title);
  ShowLeftColumn("",VolMenu())  ; // Show the Actions

  // middle column
  echo "      <div id=\"col3\"> \n";
  echo "        <div id=\"col3_content\" class=\"clearfix\"> \n";
  echo "          <div class=\"info clearfix\">\n";


    $max = count($tData);
    $infoStyles = array(0 => "              <tr class=\"blank\" align=\"left\" valign=\"center\">\n",
                        1 => "              <tr class=\"highlight\" align=\"left\" valign=\"center\">\n");

  echo "          <table cellspacing=\"10\" cellpadding=\"10\" style=\"font-size:11px;\">\n";
  echo "            <tr>\n";
  if (empty($username)) {
    echo "              <th>Username</th>\n";
    echo "              <th>Type</th>\n";
    echo "              <th>Str</th>\n";
    echo "              <th>created</th>\n";
    echo "              <th>ip</th>\n";
  } else {
    echo "              <th colspan=4 align=center> Logs for ", LinkWithUsername(fUsername($username)), "</th>\n";
  }
  echo "</tr>\n";
  for ($ii = 0; $ii < $max; $ii++) {
    $logs = $tData[$ii];
    echo $infoStyles[($ii%2)]; // this displays the <tr>
    if (!empty($logs->Username)) {
      echo "<td>";
      echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?Username=" . $logs->Username . "\">" . $logs->Username . "</a>";
      echo "</td>";
    }
    else {
      echo "<td>";
      // To do according to ip addresses replace with Google, Yahoo .. etc - an external solution is to be find
      switch (long2ip($logs->IpAddress)) {
        case "66.249.72.206" :
           echo "Googlebot/2.1" ;
           break ;
        case "74.6.23.107" :
           echo "Yahoo slurp" ;
           break ;
        case "127.0.0.1" :
           echo "<i>localhost</i>" ;
           break ;
        default :
          echo "<i>not logged</i>";
           break ;
          break ;
      }
      echo "</td>";
    }
    echo "<td>";
    echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?Type=" . $logs->Type . "\">" . $logs->Type . "</a>";
    //    echo $logs->Type;
    echo "</td>";
    echo "<td>";
    echo $logs->Str;
    echo "</td>";
    echo "<td>$logs->created</td><td>&nbsp;";
    echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?ip=" . long2ip($logs->IpAddress) . "\">" . long2ip($logs->IpAddress) . "</a>";
		echo " <a href=\"http://ws.arin.net/whois/?queryinput=+".long2ip($logs->IpAddress)." \" target=\"new\">arinc</a>" ;
		echo " <a href=\"http://outils-rezo.info/cgi-bin/action.cgi?valeur=".long2ip($logs->IpAddress)."&cmd=Whois\" target=\"new\">whois</a>" ;
    echo "</td>";
    echo "</tr>\n";
  }
  echo "          </table>\n<br>";
  if ($max>0) echo _Pagination($maxpos);

  echo "          <hr />\n";
  echo "          <table>\n";
  echo "            <form method='post' action='adminlogs.php'>\n";
  if (HasRight("Logs") > 1) {
    echo "              <tr>\n";
    echo "                <td>Username</td><td><input type=\"text\" name=\"Username\" value=\"" . (!empty($username)?$username:'') . "\"></td>\n";
  } else {
    echo "              <tr>\n";
    echo "                <td>Username</td><td><input type=\"text\" readonly=\"readonly\" name=\"Username\" value=\"" . $username . "\"></td>";
  }
  echo "                <td>Type</td><td><input type=text name=Type value=\"" . $type . "\"></td>\n";
  echo "                <td>Ip</td><td><input type=text name=ip value=\"" . $ip . "\"></td>\n";
  echo "              </tr>\n";
  echo "              <tr><td>    Having</td><td><input type=text name=andS1 value=\"" . $andS1 . "\"></td></tr>" ;
  echo "        <tr><td>and Having</td><td><input type=text name=andS2 value=\"" . $andS2 . "\"></td></tr>" ;
  echo "        <tr><td>and not Having</td><td><input type=text name=NotandS1 value=\"" . $notAndS1 . "\"></td></tr>" ;
  echo "        <tr><td>and not Having</td><td><input type=text name=NotandS2 value=\"" . $notAndS2 . "\"></td></tr>" ;
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
