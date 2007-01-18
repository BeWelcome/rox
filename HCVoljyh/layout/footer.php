<?php
global $DisplayHeaderWithColumnsIsSet ;
global $DisplayHeaderShortUserContentIsSet ;

if ($DisplayHeaderWithColumnsIsSet==true) { // if this header was displayed
  echo "\n         </div>\n"; // Class info 
  echo "       </div>\n";  // content
  echo "     </div>\n";  // columns-midle

  echo "   </div>\n";  // columns-low
  echo " </div>\n";  // columns
} // end of if  a header was displayed

if ($DisplayHeaderShortUserContentIsSet==true) { // if this header was displayed
  echo "          </div>\n" ; // user-content
}

echo "\n<div class=\"user-content\">\n" ;
// Just add add the bottom the language switch trick
echo "  <table width=100%>\n  <tr>\n  <td align=right>" ;
  $langurl=$_SERVER['PHP_SELF']."?" ;
  if ($_SERVER['QUERY_STRING']!="") {
	  $QS = explode('&', $_SERVER['QUERY_STRING']);
		for ($ii=0;$ii<count($QS);$ii++) {
		  if (strpos($QS[$ii],"lang=")===false) $langurl=$langurl.$QS[$ii]."&" ;
		}
  }


if ($_SESSION['lang']!='fr') echo "  <a href=\"",$langurl,"lang=fr\"><img border=0 height=10 src=\"images/fr.gif\" alt=\"Français\" width=16></a>\n" ;
if ($_SESSION['lang']!='eng') echo "  <a href=\"",$langurl,"lang=eng\"><img border=0 height=10 src=\"images/en.gif\" alt=\"English\" width=16></a>\n" ;
if ($_SESSION['lang']!='esp') echo "  <a href=\"",$langurl,"lang=esp\"><img border=0 height=10 src=\"images/esp.gif\" alt=\"Español\" width=16></a>\n" ;
if ($_SESSION['lang']!='de') echo "  <a href=\"",$langurl,"lang=de\"><img border=0 height=10 src=\"images/de.gif\" alt=\"Deutsch\" width=16></a>\n" ;
if ($_SESSION['lang']!='it') echo "  <a href=\"",$langurl,"lang=it\"><img border=0 height=10 src=\"images/it.gif\" alt=\"Italiano\" width=16></a>\n" ;
if ($_SESSION['lang']!='ru') echo "  <a href=\"",$langurl,"lang=ru\"><img border=0 height=10 src=\"images/ru.gif\" alt=\"russian\" width=16></a>\n" ;

//if ($_SESSION['switchtrans']!='on') echo "<a href=\"",$langurl,"switchtrans=off\"><img border=0 height=10 src=\"images/showtransarray.gif\" alt=\"switch to translation mode\" width=16></a>&nbsp;" ;
if ($_SESSION['switchtrans']=='on') {
//  echo "<a href=\"",$langurl,"switchtrans=off\"><img border=0 height=10 src=\"images/showtransarray.gif\" alt=\"remove translation mode\" width=16></a>&nbsp;" ;
	$pagetotranslate=$_SERVER['PHP_SELF'];
	if ($pagetotranslate{0}=="/") $pagetotranslate{0}="_" ; 
  echo "  <a href=\"adminwords.php?showtransarray=1&pagetotranslate=".$pagetotranslate."\" target=new><img border=0 height=10 src=\"images/switchtrans.gif\" alt=\"go to current translation list for ".$_SERVER['PHP_SELF']."\" width=16></a>&nbsp;\n" ;
}
echo "\n  </td>" ;
echo "\n  </table>" ;
echo "\n</div>\n" ; // user-content
echo "</body>\n" ;
echo "</html>\n" ;
?>