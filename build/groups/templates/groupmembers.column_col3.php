<?php
    if (!$this->isGroupMember() && $this->group->Type == 'NeedInvitation')
    {
        echo "not public";
    }
    else
    {
?>
    <h3><?= $words->get('GroupsMembers'); ?></h3>

    <?php $this->pager_widget->render(); ?>
<div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="blank">
            <tr>
                <th colspan="2">
                    <?= $words->get('Username'); ?>
                </th>
                <th>
                    <?= $words->get('GroupsMemberComment'); ?>
                </th>
            </tr>
            </thead>
            <tbody>

                <?php
                $purifier = MOD_htmlpure::getBasicHtmlPurifier();
                $count = 0;
                foreach ($this->pager_widget->getActiveSubset($this->group->getMembers('In', $this->pager_widget->getActiveStart(), $this->pager_widget->getActiveLength())) as $member){
                    $membershipinfo = $member->getGroupMembership($this->group);
                ?>
                    <tr>
                        <td>
                            <?=MOD_layoutbits::PIC_50_50($member->Username) ?>
                        </td>
                        <td>
                            <a href="members/<?=$member->Username ?>" class="username"><?=$member->Username ?></a>
                               <p class="small m-0"><?= $words->get('Age') ?>: <?= $member->age; ?> </p>
                               <p class="small m-0"><?= $member->get_city(); ?>, <?= $member->get_country(); ?></p>
                        </td>
                        <td>
                            <em><?php echo $purifier->purify($words->mTrad($membershipinfo->Comment,true)) ?></em>
                        </td>
                    <?php
                }
                ?>
            </tbody>
        </table>
</div>
    <?php 
        $this->pager_widget->render();
    }
    ?>
