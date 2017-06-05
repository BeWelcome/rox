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
        <div class="col-12"><h3><?php echo $words->getFormatted('CurrentRelatedGroupsTitle');?></h3></div>

        <?php
        $relatedgroups = $this->group->findRelatedGroups($groupId);
        foreach ($relatedgroups as $group_data) :
        ?>

        <div class="col-12 col-md-6 col-lg-4 p-2">
            <div class="float-left h-100 mr-2" style="width: 80px;">
                <!-- group image -->
                <a href="groups/<?=$group_data->getPKValue() ?>">
                    <img class="framed" alt="<?=htmlspecialchars($group_data->Name, ENT_QUOTES) ?>" src="<?= ((strlen($group_data->Picture) > 0) ? "groups/thumbimg/{$group_data->getPKValue()}" : 'images/icons/group.png' ) ?>" style="width: 80px; height: 80px;" />
                </a>
            </div>
            <div>
                <!-- group name -->
                <a href="groups/<?=$group_data->getPKValue() ?>" class="h4"><?=htmlspecialchars($group_data->Name, ENT_QUOTES) ?></a>
                <!-- group details -->
                <ul class="groupul mt-1">
                    <li><i class="fa fa-group pr-1" title="<?php echo $words->get('GroupsMemberCount');?>"></i> <?=$group_data->getMemberCount(); ?></li>
                    <li><?= $words->get('GroupsDateCreation');?>: <?=date('d F Y', ServerToLocalDateTime(strtotime($group_data->created), $this->getSession())); ?></li>
                </ul>
            </div>
        </div>
        <?php endforeach; ?>

    <div class="col-12">
        <?php $logvar = $this->logs;?>
    </div><!-- subcolumns -->
    <div class="col-12"> <?php
        if ($this->isGroupMember())  {
            $add_button_link = "groups/{$this->group->id}/selectrelatedgroup";
            $add_button_word = $words->get('AddRelatedGroupButton');
            $delete_button_link = "groups/{$this->group->id}/selectdeleterelatedgroup";
            $delete_button_word = $words->get('RemoveRelatedGroupButton');
            ?>
            <div class="c50l">
                <div class="subcl">
                    <a class="button" role="button" href="<?php echo $add_button_link; ?>">
                        <span><?php echo $add_button_word; ?></span>
                    </a>
                </div><!-- subcl -->
            </div><!-- c50l -->
            <div class="c50r">
                <div class="subcr"> <?php 
                    if (!empty($relatedgroups)) { ?>
                        <a class="button" role="button" href="<?php echo $delete_button_link; ?>">
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
            <ul class="clearfix"> <?php
                foreach ($logvar as &$value) : ?>
                    <li class="picbox_relatedgroup float_left">
                        <img class="framed_relatedgroup float_left" src="members/avatar/<?php echo $value->member->Username; ?>?xs"/>
                        <div class="userinfo">
                                <?php
                                $layoutbits->ago(strtotime($value->ts));
                                ?>
                                <br /><?php 
                                $memberlink = '<a href="members/' . $value->member->Username . '">' . $value->member->Username . '</a>';
                                $grouplink =  '<a href="groups/' . $value->relatedgroup->getPKValue() . '">' . htmlspecialchars($value->relatedgroup->Name, ENT_QUOTES) . '</a>'; 
                                $logentry = $words->get($value->RelatedGroupAction, $memberlink, $grouplink);
                                echo $logentry; ?><br />
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>  <!-- clearfix --> 
        </div><!-- subcolumns -->  
        <?php
    }
    ?>    
</fieldset>

