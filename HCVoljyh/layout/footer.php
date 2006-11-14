<?php
echo "\n<table width=100%><tr><td align=right>" ;
if (!isset($langurl)) {
  $langurl=$_SERVER['PHP_SELF']."?x=1&".$_SERVER['QUERY_STRING'] ;
}
if ($_SESSION['lang']!='fr') echo "<a href=\"",$langurl,"&lang=fr\"><img border=0 height=10 src=\"images/fr.gif\" alt=\"Français\" width=16></a>&nbsp;" ;
if ($_SESSION['lang']!='eng') echo "<a href=\"",$langurl,"&lang=eng\"><img border=0 height=10 src=\"images/en.gif\" alt=\"English\" width=16></a>&nbsp;" ;
if ($_SESSION['lang']!='esp') echo "<a href=\"",$langurl,"&lang=esp\"><img border=0 height=10 src=\"images/esp.gif\" alt=\"Español\" width=16></a>&nbsp;" ;
if ($_SESSION['lang']!='de') echo "<a href=\"",$langurl,"&lang=de\"><img border=0 height=10 src=\"images/de.gif\" alt=\"Deutsch\" width=16></a>&nbsp;" ;
//if (IsAdmin()) echo "&nbsp <a href=\"",$langurl,"&clearadmin\">clear admin mode</a>&nbsp;" ;
echo "</table>" ;
echo "\n</body>\n" ;
echo "</html>\n" ;
exit(0) ;
?>