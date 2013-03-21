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
  <?php translator_block() ?>
  <div class="subcolumns">
      <div class="c50l">
          <div class="subcl">
            <div class="floatbox">
              <p class="float_left linklist">
                <a href="about"><?php echo $words->getFormatted('AboutUsPage'); ?></a> • 
                <a href="terms" target="new"><?php echo $words->getFormatted('TermsOfUse'); ?></a> • 
                <a href="privacy" target="new"><?php echo $words->getFormatted('Privacy'); ?></a> • 
                <a href="impressum"><?php echo $words->getFormatted('Impressum') ?></a> • 
                <a href="faq"><?php echo $words->getFormatted('faq'); ?></a> • 
                <a href="about/feedback"><?php echo $words->getFormatted('Contact'); ?></a>
              </p>
            </div>    
          </div>
      </div>
      <div class="c50r">
          <div class="subcr">
            <div id="language-select" class="float_right">
            <?php
            echo $words->get('Languages') . ": " . _languageFooterSelectorDropDown()
            . "&nbsp;&nbsp;&nbsp;" . $words->get('FooterHelpUsTranslate', '<a href="http://www.bewelcome.org/groups/60/wiki">', '</a>');
            ?>
            </div>    
          </div>
      </div>
    </div>
<div class="floatbox">
  <p class="float_left">
     &copy; 2007-<?php echo date('Y') . ' ' . $words->get('CopyrightByBV', '<a href="http://www.bevolunteer.org/" target="_blank">', '</a>'); ?>
  </p>
  <p class="float_right">
     Running on <a href="http://trac.bewelcome.org/">BW Rox</a> rev. <a href="http://gitorious.org/bewelcome/rox/commit/<?php echo $versionInfo; ?>"><?php echo $versionInfo; ?></a>
     (<span title="<?php echo $deploymentDateTime; ?>"><?php echo $deploymentDate; ?></span>)
     • <a href="<?php echo $bugreportLink; ?>">Report bug</a>
  </p>
  </div>
  </div> <!-- footer -->

<?php
// List of DB queries with execution time
if (PVars::get()->debug) {
    $R = MOD_right::get();
    if($R->HasRight("Debug","DB_QUERY")) {
?>
<p>
<a style="cursor:pointer;" onclick="$('query_list').toggle();">DB queries</a>
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
