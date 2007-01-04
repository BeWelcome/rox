<?php

// Just add add the bottom the language switch trick
echo "\n<table width=100%><tr><td align=right>" ;
  $langurl=$_SERVER['PHP_SELF']."?" ;
  if ($_SERVER['QUERY_STRING']!="") {
	  $QS = explode('&', $_SERVER['QUERY_STRING']);
		for ($ii=0;$ii<count($QS);$ii++) {
		  if (strpos($QS[$ii],"lang=")===false) $langurl=$langurl.$QS[$ii]."&" ;
		}
  }


if ($_SESSION['lang']!='fr') echo "<a href=\"",$langurl,"lang=fr\"><img border=0 height=10 src=\"images/fr.gif\" alt=\"Français\" width=16></a>&nbsp;" ;
if ($_SESSION['lang']!='eng') echo "<a href=\"",$langurl,"lang=eng\"><img border=0 height=10 src=\"images/en.gif\" alt=\"English\" width=16></a>&nbsp;" ;
if ($_SESSION['lang']!='esp') echo "<a href=\"",$langurl,"lang=esp\"><img border=0 height=10 src=\"images/esp.gif\" alt=\"Español\" width=16></a>&nbsp;" ;
if ($_SESSION['lang']!='de') echo "<a href=\"",$langurl,"lang=de\"><img border=0 height=10 src=\"images/de.gif\" alt=\"Deutsch\" width=16></a>&nbsp;" ;

//if ($_SESSION['switchtrans']!='on') echo "<a href=\"",$langurl,"switchtrans=off\"><img border=0 height=10 src=\"images/showtransarray.gif\" alt=\"switch to translation mode\" width=16></a>&nbsp;" ;
if ($_SESSION['switchtrans']=='on') {
//  echo "<a href=\"",$langurl,"switchtrans=off\"><img border=0 height=10 src=\"images/showtransarray.gif\" alt=\"remove translation mode\" width=16></a>&nbsp;" ;
	$pagetotranslate=$_SERVER['PHP_SELF'];
	if ($pagetotranslate{0}=="/") $pagetotranslate{0}="_" ; 
  echo "<a href=\"adminwords.php?showtransarray=1&pagetotranslate=".$pagetotranslate."\" target=new><img border=0 height=10 src=\"images/switchtrans.gif\" alt=\"go to current translation list for ".$_SERVER['PHP_SELF']."\" width=16></a>&nbsp;" ;
}
echo "</table>" ;
echo "\n</body>\n" ;
echo "</html>\n" ;
exit(0) ;
?>