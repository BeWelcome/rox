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
require_once("../lib/init.php");

/* deprecated */

$title="Last error log or last slow queries" ;

require_once "../layout/menus.php";

require_once "../layout/header.php";

Menu1("", "BW Errors log"); // Displays the top menu

Menu2("main.php", "Error log"); // Displays the second menu

$MenuAction="" ;
$MenuAction .= "<a href=\"".bwlink("admin/phplog.php?showerror=1&NbLines=10")."\">php logs 10</a>&nbsp;&nbsp;&nbsp;&nbsp;\n";
$MenuAction .= "<a href=\"".bwlink("admin/phplog.php?showerror=1&NbLines=100")."\">php logs 100</a>&nbsp;&nbsp;&nbsp;&nbsp;\n";
$MenuAction .= "<a href=\"".bwlink("admin/phplog.php?ShowSlowQuery=1")."\">Slow queries</a>&nbsp;&nbsp;&nbsp;&nbsp;\n";

DisplayHeaderShortUserContent("Admin logs",$MenuAction,""); // Display the header

if (!HasRight("Debug")) {
	echo("<p>You miss Debug Right</p>") ;
	require_once "../layout/footer.php";
	die() ;
}

echo "<p>$MenuAction</p><br/>" ;


if (GetStrParam("showerror","") !="") { 
	if (!HasRight("Debug","ShowErrorLog")) {
	   echo("<p>You miss Debug Right ShowErrorLog</p>") ;
	   require_once "../layout/footer.php";
	}
	 // This file display the last errors


	 $NbLines = GetStrParam("NbLines","100");
	 $filename = "/home/bwrox/".$_SERVER['SERVER_NAME']."/errors.log";
	// $filename = "/home/bwrox/".$_SYSHCVOL['SiteName']."/errors.log";

	 echo "tail --lines=".$NbLines." <b>",$filename,"</b><br>" ;
	 $t=array() ;
	 exec("tail --lines=".$NbLines." ".$filename,$t) ;
	 $max=count($t) ;
	 for ($ii=0;$ii<$max;$ii++) {
	 		 $ss=str_replace("\n","<br>",$t[$ii]) ;
  	 	 echo htmlentities($ss),"<br>\n";
	 }
	 echo "Current date=<b>[",date("D M j G:i:s Y"),"]</b><br>"  ;
	 echo "<form method=get>" ;
	 echo "<input type=hidden name=showerror value=10>" ;
	 echo "NbLines : <input type=text Name=NbLines value=\"".$NbLines."\"> <input type=submit>\n</form>\n" ;
}
if (GetStrParam("ShowSlowQuery","") !="") { 
	 // This file display the last errors
	if (!HasRight("Debug","ShowSlowQuery")) {
	   echo("<p>You miss Debug Right ShowSlowQuery</p>") ;
	   require_once "../layout/footer.php";
	   die() ;
	}


	 $NbLines = GetStrParam("NbLines","100");
	 $filename = "/home/bwrox/logs/mysql/mysql-slow.log";

	 echo "tail --lines=".$NbLines." <b>",$filename,"</b><br>" ;
	 $t=array() ;
	 exec("tail --lines=".$NbLines." ".$filename,$t) ;
	 $max=count($t) ;
	 for ($ii=0;$ii<$max;$ii++) {
	 		 # Time: 
	 		 $s1=str_replace("\n","<br>",$t[$ii]) ;
  	 	 $ss= htmlentities($s1)."<br>\n";
	 		 echo str_replace("# Time:","<hr># Time:",$ss) ;
	 }
	 echo "Current date=<b>[",date("D M j G:i:s Y"),"]</b><br>"  ;
	 echo "<form method=get>" ;
	 echo "<input type=hidden name=ShowSlowQuery value=10>" ;
	 echo "NbLines : <input type=text Name=NbLines value=\"".$NbLines."\"> <input type=submit>\n</form>\n" ;
}




require_once "../layout/footer.php";

exit (0);
?>

