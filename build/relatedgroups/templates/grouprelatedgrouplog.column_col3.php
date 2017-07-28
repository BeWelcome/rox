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
    </div>
    <div class="col-12">
        <?php
            if ($this->isGroupMember())  {
                ?>

                    <a class="btn btn-primary" role="button" href="groups/<? echo $this->group->id; ?>/selectrelatedgroup">
                        <?php echo $words->get('AddRelatedGroupButton'); ?>
                    </a>
        <?php
                    if (!empty($relatedgroups)) { ?>
                        <a class="btn btn-primary" role="button" href="groups/<? echo $this->group->id; ?>/selectdeleterelatedgroup">
                            <? echo $words->get('RemoveRelatedGroupButton'); ?>
                        </a><?php
                    } ?>
               <?php
        } ?>
    </div>
    <?php
    if ($this->isGroupAdmin) {
        ?>        
        <div class="col-12 mt-3">
            <h4><?php echo $words->get('NbOfLogEntries', count($logvar)); ?></h4>
        </div>
            <?php
                foreach ($logvar as &$value) : ?>

                    <div class="col-12 col-md-6 p-2">
                        <div class="float-left h-100 mr-2" style="width: 50px;">
                            <a href="members/<?php echo $value->member->Username; ?>">
                                <img class="framed float-left" src="members/avatar/<?php echo $value->member->Username; ?>?50"/>
                            </a>
                        </div>
                        <div class="p-2">
                            <?php
                            $layoutbits->ago(strtotime($value->ts));
                            ?>
                            <?php
                            $memberlink = '<a href="members/' . $value->member->Username . '">' . $value->member->Username . '</a>';
                            $grouplink =  '<a href="groups/' . $value->relatedgroup->getPKValue() . '">' . htmlspecialchars($value->relatedgroup->Name, ENT_QUOTES) . '</a>';
                            $logentry = $words->get($value->RelatedGroupAction, $memberlink, $grouplink);
                            echo $logentry;
                            echo "<br>" . $layoutbits->ago(strtotime($value->ts)); ?>
                        </div>
                    </div>


                <?php endforeach; ?>

        <?php
    }
    ?>
