<?php
// THis file display the last errors

  $NbLines=10 ;
	if (isset($_GET["showerror"])) {
	  if ($_GET["showerror"]>0) 
		  $NbLines=$_GET["showerror"] ;
	}
  $filename="/etc/httpd/logs/error_log";
  $ss="tail --lines=".$NbLines." ".$filename ;
  echo "\n<TABLE style='border:1px solid #cccccc;' cellPadding=3 cellSpacing=0 width=100% class=s>\n" ;
  echo "<TR><TH colspan=2 bgColor=#cccccc class=header>$ss</TH>\n" ;
  echo "<TR><TD>",$NbLines--,"</TD><TD bgColor=#ffff99 class=s>" ;
  $sresult=htmlentities(system($ss)) ;
	echo str_replace("\n","<br><br>",$sresult) ;
	echo "</TD>\n</TABLE>\n<br>" ;
	
?>