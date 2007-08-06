<?php
$words = new MOD_words();
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

echo "      <p>".$words->get('ToChangeLanguageClickFlag');
echo "      </p>\n";

echo $flagList;

//if ($_SESSION['switchtrans']!='on') echo "<a href=\"",$langurl,"switchtrans=off\"><img border=0 height=10 src=\"images/showtransarray.gif\" alt=\"switch to translation mode\" width=16></a>&nbsp;";
if (isset($_SESSION['switchtrans']) && $_SESSION['switchtrans'] == 'on') {
	//  echo "<a href=\"",$langurl,"switchtrans=off\"><img border=0 height=10 src=\"images/showtransarray.gif\" alt=\"remove translation mode\" width=16></a>&nbsp;";
	$pagetotranslate = $_SERVER['PHP_SELF'];
	if ($pagetotranslate { 0 }	== "/")
	   $pagetotranslate { 0 }= "_";
	echo "      <a href=\"".bwlink("admin/adminwords.php?showtransarray=1&amp;pagetotranslate=" . $pagetotranslate)."\" target=\"_blank\"><img height=\"11px\" width=\"16px\" src=\"".bwlink("images/switchtrans.gif")."\" alt=\"go to current translation list for " . $_SERVER['PHP_SELF'] . "\" title=\"go to current translation list for " . $_SERVER['PHP_SELF'] . "\" /></a>\n";
}

echo "      <p>&nbsp;</p>\n";

echo "	<p align=\"center\">";
echo "		<a href=\"bw/aboutus.php\">" . $words->get('AboutUsPage') . "</a>|";
//echo "		<a href=\"" . bwlink("disclaimer.php") . "\">" . Disclaimer . "</a>|";
echo "		<a href=\"bw/impressum.php\">Impressum</a>|"; // FIXME: $words->get('Impressum')
echo "		<a href=\"bw/faq.php\">" . $words->get('faq') . "</a>|";
echo "		<a href=\"bw/feedback.php\">Contact</a>";    // FIXME: $words->get('Contact')
echo "	</p>";



echo "      <p>&copy;2007 <strong>BeWelcome</strong> - ".$words->get('TheHospitalityNetwork')."\n";
echo "      Code partly based on <a href=\"http://sourceforge.net/projects/mytravelbook\">MyTravelBook</a></p>";
echo "      <p>The Layout is based on <a href=\"http://www.yaml.de/\">YAML</a> &copy; 2005-2006 by <a href=\"http://www.highresolution.info\">Dirk Jesse</a></p>\n";


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
?>
