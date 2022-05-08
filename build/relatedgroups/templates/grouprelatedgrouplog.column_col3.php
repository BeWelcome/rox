<div class="row">
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

        <div class="col-12 col-md-6">
            <div class="d-flex flex-row justify-content-start">
                <div class="mr-2">
                    <!-- group image -->
                    <a href="group/<?=$group_data->getPKValue() ?>">
                        <img class="framed groupimg" alt="<?=htmlspecialchars($group_data->Name, ENT_QUOTES) ?>" src="<?= ((strlen($group_data->Picture) > 0) ? "group/thumbimg/{$group_data->getPKValue()}" : 'images/icons/group.png' ) ?>" />
                    </a>
                </div>
                <div>
                    <!-- group name -->
                    <a href="group/<?=$group_data->getPKValue() ?>" class="h4"><?=htmlspecialchars($group_data->Name, ENT_QUOTES) ?></a>
                    <!-- group details -->
                    <ul class="groupul mt-1 text-truncate">
                        <li><i class="fa fa-users pr-1" title="<?php echo $words->get('GroupsMemberCount');?>"></i><?=$group_data->getMemberCount(); ?></li>
                        <li><i class="fa fa-history pr-1" title="<?= $words->get('GroupsDateCreation');?>"></i><?=date('d F Y', ServerToLocalDateTime(strtotime($group_data->created), $this->getSession())); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

    <div class="col-12">
        <?php $logvar = $this->logs;?>
    </div>
    <div class="col-12 mt-3">
        <?php
            if ($this->isGroupMember())  {
                ?>

                    <a class="btn btn-primary" role="button" href="group/<?php echo $this->group->id; ?>/selectrelatedgroup">
                        <?php echo $words->get('AddRelatedGroupButton'); ?>
                    </a>
        <?php
                    if (!empty($relatedgroups)) { ?>
                        <a class="btn btn-primary" role="button" href="group/<?php echo $this->group->id; ?>/selectdeleterelatedgroup">
                            <?php echo $words->get('RemoveRelatedGroupButton'); ?>
                        </a><?php
                    } ?>
               <?php
        } ?>
    </div>
    <?php
    if ($this->isGroupAdmin) {
        ?>
        <div class="col-12 mt-3">
            <h5><?php echo $words->get('NbOfLogEntries', count($logvar)); ?></h5>
        </div>
            <?php
                foreach ($logvar as &$value) : ?>

                    <div class="col-12 col-md-6">
                        <div class="d-flex flex-row justify-content-start">
                            <div class="mr-2">
                                <a href="members/<?php echo $value->member->Username; ?>">
                                    <img class="profileimg" src="members/avatar/<?php echo $value->member->Username; ?>?48" width="48" height="48"/>
                                </a>
                            </div>
                            <div>
                                <?php
                                $layoutbits->ago(strtotime($value->ts));
                                ?>
                                <?php
                                $memberlink = '<a href="members/' . $value->member->Username . '">' . $value->member->Username . '</a>';
                                $grouplink =  '<a href="group/' . $value->relatedgroup->getPKValue() . '">' . htmlspecialchars($value->relatedgroup->Name, ENT_QUOTES) . '</a>';
                                $logentry = $words->get($value->RelatedGroupAction, $memberlink, $grouplink);
                                echo $logentry;
                                echo "<br>" . $layoutbits->ago(strtotime($value->ts)); ?>
                            </div>
                        </div>
                    </div>


                <?php endforeach; ?>

        <?php
    }
    ?>
</div>
