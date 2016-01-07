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

<div class="stickyfooter">
    <div class="container">
<form style="display: inline;" action="a" method="post">
<div class="float_left"><?= $words->get('FooterSiteDisplayed', _languageFooterSelectorDropDown()) ?>
    <?php         if (MOD_right::get()->hasRight("Words", PVars::get()->lang)) {
        echo '</div>';
        $this->translator_block();
    } else {
    echo $words->get('FooterHelpUsTranslate', '<a href="http://www.bewelcome.org/groups/60/wiki">', '</a>') . '</div>';
} ?>
</form>
<div class="clearfix"></div>
<p>
    <a href="about"><strong><?php echo $words->getFormatted('AboutUsPage'); ?></strong></a> •
    <a href="terms" target="new"><?php echo $words->getFormatted('TermsOfUse'); ?></a> •
    <a href="privacy" target="new"><?php echo $words->getFormatted('Privacy'); ?></a> •
    <a href="impressum"><?php echo $words->getFormatted('Impressum') ?></a> •
    <a href="faq"><?php echo $words->getFormatted('faq'); ?></a> •
    <a href="about/feedback"><?php echo $words->getFormatted('Contact'); ?></a> •
    <a href="<?php echo $bugreportLink; ?>"><?=$words->get('ReportBug') ?></a>
<p>&copy; 2007-<?= date('Y') ?> <?= $words->get('CopyrightByBV', '<a style="display: inline" href="http://www.bevolunteer.org/" target="_blank">', '</a>')?> - <em>
     <?=$words->get('RunningOn') ?> <a style="display:inline" href="http://trac.bewelcome.org/">BW Rox</a> rev. <a href="http://github.com/BeWelcome/rox/commit/<?php echo $versionInfo; ?>"><?php echo $versionInfo; ?></a>
     (<span title="<?php echo $deploymentDateTime; ?>"><?php echo $deploymentDate; ?></span>)</em></p>
    </div>
</div>