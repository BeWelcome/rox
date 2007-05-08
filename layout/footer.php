<?php   

global $DisplayHeaderWithColumnsIsSet;
global $DisplayHeaderShortUserContentIsSet;

//echo "\n         </div>\n"; // ??? 
//echo "       </div>\n"; // main

echo "\n<div id=\"footer\">\n"; // footer
echo "<p>&copy;2007 <strong>BeWelcome</strong> - ".ww("TheHospitalityNetwork")."</p>";
echo "<p>".ww("ToChangeLanguageClickFlag");
echo "</p>";
echo "<p>&nbsp;</p>";

// Just add add the bottom the language switch trick
DisplayFlag("en","en.png","English");
DisplayFlag("fr","fr.png","Français");
DisplayFlag("esp","esp.png","Español");
DisplayFlag("de","de.png","Deutsch");
DisplayFlag("it","it.png","Italian");
DisplayFlag("ru","ru.png","Russian");
DisplayFlag("espe","esper.png","Esperanto");
DisplayFlag("pl","pl.png","Polish");
DisplayFlag("tr","tr.png","Turkish");
DisplayFlag("lt","lt.png","Lithuanian");
DisplayFlag("nl","nl.png","Dutch");
DisplayFlag("dk","dk.png","Danish");
DisplayFlag("cat","cat.png","Catalan");
DisplayFlag("fi","fi.png","Finnish");
DisplayFlag("pt","pt.png","Portuguese");
DisplayFlag("hu","hu.png","Hungarian");
DisplayFlag("lv","lv.gif","Latvian");

//if ($_SESSION['switchtrans']!='on') echo "<a href=\"",$langurl,"switchtrans=off\"><img border=0 height=10 src=\"images/showtransarray.gif\" alt=\"switch to translation mode\" width=16></a>&nbsp;";
if ($_SESSION['switchtrans'] == 'on') {
	//  echo "<a href=\"",$langurl,"switchtrans=off\"><img border=0 height=10 src=\"images/showtransarray.gif\" alt=\"remove translation mode\" width=16></a>&nbsp;";
	$pagetotranslate = $_SERVER['PHP_SELF'];
	if ($pagetotranslate { 0 }	== "/")
	   $pagetotranslate { 0 }= "_";
	echo "  <a href=\"".bwlink("admin/adminwords.php?showtransarray=1&pagetotranslate=" . $pagetotranslate)."\" target=new><img border=0 height=10 src=\"".bwlink("images/switchtrans.gif")."\" title=\"go to current translation list for " . $_SERVER['PHP_SELF'] . "\" width=16></a>\n";
}
echo "\n</div>\n"; // footer

if ($DisplayHeaderWithColumnsIsSet == true) { // if this header was displayed
	echo "     </div>\n"; // columns-low
	echo "   </div>\n"; // columns
	echo " </div>\n"; // main-content

} // end of if  a header was displayed

if ($DisplayHeaderShortUserContentIsSet == true) { // if this header was displayed
	echo "          </div>\n"; // user-content
}

echo "</body>\n";
echo "</html>\n";
?>