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
            <th style="width:20%" colspan="2"><?= $words->get('Username'); ?></th>
            <th style="width:29%;"><?= $words->get('GroupsMemberComment'); ?></th>
            <th style="width:20%" colspan="2"><?= $words->get('Username'); ?></th>
            <th style="width:29%" style="width:100%;"><?= $words->get('GroupsMemberComment'); ?></th>
        </tr>
    <?php
        $purifier = MOD_htmlpure::getBasicHtmlPurifier();
        $count = 0;
        foreach ($this->pager_widget->getActiveSubset($this->group->getMembers('In', $this->pager_widget->getActiveStart(), $this->pager_widget->getActiveLength())) as $member)
        {
            $membershipinfo = $member->getGroupMembership($this->group);
            if ($count % 2 == 0) {
            ?>
            <tr class="<?php echo $background = (($count % 4) ? 'highlight' : 'blank'); ?>">
            <?php } ?>
                <td style="width:10%"><?=MOD_layoutbits::PIC_50_50($member->Username) ?></td>
                <td style="width:10%">
                    <a href="members/<?=$member->Username ?>" class="username"><?=$member->Username ?></a>
                    <ul>
                        <li><span class="small"><?= $words->get('Age') ?>: <?= $member->age; ?> </span></li>
                        <li><span class="small"><?= $member->get_city(); ?></span></li>
                    </ul>
                </td>
                <td style="width:29%"><em><?php echo $purifier->purify($words->mTrad($membershipinfo->Comment,true)) ?></em></td>
            <?php
                $count++;
                if ($count % 2 == 0) { ?>
                    </tr>
                    <?php
                }
        }
        if ($count % 2 != 0) {
            echo "<td></td><td></td><td></td><tr>";
        }
    ?>
    </table>
    <?php 
        $this->pager_widget->render();
    }
    ?>
</div>
</div>