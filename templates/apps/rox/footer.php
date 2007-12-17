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
?>


<div id="footer">
  <p class="center"><?php echo $words->get('ToChangeLanguageClickFlag'); ?></p>
  <div id="flags" class="center">
  <?php echo $flagList; ?>
<?php

require_once(dirname(__FILE__)."/../../../htdocs/bw/lib/rights.php");

if (HasRight("Words")) {
	$pagetotranslate = $_SERVER['PHP_SELF'];
	if ($pagetotranslate { 0 } == "/")  // funky array stuff
	   $pagetotranslate { 0 } = "_";
	echo "<a href='".bwlink("admin/adminwords.php?showtransarray=1&amp;pagetotranslate=" . $pagetotranslate)."' target='_blank'><img height='11px' width='16px' src='".bwlink("images/switchtrans.gif")."' alt='go to current translation list for " . $_SERVER['PHP_SELF'] . "' title='go to current translation list for " . $_SERVER['PHP_SELF'] . "' /></a>\n";
} else {
  echo $_SESSION['switchtrans'];
}
?>
  </div>
  <p>&nbsp;</p>
  <p class="center">
    <a href="bw/aboutus.php"><?php echo $words->getFormatted('AboutUsPage'); ?></a>|
    <a href="terms" target="new"><?php echo $words->getFormatted('TermsOfUse'); ?></a>|
    <a href="privacy" target="new"><?php echo $words->getFormatted('Privacy'); ?></a>|
    <a href="bw/impressum.php"><?php echo $words->getFormatted('Impressum') ?></a>|
    <a href="bw/faq.php"><?php echo $words->getFormatted('faq'); ?></a>|
    <a href="bw/feedback.php"><?php echo $words->getFormatted('Contact'); ?></a>
  </p>
  <p class="center">&copy;2007 <strong>BeWelcome</strong> - "<?php echo $words->get('TheHospitalityNetwork'); ?>"</p>
  </div> <!-- footer -->

<?php
# Preliminary code for a nice bug report function:
#
# $bug_description  = "Version of BW Rox: ";   ## is version info already accessible?
# $bug_description .= "At URL: http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . "\n";
# $bug_description .= "User agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n";
# if logged in:
#   $bug_description .= "BeWelcome account: "
# <a href='https://bevolunteer.org/trac/--fix--link--?bugstuff=$bug_description'>report bug</a>

?>
