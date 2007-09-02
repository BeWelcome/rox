<?php

/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/

 

global $DisplayHeaderWithColumnsIsSet;
global $DisplayHeaderShortUserContentIsSet;

echo "\n";
echo "        </div> <!-- col3content -->\n"; // col3content 
echo "        <!-- IE Column Clearing -->\n";
echo "        <div id=\"ie_clearing\">&nbsp;</div>\n";
echo "        <!-- Ende: IE Column Clearing -->\n";
echo "      </div> <!-- col3 --> \n"; // col3
echo "    </div>  <!-- main --> \n"; // main

echo "\n";
echo "    <div id=\"footer\">\n"; // footer

echo "      <p>".ww("ToChangeLanguageClickFlag");
echo "      </p>\n";

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
DisplayFlag("srp","srp.png","Serbian");
DisplayFlag("bg","bg.png","Bulgarian");
DisplayFlag("br","br.png","Portuguese(bra)");
DisplayFlag("ge","ge.png","Georgian");

//if ($_SESSION['switchtrans']!='on') echo "<a href=\"",$langurl,"switchtrans=off\"><img border=0 height=10 src=\"images/showtransarray.gif\" alt=\"switch to translation mode\" width=16></a>&nbsp;";
if (array_key_exists('switchtrans', $_SESSION) and $_SESSION['switchtrans'] == 'on') {
	//  echo "<a href=\"",$langurl,"switchtrans=off\"><img border=0 height=10 src=\"images/showtransarray.gif\" alt=\"remove translation mode\" width=16></a>&nbsp;";
	$pagetotranslate = $_SERVER['PHP_SELF'];
	if ($pagetotranslate { 0 }	== "/")
	   $pagetotranslate { 0 }= "_";
	echo "      <a href=\"".bwlink("admin/adminwords.php?showtransarray=1&amp;pagetotranslate=" . $pagetotranslate)."\" target=\"_blank\"><img height=\"11px\" width=\"16px\" src=\"".bwlink("images/switchtrans.gif")."\" alt=\"go to current translation list for " . $_SERVER['PHP_SELF'] . "\" title=\"go to current translation list for " . $_SERVER['PHP_SELF'] . "\" /></a>\n";
}

echo "      <p>&nbsp;</p>\n";

echo "	<p align=\"center\">";
echo "		<a href=\"" . bwlink("aboutus.php") . "\">" . ww("AboutUsPage") . "</a>|";
//echo "		<a href=\"" . bwlink("disclaimer.php") . "\">" . Disclaimer . "</a>|";
echo "		<a href=\"" . bwlink("impressum.php") . "\">" . ww("Impressum") . "</a>|";
echo "		<a href=\"" . bwlink("faq.php") . "\">" . ("faq") . "</a>|";
echo "		<a href=\"" . bwlink("feedback.php") . "\">" . ww("Contact") . "</a>";
echo "	</p>";



echo "      <p>&copy;2007 <strong>BeWelcome</strong> - ".ww("TheHospitalityNetwork")."</p>\n";
echo "      <p>The Layout is based on <a href=\"http://www.yaml.de/\">YAML</a> &copy; 2005-2006 by <a href=\"http://www.highresolution.info\">Dirk Jesse</a></p>\n";


echo "    </div>   <!-- main --> \n"; // footer
echo "  </div>   <!-- page --> \n"; // page
echo "  </div>   <!-- hold_floats --> \n"; // hold_floats ????
echo "</div>   <!-- page_margins --> \n"; // page_margins
if ($DisplayHeaderWithColumnsIsSet == true) { // if this header was displayed
//	echo "     </div>\n"; // columns-low
//	echo "   </div>\n"; // columns
//	echo " </div>\n"; // main-content

} // end of if  a header was displayed

if ($DisplayHeaderShortUserContentIsSet == true) { // if this header was displayed
	// echo "          </div>\n"; // user-content
}

echo "</body>\n";
echo "</html> <!-- footer end -->\n";
?>