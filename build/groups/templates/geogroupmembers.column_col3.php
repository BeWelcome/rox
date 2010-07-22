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
     * @author matthias
     */

    /**
     * template showing a list of members for a place
     *
     * @package Apps
     * @subpackage templates
     */
    

$layoutbits = new MOD_layoutbits();
$purifier = MOD_htmlpure::getBasicHtmlPurifier();



    if (!$this->isGroupMember() && $this->group->Type == 'NeedInvitation')
    {
        echo "not public";
    }
    else
    {
?>
<div id="groups">
    
    <div id="searchoptions" class="floatbox">
        <div id="searchorder" class="float_left" >
            <span class="small"><label for="thisorder"><?php echo $words->getFormatted('Orderby'); ?>:</label></span>
            <form id="changethisorder" action="">
            <select name="OrderBy" id="thisorder" onchange="changeSortOrder(this.value);">
                <option value="accommodation">FIXME New Members</option>
                <option value="accommodation">FIXME Accommodation</option>
                <option value="age">FIXME Age</option>
                <option value="city">FIXME City</option>
                <option value="city">FIXME Last Login</option>
                <option value="city">FIXME Comments</option>
            </select>
            </form>
            <a href="#" id='flip-sort-direction-button' style="display: none;"><img src="images/icons/reverse_order.png" align="top" alt="changeorder" /><?php echo $words->getFormatted('ChangeSortDirection'); ?></a>
            <script type="text/javascript">
                $('flip-sort-direction-button').show();
                $('flip-sort-direction-button').observe('click', function(e){
                    var e = e || window.event;
                    Event.stop(e);
                    $('changethisorder').submit();
                });
            </script>
            <noscript>
                <input type="submit" value="<?php echo $words->getFormatted('ChangeSortDirection'); ?>"/>
            </noscript>
        </div> <!-- sortorder -->
        <?php $this->pager_widget->render(); ?>
    </div> <!-- searchoptions -->

    <h3><?= $words->get('MembersFound', $this->group->getMemberCount()); ?></h3>
    <table>
        <tr>
          <th colspan="2"><?= $words->get('Username'); ?></th>
          <th><?= $words->get('Host'); ?></th>
          <th><?= $words->get('MemberSince'); ?></th>
          <th><?= $words->get('LastLogin'); ?></th>
          <th><?= $words->get('Comments'); ?></th>
          <th><?= $words->get('ProfileSummary'); ?></th>
        </tr>
    <?php
        $count = 0;
        foreach ($this->pager_widget->getActiveSubset($this->group->getMembers('In', $this->pager_widget->getActiveStart(), $this->pager_widget->getActiveLength())) as $member)
        {
            $membershipinfo = $member->getGroupMembership($this->group);
            ?>
            <tr class="<?php echo $background = (($count % 2) ? 'highlight' : 'blank'); ?>">
                <td class="profilepicture"><?=MOD_layoutbits::PIC_50_50($member->Username) ?></td>
                <td class="profilepicture">
                    <a href="members/<?=$member->Username ?>" class="username"><?=$member->Username ?></a>
                    <ul>
                        <li><span class="small">FIXME AGE<?php echo $member->get_age();?></span></li>
                        <li><span class="small"><?php echo $member->get_city();?></span></li>
                    </ul>
                </td>
                <td><?php echo $words->get('Accomodation_' . $member->Accomodation);?></td>
                <td><?php echo $layoutbits->ago(strtotime($member->created));?></td>
                <td><?php echo $layoutbits->ago(strtotime($member->LastLogin));?></td>
                <td>FIXME COMMENTS</td>
                <td><?php echo $purifier->purify(stripslashes($words->mInTrad($member->ProfileSummary, $language_id=0, true))) ?></td>
            </tr>
            <?php
            $count++;
        }
        echo "</table>";
        $this->pager_widget->render();
    }
    ?>
</div>
