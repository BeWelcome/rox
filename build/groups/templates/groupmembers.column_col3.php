<?php
    if (!$this->isGroupMember() && $this->group->Type == 'NeedInvitation')
    {
        echo "not public";
    }
    else
    {
?>
<div id="groups">
    <div class="subcolumns">
    <h3><?= $words->get('GroupsMembers'); ?></h3>

    <?php $this->pager_widget->render(); ?>
    <table>
        <tr>
          <th colspan="2"><?= $words->get('Username'); ?></th>
          <th style="width:100%;"><?= $words->get('GroupsMemberComment'); ?></th>
        </tr>
    <?php
        $purifier = MOD_htmlpure::getBasicHtmlPurifier();
        $count = 0;
        foreach ($this->pager_widget->getActiveSubset($this->group->getMembers('In', $this->pager_widget->getActiveStart(), $this->pager_widget->getActiveLength())) as $member)
        {
            $membershipinfo = $member->getGroupMembership($this->group);
            ?>
            <tr class="<?php echo $background = (($count % 2) ? 'highlight' : 'blank'); ?>">
                <td><?=MOD_layoutbits::PIC_50_50($member->Username) ?></td>
                <td>
                    <a href="members/<?=$member->Username ?>" class="username"><?=$member->Username ?></a>
                    <ul>
                        <li><span class="small"><?= $member->age; ?> </span></li>
                        <li><span class="small"><?= $member->cityname; ?></span></li>
                    </ul>
                </td>
                <td><em><?php echo $purifier->purify($words->mTrad($membershipinfo->Comment,true)) ?></em></td>
            </tr>
            <?php
            $count++;
        }
    ?>
    </table>
    <?php 
        $this->pager_widget->render();
    }
    ?>
</div>
</div>