<?
// THis file display the last errors

  $filename="/etc/httpd/logs/error_log");
  $ss="tail ".$filename ;
  echo "\n<TABLE style='border:1px solid #cccccc;' cellPadding=3 cellSpacing=0 width=100% class=s>\n" ;
  echo "<TR><TH bgColor=#99ccff class=header>$ss</TH>\n" ;
  echo "<TR><TD bgColor=#ffffff class=s>" ;
  $sresult=htmlentities(system($ss)) ;
	echo str_replace("\n","<br><br>",$sresult) ;
	echo "</TD>\n</TABLE>\n<br>" ;
	
?>