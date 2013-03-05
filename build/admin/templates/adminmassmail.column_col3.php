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
     * massmail overview template
     * 
     * @package Apps
     * @subpackage Admin
     */
$words = new MOD_words();
?>
<div id="adminmassmail">
<h3><?php echo $words->get('AdminMassMailListHeader'); ?></h3>
<?php
$ii = 0; 
$this->pager->render();
if (isset($_SESSION['AdminMassMailStatus'])) {
    echo '<div class="success">';
    $status = $_SESSION['AdminMassMailStatus'];
    switch($status[0]) {
        case 'Edit':
            echo $words->get('AdminMassMailSuccessEdit', $status[1]);
            break;  
        case 'Create':
            echo $words->get('AdminMassMailSuccessCreate', $status[1]);
            break;  
        case 'Enqueue':
            echo $words->get('AdminMassMailSuccessEnqueue', $status[1], $status[2]);
            break;
        case 'Unqueue':
            echo $words->get('AdminMassMailSuccessUnqueue', $status[1], $status[2]);
            break;
        case 'Trigger':
            echo $words->get('AdminMassMailSuccessTrigger', $status[1], $status[2]);
            break;
        case 'Untrigger':
            echo $words->get('AdminMassMailSuccessUntrigger', $status[1], $status[2]);
            break;
    }
    echo '</div>';
    unset($_SESSION['AdminMassMailStatus']);
}
echo '<table>';
echo '<tr><th class="left" style="width:50%;">' . $words->getBuffered('AdminMassMailName') . '</th>';
if ($this->canTrigger) {
    echo '<th colspan="5">' . $words->getBuffered('AdminMassMailActions') . '</th>';
} else {
    echo '<th colspan="3">' . $words->getBuffered('AdminMassMailActions') . '</th>';
}
echo '<th><img src="images/icons/tick.png" alt="' . $words->getBuffered('AdminMassMailEnqueued') . '"></th>'
    . '<th><img src="images/icons/exclamation.png" alt="' . $words->getBuffered('AdminMassMailTriggered') . '"></th>'
    . '<th><img src="images/icons/email.png" alt="' . $words->getBuffered('AdminMassMailSent') . '"></th>'
    . '<th><img src="images/icons/error.png" alt="' . $words->getBuffered('AdminMassMailFailed') . '"></th>'
    . '<th>' . $words->flushBuffer() . '</th>' // cell for translations
    . '</tr>';

foreach($this->pager->getActiveSubset($this->massmails) as $massmail) {
    $enqueued = ($massmail->enqueuedCount != 0);
    $triggered = ($massmail->triggeredCount != 0);
    $edit = '<a href="admin/massmail/edit/' . $massmail->id . '">'
        . '<img src="images/icons/comment_edit.png" alt="edit" /></a><br><a href="admin/massmail/edit/' 
        . $massmail->id . '">' . $words->getBuffered('AdminMassMailEdit') . '</a>';    
    $enqueue = '<a href="admin/massmail/enqueue/' . $massmail->id . '">'
        . '<img src="images/icons/tick.png" alt="enqueue" /></a><br/><a href="admin/massmail/enqueue/' 
        . $massmail->id . '">'. $words->getBuffered('AdminMassMailEnqueue') . '</a>'; 
    $unqueue = '<a href="admin/massmail/unqueue/' . $massmail->id . '">'
        . '<img src="images/icons/delete.png" alt="unqueue" /></a><br/><a href="admin/massmail/unqueue/' 
        . $massmail->id . '">'. $words->getBuffered('AdminMassMailUnqueue') . '</a>'; 
    $trigger = '<a href="admin/massmail/trigger/' . $massmail->id . '">'
        . '<img src="images/icons/exclamation.png" alt="trigger" /></a><br/><a href="admin/massmail/trigger/' 
        . $massmail->id . '">'. $words->getBuffered('AdminMassMailTrigger') . '</a>'; 
    $untrigger = '<a href="admin/massmail/trigger/' . $massmail->id . '">'
        . '<img src="images/icons/delete.png" alt="untrigger" /></a><br/><a href="admin/massmail/untrigger/' 
        . $massmail->id . '">'. $words->getBuffered('AdminMassMailUntrigger') . '</a>'; 
    if ($ii % 2 == 0) {
        $str = '<tr class="blank">';
    } else {
        $str = '<tr class="highlight">';
    }
    $str .= '<td class="left"><a href="admin/massmail/details/' . $massmail->id . '">' . $massmail->Name . '</a></td>';
    if (!$enqueued) {
        $str .= '<td>' . $edit . '</td>';
    } else {   
        $str .= '<td><span style="visibility: hidden;">' . $edit . '<span></td>';
    }
    $str .= '<td>' . $enqueue . '</td>';
    if ($enqueued) {
        $str .= '<td>' . $unqueue . '</td>';
    } else {
        $str .= '<td><span style="visibility: hidden;">' . $unqueue . '</span></td>';
    }
    if ($this->canTrigger) {
        if ($enqueued) {
            $str .= '<td>' . $trigger . '</td>';
        } else {
            $str .= '<td><span style="visibility: hidden;">' . $trigger . '</span></td>';
        }
        if ($triggered) {
            $str .= '<td>' . $untrigger . '</td>';
        } else {
            $str .= '<td><span style="visibility: hidden;">' . $untrigger . '</span></td>';
        }
    }
    $str .= '<td><a href="admin/massmail/details/' . $massmail->id . '/enqueued">' . $massmail->enqueuedCount . '</a></td>';
    $str .= '<td><a href="admin/massmail/details/' . $massmail->id . '/triggered">' . $massmail->triggeredCount . '</a></td>';
    $str .= '<td><a href="admin/massmail/details/' . $massmail->id . '/sent">' . $massmail->sentCount . '</a></td>';
    $str .= '<td><a href="admin/massmail/details/' . $massmail->id . '/failed">' . $massmail->failedCount . '</a></td>';
    $str .= '<td>' . $words->flushBuffer() . '</td>';
    $str .= '</tr>';
    echo $str;
    $ii++;
}

echo '</table>';
$this->pager->render();
?>
</div>