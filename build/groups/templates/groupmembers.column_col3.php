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

    <table>
        <tr>
          <th colspan="2"><?= $words->get('Username'); ?></th>
          <th><?= $words->get('GroupsMemberComment'); ?></th>
        </tr>
    <?php
    $count = 0;
    foreach ($this->group->getMembers() as $member) {
        $membershipinfo = $member->getGroupMembership($this->group);
        ?>
        <tr class="<?php echo $background = (($count % 2) ? 'highlight' : 'blank'); ?>">
            <td><?=MOD_layoutbits::PIC_50_50($member->Username) ?></td>
            <td>
                <a href="people/<?=$member->Username ?>" class="username"><?=$member->Username ?></a>
                <ul>
                    <li><span class="small"><?= $member->get_city(); ?></span></li>
                    <li><span class="small"><?= $member->get_country(); ?></span></li>
                </ul>
            </td>
            <td><em><?php echo $words->mTrad($membershipinfo->Comment) ?></em></td>
        </tr>
        <?php $count++;
    }
    ?>
    </table>
    </div> <!-- subcolums -->
</div>
<?php
    }
?>
