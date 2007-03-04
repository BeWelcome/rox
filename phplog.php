<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
<title>Last error log</title>
</head>
<body>
<?php

// THis file display the last errors

$NbLines = 10;
if (isset ($_GET["showerror"])) {
	if ($_GET["showerror"] > 0)
		$NbLines = $_GET["showerror"];
}
$filename = "/etc/httpd/logs/www.bewelcome.org-error_log";
//$filename = "C:\wamp\logs\php_error.log" ;
$ss = "tail --lines=" . $NbLines . " " . $filename;
echo system($ss) ;
echo "<br>" ;
$sresult=htmlentities(system($ss)) ;
$tt=explode("\n",$sresult) ;
echo "\n<TABLE style='border:1px solid #cccccc;' cellPadding=3 cellSpacing=0 width=100% class=s>\n";
echo "<TR><TH colspan=2 bgColor=#cccccc class=header>", $ss, "</TH>\n";
for ($count=0;$count<$NbLines;$count++) {
  echo "<TR><TD>", $NbLine-$count , "</TD><TD bgColor=#ffff99 class=s>";
  echo $tt[$count] ;
//$sresult = htmlentities(system($ss));
//echo str_replace("\n", "<br>", $sresult);
  echo "</TD>\n";
}
echo "</TABLE>\n<br>";
echo "</body></html>";

exit (0);
?>

