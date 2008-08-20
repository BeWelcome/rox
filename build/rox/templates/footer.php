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
require_once "footer.helper.php";
?>

<div id="footer">
  <p class="center"><?php echo $words->get('ToChangeLanguageClickFlag'); ?></p>
  <div id="flags" class="center">
    <?php echo $languageSelector; ?>

    <?php translator_block() ?>
  </div>

  <p class="center">
    <a href="about"><?php echo $words->getFormatted('AboutUsPage'); ?></a> •
    <a href="terms" target="new"><?php echo $words->getFormatted('TermsOfUse'); ?></a> •
    <a href="privacy" target="new"><?php echo $words->getFormatted('Privacy'); ?></a> •
    <a href="impressum"><?php echo $words->getFormatted('Impressum') ?></a> •
    <a href="faq"><?php echo $words->getFormatted('faq'); ?></a> •
    <a href="about/feedback"><?php echo $words->getFormatted('Contact'); ?></a>
  </p>
  <div class="floatbox">
  <p class="float_left">
     &copy; 2007-2008 <strong>BeWelcome</strong> - <?php echo $words->get('TheHospitalityNetwork'); ?>
  </p>
  <p class="float_right">
     running on <a href="http://bevolunteer.org/trac">BW Rox <?php echo $versionInfo; ?></a>
     • <a href="<?php echo $bugreportLink; ?>">report bug</a>
  </p>
  </div>
  </div> <!-- footer -->

<?php
// List of DB queries with execution time
if (PVars::get()->debug) {
    $R = MOD_right::get();
    if($R->HasRight("Debug","DB_QUERY")) {
?>
<p class="center">
<a style="cursor:pointer;" onClick="$('query_list').toggle();">DB queries</a>
</p>
<div id='query_list' style="display:none;">
<?php
        foreach($query_list as $key=>$query) {
            echo ($key + 1).": $query<br />\n";
        }
?>
<br />
</div>
<?php }} ?>
