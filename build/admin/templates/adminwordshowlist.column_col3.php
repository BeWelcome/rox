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
     * @author crumbking, Tsjoek
     */

    /** 
     * words management overview template
     * 
     * @package Apps
     * @subpackage Admin
     */

$layoutbits = new MOD_layoutbits;
$formkit = $this->layoutkit->formkit;
$callback_tag = $formkit->setPostCallback('AdminWordController', 'showListCallback');
$this->words = new MOD_words();


if ($this->noScope){
    echo '<h2>You do not have translation rights for this language</h2>';
}
echo 'Your scope: '.$this->showScope();
if (!$this->noScope){
?>
<table class="awstatstable"><tr valign=top>
  <td><?= $this->nav['currentLanguage'] ?></td>
  <td>
<?php
    printf("%01.1f", (float)$this->stat[0]['perc']);
?>
    % done</td>
</tr></table>
</p>
<?php
    if ($this->filter == 'short'){
?>
<span id="awlistlimit">This list only contains items created at least 7 days ago and updated at most 6 months ago.</span>
<a href="admin/word/list/<?= htmlspecialchars($this->type); ?>/long">Remove this limitation</a>
<p>
<?php } ?>
<table id="awlisttable">
<form method="POST">
<?= $callback_tag ?>
  <tr>
    <th class="awlistcode">Code & Description</th>
    <?php if ($this->nav['shortcode'] != 'en'){?><th class="awlisteng">English</th><?php } ?>
    <th class="awlisttrok"> <?= $this->nav['currentLanguage'] ?> </th>
  </tr>

<?php
    foreach ($this->data as $dat){
        echo '<tr><td class="awlistcode"><p>'.htmlspecialchars($dat->EngCode).'</p>';
        if ($dat->EngDnt=='yes'){echo '<span class="awdntwarning">Do not translate</span>';}
        if ($this->nav['grep']>0) {
           echo '<a href="bw/admin/admingrep.php?action=grep&submit=find&s2=ww&s1=';
           echo htmlspecialchars($dat->EngCode) . '&scope=layout/*;*;lib/*">grep</a>';
        }

        echo '<p class="smallXtext">' . htmlspecialchars($dat->EngDesc) . '</p>';
    
        echo '</td>';
        if ($this->nav['shortcode'] != 'en'){
            echo '<td class="awlisteng">'.$this->purifier->purify($dat->EngSent);
            echo '<p class="awlistupdate">Last update '.$layoutbits->ago(strtotime($dat->EngUpdated)).' '.htmlspecialchars($dat->EngMember).'</p>';
            echo '</td>';
        }
        if ($dat->missing){
            // missing translation
            echo "<td bgcolor=white align=center>";
            echo '<br /><a href="/admin/word/edit/'.htmlspecialchars($dat->EngCode).'">ADD</a>';
        } else {
            if ($dat->update){
                // update needed
                echo '<td class="awlisttrupd">';
                echo $this->purifier->purify($dat->Sentence);
                echo '<fieldset><legend>update needed?</legend>';
                echo '<input type="submit" value="Edit" name="Edit_'.(int)$dat->TrId.'">';
                echo '<input type="submit" value="This is ok" name="ThisIsOk_'.(int)$dat->TrId.'">';
                echo '</fieldset>';            
            } else {
                // up-to-date translation
                echo '<td class="awlisttrok">'.$this->purifier->purify($dat->Sentence);
                echo '<p><a href="/admin/word/edit/'.htmlspecialchars($dat->EngCode).'">edit</a></p>';  
            }
            echo '<p class="awlistupdate">Last update '.$layoutbits->ago(strtotime($dat->TrUpdated)).' '.htmlspecialchars($dat->TrMember).'</p>';
        }
        echo '</td></tr>';
    }
?>
</form></table>
<?php } ?>