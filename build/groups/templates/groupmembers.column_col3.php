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
            <td><?=MOD_layoutbits::linkWithPicture($member->Username) ?></td>
            <td>
                <a href="people/<?=$member->Username ?>" class="username"><?=$member->Username ?></a>
                <ul>
                    <li><span class="small"><?= $member->age; ?> </span></li>
                    <li><span class="small"><?= $member->CityName; ?></span></li>
                </ul>
            </td>
            <td><em><?php echo $words->mTrad($membershipinfo->Comment) ?></em></td>
        </tr>
        <?php
    }
    ?>
    </table>
</div>
