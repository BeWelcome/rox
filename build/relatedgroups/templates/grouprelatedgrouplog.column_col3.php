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
?>
<fieldset>
    <legend><?= $words->get('AdministrateRelatedGroupsTitle'); ?></legend>
    <div class="subcolumns">
    <h3><?php echo $words->getFormatted('CurrentRelatedGroupsTitle');?></h3>
        <ul class="floatbox">
            <?php 
            $relatedgroups = $this->group->findRelatedGroups($groupId);
            foreach ($relatedgroups as $group_data) :
                if (strlen($group_data->Picture) > 0) {
                    $img_link = "groups/thumbimg/{$group_data->getPKValue()}";
                } else {
                    $img_link = "images/icons/group.png";
                } ?>
                <li class="picbox_relatedgroup float_left">
                    <a href="groups/<?php echo $group_data->getPKValue() ?>">
                        <img class="framed_relatedgroup float_left" alt="Group" src="<?php echo $img_link; ?>"/>
                    </a>
                    <div class="userinfo"><span class="small">
                    <h4><a href="groups/<?php echo $group_data->getPKValue() ?>"><?php echo htmlspecialchars($group_data->Name, ENT_QUOTES) ?></a></h4>
                        <?php echo $words->get('GroupsMemberCount');?>: <?php echo $group_data->getMemberCount(); ?><br />
                        <?php echo $words->get('GroupsNewMembers');?>: <?php echo count($group_data->getNewMembers()) ; ?><br />
                    </span></div> <!-- userinfo -->
                </li> <!-- userpicbox_relatedgroup -->
            <?php endforeach; ?>
        </ul>   
    </div><!-- subcolumns -->
    <div class="subcolumns">   
        <?php $logvar = $this->logs;?>
    </div><!-- subcolumns -->
    <div class="subcolumns"> <?php
        if ($this->isGroupMember())  {
            $add_button_link = "groups/{$this->group->id}/selectrelatedgroup";
            $add_button_word = $words->get('AddRelatedGroupButton');
            $delete_button_link = "groups/{$this->group->id}/selectdeleterelatedgroup";
            $delete_button_word = $words->get('RemoveRelatedGroupButton');
            ?>
            <div class="c50l">
                <div class="subcl">
                    <a class="button" href="<?php echo $add_button_link; ?>">
                        <span><?php echo $add_button_word; ?></span>
                    </a>
                </div><!-- subcl -->
            </div><!-- c50l -->
            <div class="c50r">
                <div class="subcr"> <?php 
                    if (!empty($relatedgroups)) { ?>
                        <a class="button" href="<?php echo $delete_button_link; ?>">
                            <span><?php echo $delete_button_word; ?></span>
                        </a><?php
                    } ?>
                </div><!-- subcr -->
            </div><!-- c50r --> <?php
        } ?>
    </div><!-- subcolumns -->
    <?php
    if ($this->isGroupAdmin) {
        ?>        
        <div class="subcolumns">
            <br />
            <h4> <?php echo $words->get('NbOfLogEntries', count($logvar)); ?> </h4>
            <ul class="floatbox"> <?php
                foreach ($logvar as &$value) : ?>
                    <li class="picbox_relatedgroup float_left">
                        <img class="framed_relatedgroup float_left" src="members/avatar/<?php echo $value->member->Username; ?>?xs"/>
                        <div class="userinfo">
                            <span class="small">
                                <?php
                                $layoutbits->ago(strtotime($value->ts));
                                ?>
                                <br /><?php 
                                $memberlink = '<a href="members/' . $value->member->Username . '">' . $value->member->Username . '</a>';
                                $grouplink =  '<a href="groups/' . $value->relatedgroup->getPKValue() . '">' . htmlspecialchars($value->relatedgroup->Name, ENT_QUOTES) . '</a>'; 
                                $logentry = $words->get($value->RelatedGroupAction, $memberlink, $grouplink);
                                echo $logentry; ?><br />
                            </span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>  <!-- floatbox --> 
        </div><!-- subcolumns -->  
        <?php
    }
    ?>    
</fieldset>

