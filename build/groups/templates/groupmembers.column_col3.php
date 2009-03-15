<div id="groups">
    <h3><?= $words->get('GroupsMembers'); ?></h3>

    <table>
        <tr>
          <th colspan="2"><?= $words->get('Username'); ?></th>
          <th><?= $words->get('GroupsMemberComment'); ?></th>
        </tr>
    <?php
    foreach ($this->group->getMembers() as $member) {
        $membershipinfo = $member->getGroupMembership($this->group);
        ?>
        <tr>
            <td><?php echo MOD_layoutbits::PIC_50_50($member->Username,'',$style='framed'); ?></td>
            <td>
                <a href="people/<?=$member->Username ?>" class="username"><?=$member->Username ?></a>
                <ul>
                    <li><span class="small"><?= $member->age; ?> </span></li>
                    <li><span class="small"><?= $member->cityname; ?></span></li>
                </ul>
            </td>
            <td><em><?php echo $words->mTrad($membershipinfo->Comment) ?></em></td>
        </tr>
        <?php
    }
    ?>
    </table>
</div>
