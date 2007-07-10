<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
<title>Last error log</title>
</head>
<body>
<?php

require_once("lib/init.php");


if (!HasRight("Debug")) die("You miss Debug Right") ;

// THis file display the last errors

$NbLines = GetParam("NbLines",100);
$filename = "/etc/httpd/logs/www.bewelcome.org-error_log";

echo "tail --lines=".$NbLines." <b>",$filename,"</b><br>" ;
$t=array() ;
exec("tail --lines=".$NbLines." ".$filename,$t) ;
$max=count($t) ;
for ($ii=0;$ii<$max;$ii++) {
	 $ss=str_replace("\n","<br>",$t[$ii]) ;
  	 echo htmlentities($ss),"<br>\n";
}
echo "Current date=<b>[",date("D M j G:i:s Y"),"]</b><br>"  ;
echo "<form>" ;
echo "NbLines : <input type=text Name=NbLines value=\"".$NbLines."\"> <input type=submit id=submit>\n</form>\n" ;
echo "</body></html>";

exit (0);
?>

