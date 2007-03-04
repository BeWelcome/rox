<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
<title>Last error log</title>
</head>
<body>
<?php

// THis file display the last errors

$NbLines = 20;
if (isset ($_GET["showerror"])) {
	if ($_GET["showerror"] > 0)
		$NbLines = $_GET["showerror"];
}
$filename = "/etc/httpd/logs/www.bewelcome.org-error_log";
//$filename = "C:\wamp\logs\php_error.log" ;
//echo file_get_contents ($filename) ;
echo "<br>" ;
$tt=explode("\n",file_get_contents ($filename)) ;
$iMax=count($tt) ;
echo "\n<TABLE style='border:1px solid #cccccc;' cellPadding=3 cellSpacing=0 width=100% class=s>\n";
echo "<TR><TH colspan=2 bgColor=#cccccc class=header>", $filename," (total=",$iMax, ", displaying ".$NbLines." last lines)</TH>\n";
for ($count=0;$count<$NbLines;$count++) {
  	 $indice=$iMax-$NbLines+$count ;
	 if ($indice<0) continue ;
  echo "<TR><TD>", $indice , "</TD><TD bgColor=#ffff99>";
  echo htmlentities($tt[$indice]) ;
//$sresult = htmlentities(system($ss));
//echo str_replace("\n", "<br>", $sresult);
  echo "</TD>\n";
}
echo "</TABLE>\n<br>";
echo "</body></html>";

exit (0);
?>

