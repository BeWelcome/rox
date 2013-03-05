<?php
/*
Copyright (c) 2007-2009 BeVolunteer

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
    /** 
     * @author shevek
     */

    /** 
     * massmail details template
     * 
     * @package Apps
     * @subpackage Admin
     */
$words = new MOD_words();
$massmail = $this->massmail;;
?>
<div id="adminmassmail">
<h3><?php echo $words->get('AdminMassMailDetailsHeader'); ?></h3>
<table>
<?php
echo '<tr class="highlight"><th class="left" style="width:80%;">' . $massmail->Name . '</th>'
    . '<th><img src="images/icons/tick.png" alt="' . $words->get('AdminMassMailEnqueued') . '" title="' . $words->get('AdminMassMailEnqueued') . '"></th>'
    . '<th><img src="images/icons/exclamation.png" alt="' . $words->get('AdminMassMailTriggered') . '" title="' . $words->get('AdminMassMailTriggered') . '"></th>'
    . '<th><img src="images/icons/email.png" alt="' . $words->get('AdminMassMailSent') . '" title="' . $words->get('AdminMassMailSent') . '"></th>'
    . '<th><img src="images/icons/error.png" alt="' . $words->get('AdminMassMailFailed') . '"title="' . $words->get('AdminMassMailFailed') . '"></th>'
    . '</tr>';
echo '<tr class="blank"><td>&nbsp;</td>';
echo '<td><a href="admin/massmail/details/' . $this->id . '/enqueued">'. $massmail->ToApprove . '</a></td>';
echo '<td><a href="admin/massmail/details/' . $this->id . '/triggered">' . $massmail->ToSend . '</a></td>';
echo '<td><a href="admin/massmail/details/' . $this->id . '/sent">' . $massmail->Sent . '</a></td>';
echo '<td><a href="admin/massmail/details/' . $this->id . '/failed">' . $massmail->Failed . '</a></td></tr>';
if (!$this->detail) {
    echo '<tr class="highlight"><td class="left" colspan="5"><strong>' . $words->get('AdminMassMailAvailableLanguages') . '</strong>:';
    $lang = $_SESSION['lang'];
    foreach($massmail->Languages as $language) {
        if ($language->ShortCode == $lang) {
            echo '<strong>';
        }
        echo " " . $language->Name;
        if ($language->ShortCode == $lang) {
            echo '</strong>';
        }
    }
    $purifier = MOD_htmlpure::getAdvancedHtmlPurifier();
    echo '<tr class="blank"><td class="left" colspan="5"><strong>' . $words->get('AdminMassMailSubject') . '</strong>: ' 
        . str_replace("%username%", "Username", $words->getAsIs('BroadCast_Title_' . $massmail->Name))
        . '</td></tr>';
    echo '<tr class="blank"><td class="left" colspan="5"><strong>' . $words->get('AdminMassMailBody') . '</strong>:<br />' 
        . str_replace("%username%", "Username", nl2br($words->getAsIs('BroadCast_Body_' . $massmail->Name)))
        . '</td></tr>';
    echo '</table>';
} else {
    echo '<tr class="highlight"><td class="left" colspan="5"><strong>' . $words->get('AdminMassMailDetailsStatus') 
        . ':</strong> ' . $words->get('AdminMassMail' . $this->type) . '</td></tr>';
    echo '<tr class="blank"><td class="left" colspan="5">' . $words->get('AdminMassMailShowRecipients', $this->ROWSPERPAGE) . '</td></tr>';
    echo '</table>';
    echo $this->detail;
    $params = new StdClass;
    $params->strategy = new HalfPagePager('right');
    $params->page_url = 'admin/massmail/details/' . $this->id . '/' . $this->detail;
    echo $params->page_url;
    $params->page_url_marker = 'page';
    $params->page_method = 'url';
    $params->items = $this->count;
    $params->active_page = $this->pageno;
    $params->items_per_page = 20;
    $pager = new PagerWidget($params);
    $pager->render();
    echo '<table>';
    echo '<tr class="highlight"><th class="left">Username</th><th class="left">Country</th>'
        . '<th class="left">Status</th><th class="left">Language</th><tr>';
    $ii = 0;
    foreach($this->details as $detail) {
        if ($ii % 2 == 0) {
            echo '<tr class="blank">';
        } else {
            echo '<tr class="highlight">';
        }
        echo '<td class="left">' . $detail->Username . '</td>';
        echo '<td class="left">' . $detail->Country . '</td>';
        echo '<td class="left">' . $detail->Status . '</td>';
        echo '<td class="left">' . $detail->Language . '</td>';
        echo '<tr>';
        $ii++;
    }
    echo '</table>';
    $pager->render();
}
?>
</div>