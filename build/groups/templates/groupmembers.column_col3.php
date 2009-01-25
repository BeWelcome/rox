
<h3>Group Members</h3>

<table>
    <tr>
      <th colspan="2">Username</td>
      <th>Comment</td>
    </tr>
<?php
foreach ($this->group->getMembers() as $member) {
    $membershipinfo = $member->getGroupMembership($this->group);
    ?>
    <tr>
        <td><?=MOD_layoutbits::linkWithPicture($member->Username) ?></td>
        <td><a href="#" class="username"><?=$member->Username ?></a></td>
        <td><?php echo $words->mTrad($membershipinfo->Comment) ?></td>
    </tr>
    <?php
}
?>
</table>
