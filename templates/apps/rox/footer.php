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
$words = new MOD_words();
global $DisplayHeaderWithColumnsIsSet;
global $DisplayHeaderShortUserContentIsSet;


# all these echos should be gone soon!

echo "    <div id=\"footer\">\n"; // footer

echo "      <p class=\"center\">".$words->get('ToChangeLanguageClickFlag');
echo "      </p>\n";

echo "      <div id=\"flags\" class=\"center\">\n";
echo $flagList;
echo "      </div>\n";

//if ($_SESSION['switchtrans']!='on') echo "<a href=\"",$langurl,"switchtrans=off\"><img border=0 height=10 src=\"images/showtransarray.gif\" alt=\"switch to translation mode\" width=16></a>&nbsp;";
if (isset($_SESSION['switchtrans']) && $_SESSION['switchtrans'] == 'on') {
	//  echo "<a href=\"",$langurl,"switchtrans=off\"><img border=0 height=10 src=\"images/showtransarray.gif\" alt=\"remove translation mode\" width=16></a>&nbsp;";
	$pagetotranslate = $_SERVER['PHP_SELF'];
	if ($pagetotranslate { 0 }	== "/")
	   $pagetotranslate { 0 }= "_";
	echo "      <a href=\"".bwlink("admin/adminwords.php?showtransarray=1&amp;pagetotranslate=" . $pagetotranslate)."\" target=\"_blank\"><img height=\"11px\" width=\"16px\" src=\"".bwlink("images/switchtrans.gif")."\" alt=\"go to current translation list for " . $_SERVER['PHP_SELF'] . "\" title=\"go to current translation list for " . $_SERVER['PHP_SELF'] . "\" /></a>\n";
}

echo "      <p>&nbsp;</p>\n";

echo "	<p class=\"center\">";
echo "		<a href=\"bw/aboutus.php\">" . $words->get('AboutUsPage') . "</a>|";
//echo "		<a href=\"" . bwlink("disclaimer.php") . "\">" . Disclaimer . "</a>|";
echo "		<a href=\"bw/impressum.php\">Impressum</a>|"; // FIXME: $words->get('Impressum')
echo "		<a href=\"bw/faq.php\">" . $words->get('faq') . "</a>|";
echo "		<a href=\"bw/feedback.php\">Contact</a>";    // FIXME: $words->get('Contact')
echo "	</p>";
echo "      <p class=\"center\">&copy;2007 <strong>BeWelcome</strong> - ".$words->get('TheHospitalityNetwork')."</p>\n";
echo "    </div>\n"; // footer


# Preliminary code for a nice bug report function:
#
# $bug_description  = "Version of BW Rox: ";   ## is version info already accessible?
# $bug_description .= "At URL: http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . "\n";
# $bug_description .= "User agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n";
# if logged in:
#   $bug_description .= "BeWelcome account: "

?>
