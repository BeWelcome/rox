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
?>
<p>Your scope is <?= $this->nav['scope'] ?>
<table class="awstatstable">
<tr valign=top><td><?= $this->nav['currentLanguage'] ?></td><td>
<?php printf("%01.1f", $this->stat[0]['perc']); ?>
% done</td></tr></table>
</p>
<?php
if ($this->noScope){
    echo '<h2>You do not have translation rights for '.$this->nav['currentLanguage'].'</h2>';
} else {
    if (substr($this->type,-1,1)!='x'){
?>
<span id="awlistlimit">This list only contains items created at least 7 days ago and updated at most 6 months ago.</span>
<a href="admin/words/list/<?= $this->type ?>x">Show all</a>
<p>
<?php } ?>
<table id="awlisttable">
  <tr>
    <th class="awlistcode">Code & Description</th>
    <th class="awlisteng">English</th>
    <th class="awlisttrok"> <?= $this->nav['currentLanguage'] ?> </th>
  </tr>
<?php

 foreach ($this->data as $dat){
    echo '<tr><td class="awlistcode"><p>'.$dat->EngCode.'</p>';
    //    if (HasRight("Grep")) {
    //  echo " <a href=\"".bwlink("admin/admingrep.php?action=grep&submit=find&s2=ww&s1=" . $rWord->EngCode . "&scope=layout/*;*;lib/*")."\">grep</a>";
    //}
    
    //if (IsAdmin()) {
    //   if ($rWord->EngDnt=="yes") {
    //       echo "<strong>not translatable</strong>" ;
    //   }
    //   else {
    //       echo " translatable" ;
    //   }
    //}

    echo '<p class="awlistdesc">' . $dat->EngDesc . '</p>';
    echo '<p class="awlistupdate">Last update '.$layoutbits->ago(strtotime($dat->EngUpdated)).' '.$dat->EngMember.'</p>';

    echo '</td>';
    echo '<td class="awlisteng">'.$dat->EngSent.'</td>';
    if ($dat->missing){
        // missing translation
        echo "<td bgcolor=white align=center>";
        echo '<br /><a href="">ADD</a>';        
    } else {
        if ($dat->update){
            // update needed
            echo '<td class="awlisttrupd">';
            echo $dat->TrSent;
            echo '<form><fieldset><legend>update needed?</legend>';
            echo '<input type="submit" value="Edit" name="Edit">';
            //echo '<span class="awupdateneeded">update needed?</span>';
            echo '<input type="submit" value="This is ok" name="ThisIsOk">
                <input type="hidden" name="code" value="'.$dat->EngCode.'">
                </fieldset></form>';            
        } else {
            // uptodate translation
            echo '<td class="awlisttrok">'.$dat->TrSent;
            echo '<p>edit</p>';
        }
            echo '<p class="awlistupdate">Last update '.$layoutbits->ago(strtotime($dat->EngUpdated)).' '.$dat->TrMember.'</p>';
    }
    echo '</td></tr>';
 }

?>
</table>
<?php } ?>