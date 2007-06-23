<?php 

global $DisplayHeaderWithColumnsIsSet;
global $DisplayHeaderShortUserContentIsSet;

echo "\n";
echo "        </div>\n"; // col3content 
echo "        <!-- IE Column Clearing -->\n";
echo "        <div id=\"ie_clearing\">&nbsp;</div>\n";
echo "        <!-- Ende: IE Column Clearing -->\n";
echo "      </div>\n"; // col3
echo "    </div>\n"; // main

echo "\n";
echo "    <div id=\"footer\">\n"; // footer
echo "      <p>&copy;2007 <strong>BeWelcome</strong> - ".ww("TheHospitalityNetwork")."</p>\n";
echo "      <p>The Layout is based on <a href=\"http://www.yaml.de/\">YAML</a> &copy; 2005-2006 by <a href=\"http://www.highresolution.info\">Dirk Jesse</a></p>\n";
echo "      <p>&nbsp;</p>\n";
echo "      <p>".ww("ToChangeLanguageClickFlag");
echo "      </p>\n";
echo "      <p>&nbsp;</p>\n";

// Just add add the bottom the language switch trick
DisplayFlag("en","en.png","English");
DisplayFlag("fr","fr.png","Français");
DisplayFlag("esp","esp.png","Español");
DisplayFlag("de","de.png","Deutsch");
DisplayFlag("it","it.png","Italian");
DisplayFlag("ru","ru.png","Russian");
DisplayFlag("espe","espe.png","Esperanto");
DisplayFlag("pl","pl.png","Polish");
DisplayFlag("tr","tr.png","Turkish");
DisplayFlag("lt","lt.png","Lithuanian");
DisplayFlag("nl","nl.png","Dutch");
DisplayFlag("dk","dk.png","Danish");
DisplayFlag("cat","cat.png","Catalan");
DisplayFlag("fi","fi.png","Finnish");
DisplayFlag("pt","pt.png","Portuguese");
DisplayFlag("hu","hu.png","Hungarian");
DisplayFlag("lv","lv.png","Latvian");
DisplayFlag("gr","gr.png","Greek");
DisplayFlag("no","no.png","Norvegian");
DisplayFlag("sr","sr.png","Serbian");
DisplayFlag("bg","bg.png","Bulgarian");

//if ($_SESSION['switchtrans']!='on') echo "<a href=\"",$langurl,"switchtrans=off\"><img border=0 height=10 src=\"images/showtransarray.gif\" alt=\"switch to translation mode\" width=16></a>&nbsp;";
if ($_SESSION['switchtrans'] == 'on') {
	//  echo "<a href=\"",$langurl,"switchtrans=off\"><img border=0 height=10 src=\"images/showtransarray.gif\" alt=\"remove translation mode\" width=16></a>&nbsp;";
	$pagetotranslate = $_SERVER['PHP_SELF'];
	if ($pagetotranslate { 0 }	== "/")
	   $pagetotranslate { 0 }= "_";
	echo "      <a href=\"".bwlink("admin/adminwords.php?showtransarray=1&amp;pagetotranslate=" . $pagetotranslate)."\" target=\"_blank\"><img height=\"11px\" width=\"16px\" src=\"".bwlink("images/switchtrans.gif")."\" alt=\"go to current translation list for " . $_SERVER['PHP_SELF'] . "\" title=\"go to current translation list for " . $_SERVER['PHP_SELF'] . "\" /></a>\n";
}

echo "    </div>\n"; // footer
echo "  </div>\n"; // page
echo "</div>\n"; // page_margins
if ($DisplayHeaderWithColumnsIsSet == true) { // if this header was displayed
//	echo "     </div>\n"; // columns-low
//	echo "   </div>\n"; // columns
//	echo " </div>\n"; // main-content

} // end of if  a header was displayed

if ($DisplayHeaderShortUserContentIsSet == true) { // if this header was displayed
	// echo "          </div>\n"; // user-content
}

echo "</body>\n";
echo "</html>\n";
?>