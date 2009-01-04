
<h3>Group Members</h3>

<table>
    <tr>
      <th colspan="2">Username</td>
      <th>Comment</td>
    </tr>
<?php
foreach ($this->group->getMembers() as $member) {
    ?>
    <tr>
        <td><?=MOD_layoutbits::linkWithPicture($member->Username) ?></td>
        <td><a href="#" class="username"><?=$member->Username ?></a></td>
        <td><?php echo $member->Comment ?></td>
    </tr>
    <?php
}
?>
</table>
