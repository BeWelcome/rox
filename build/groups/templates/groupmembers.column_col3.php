
<h3>Group Members</h3>

<table>
    <tr>
      <th colspan="2">Username</th>
      <th>Comment</th>
    </tr>
<?php
foreach ($this->group->getMembers() as $member) {
    $membershipinfo = $member->getGroupMembership($this->group);
    ?>
    <tr>
        <td><?=MOD_layoutbits::linkWithPicture($member->Username) ?></td>
        <td><a href="#" class="username"><?=$member->Username ?></a></td>
        <td><q><?php echo $words->mTrad($membershipinfo->Comment) ?></q></td>
    </tr>
    <?php
}
?>
</table>
