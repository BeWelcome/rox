<h3>Group Description</h3>
        <?=$this->getGroupDescription() ?><br />
<?php
/* ?><div><pre><?php print_r($this->getGroup()->getData()); ?></pre></div><?php */
?>
<h3>Group Members</h3>
<div><?php
foreach ($this->getGroup()->getMembers() as $member) {
    ?><div style="margin:2px; border:1px solid #eee; padding:2px;">
    <div style="float:left; padding: 4px">
    <?=MOD_layoutbits::linkWithPicture($member->Username) ?>
    </div>
    <div style="margin-left:80px">
    <strong><?=$member->Username ?></strong><br />
    I joined this group because...
    </div>
    <div style="clear:both; margin:2px"></div>
    </div>
    <?php
}
?>
</div>
