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
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
<title>Last error log or last slow queries</title>
</head>
<body>
<?php


if (!HasRight("Debug")) die("You miss Debug Right") ;

if (GetStrParam("showerror","") !="") { 
	if (!HasRight("Debug","ShowErrorLog")) die("You miss Debug Right with ShowErrorLog") ;
	 // This file display the last errors


	 $NbLines = GetStrParam("NbLines","100");
	 $filename = "/home/bwrox/bwrox.production/errors.log";

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
	 echo "</body></html>";
}
if (GetStrParam("ShowSlowQuery","") !="") { 
	 // This file display the last errors
	if (!HasRight("Debug","ShowSlowQuery")) die("You miss Debug Right with ShowSlowQuery") ;


	 $NbLines = GetStrParam("NbLines","100");
	 $filename = "/var/lib/mysql/ns20516-slow.log";

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
	 echo "</body></html>";
}





exit (0);
?>

