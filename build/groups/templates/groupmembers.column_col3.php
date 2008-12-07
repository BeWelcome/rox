<h3><?php echo $words->get('GroupDescription'); ?></h3>
        <?=$this->getGroupDescription() ?>
<br />

<h3>Group Members</h3>

<div>
<?php
foreach ($this->group->getMembers() as $member) {
    ?><div style="margin:2px; border:1px solid #eee; padding:2px;">
    <div style="float:left; padding: 4px">
    <?=MOD_layoutbits::linkWithPicture($member->Username) ?>
    </div>
    <div style="margin-left:80px">
    <strong><?=$member->Username ?></strong><br />
    <?php echo $member->Comment ?> - WHERE IS THIS NUMBER?  AND WHY IS IT ACTUALLY A STRING IN THE DATABASE?
    </div>
    <div style="clear:both; margin:2px"></div>
    </div>
    <?php
}
?>
</div>
