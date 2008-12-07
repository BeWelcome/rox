<?php foreach ($this->getMessages() as $message) : ?>
<p><?= $words->get($message); ?>
<?php endforeach; ?>
<h3><?= $words->get('GroupDescription'); ?></h3>
<p><?=$this->getGroupDescription() ?></p>
<h3><?= $words->get('GroupMembers'); ?></h3>
<div>
<?php $memberlist_widget->render() ?>
</div>
<?php
    if (!APP_user::isBWLoggedIn('NeedMore,Pending')) : ?>
<h3><?= $words->get('GroupsJoinNamedGroup', $this->getGroupTitle()); ?></h3>
    <?= $words->get('GroupsJoinLoginFirst'); ?>
<?php else : ?>

<h3><?= ((!$this->isGroupMember()) ? $words->get('GroupsJoinNamedGroup', $this->getGroupTitle()) : $words->get('GroupsLeaveNamedGroup', $this->getGroupTitle()) ) ?></h3>
        <span class="button"><a href="groups/<?=$this->group->id ?>/<?= (($this->isGroupMember()) ? 'leave' : 'join' ); ?>"><?= ((!$this->isGroupMember()) ? $words->get('GroupsJoinTheGroup') : $words->get('GroupsLeaveTheGroup') ); ?></a></span>
<?php endif; ?>
<h3><?= $words->getFormatted('ForumRecentPostsLong');?></a></h3>
<a href='forums/new/u<?= $this->group->id;?>'><?= $words->get('ForumGroupNewPost');?></a>
    <div class="floatbox">
    <?= $Forums->showExternalGroupThreads($group_id); ?>
    </div>
<h3><?= $words->get('GroupWiki'); ?></h3>
<div><?= $wiki->getWiki($wikipage,false); ?></div>
