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

echo "      <p class=\"center\">".ww("ToChangeLanguageClickFlag");
echo "      </p>\n";

echo "      <div id=\"flags\" class=\"center\">\n";
// Just add add the bottom the language switch trick

// Seeking the available languages in the language table

$ss="SELECT languages.Name, languages.ShortCode,languages.EnglishName " ;
$ss.="FROM languages,words where words.IdLanguage=languages.id and words.code='WelcomeToSignup' ORDER BY FlagSortCriteria " ;
$qq=sql_query($ss) ;
while ($rr=mysql_fetch_object($qq)) {
			DisplayFlag($rr->ShortCode,$rr->ShortCode.".png",$rr->Name." (".$rr->EnglishName.")");
}
/*
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
DisplayFlag("ar","sa.png","Arabic");
DisplayFlag("he","il.png","Hebrew");
DisplayFlag("basque","basque.png","Basque");
*/

//if ($_SESSION['switchtrans']!='on') echo "<a href=\"",$langurl,"switchtrans=off\"><img border=0 height=10 src=\"images/showtransarray.gif\" alt=\"switch to translation mode\" width=16></a>&nbsp;";
if (array_key_exists('switchtrans', $_SESSION) and $_SESSION['switchtrans'] == 'on') {
	//  echo "<a href=\"",$langurl,"switchtrans=off\"><img border=0 height=10 src=\"images/showtransarray.gif\" alt=\"remove translation mode\" width=16></a>&nbsp;";
	$pagetotranslate = $_SERVER['PHP_SELF'];
	if ($pagetotranslate { 0 }	== "/")
	   $pagetotranslate { 0 }= "_";
	echo "      <a href=\"".bwlink("admin/adminwords.php?showtransarray=1&amp;pagetotranslate=" . $pagetotranslate)."\" target=\"_blank\"><img height=\"11px\" width=\"16px\" src=\"".bwlink("images/switchtrans.gif")."\" alt=\"go to current translation list for " . $_SERVER['PHP_SELF'] . "\" title=\"go to current translation list for " . $_SERVER['PHP_SELF'] . "\" /></a>\n";
}

echo "      </div>\n";
echo "      <p>&nbsp;</p>\n";

echo "	<p class=\"center\">";
echo "		<a href=\"../about\">" . ww("AboutUsPage") . "</a>|";
echo "    <a href=\"../terms\">". ww('TermsOfUse'). "</a>|";
echo "    <a href=\"../privacy\">". ww('Privacy'). "</a>|";
echo "		<a href=\"../impressum\">" . ww("Impressum") . "</a>|";
echo "		<a href=\"" . bwlink("faq.php") . "\">" . ("faq") . "</a>|";
echo "		<a href=\"" . bwlink("feedback.php") . "\">" . ww("Contact") . "</a>";
echo "	</p>";

echo "      <p class=\"center\">&copy;2007-2008 <strong>BeWelcome</strong> - ".ww("TheHospitalityNetwork")."</p>\n";

echo "    </div>   <!-- footer --> \n"; 
echo "</div>   <!-- container --> \n";
echo "</body>\n";
echo "</html>\n";

// This will log the delay if a $started_time=time() was issued in config.inc.php and if the delay exceed one second 
// in config.inc.php it must also be declared as global
global $started_time ;
if (isset($started_time)and($started_time>0)) {
	$started_time=$started_time-time() ;
	LogStr("Delay for the page according to footer ".$started_time." second [".$_SERVER['PHP_SELF']."]","DebugDelay") ;
}
?>
