<?php foreach ($this->getMessages() as $message) : ?>
<p><?= $words->get($message); ?>
<?php endforeach; ?>

<div id="groups">
    <div class="subcolumns">
        <div class="c62l">
            <div class="subcl">
            
                <h3><?= $words->get('GroupDescription'); ?></h3>
                <p><?=$this->getGroupDescription() ?></p>

                <h3><?= $words->getFormatted('ForumRecentPostsLong');?></h3>
                <a class="button" href='forums/new/u<?= $this->group->id;?>'><?= $words->get('ForumGroupNewPost');?></a>
                <div class="floatbox">
                    <?= $Forums->showExternalGroupThreads($group_id); ?>
                </div>
                
                <h3><?= $words->get('GroupWiki'); ?></h3>
                <div>
                    <?= $wiki->getWiki($wikipage,false); ?>
                </div>
                
            </div> <!-- subcl -->
        </div> <!-- c62l -->
        
        <div class="c38r">
            <div class="subcr">
            
                <?php
                    if (!APP_user::isBWLoggedIn('NeedMore,Pending')) : ?>
                <h3><?= $words->get('GroupsJoinNamedGroup', $this->getGroupTitle()); ?></h3>
                <?php else : ?>
                    <?= $words->get('GroupsJoinLoginFirst'); ?>
                <h3><?= ((!$this->isGroupMember()) ? $words->get('GroupsJoinNamedGroup', $this->getGroupTitle()) : $words->get('GroupsLeaveNamedGroup', $this->getGroupTitle()) ) ?></h3>
                    <a class="bigbutton" href="groups/<?=$this->group->id ?>/<?= (($this->isGroupMember()) ? 'leave' : 'join' ); ?>"><span><?= ((!$this->isGroupMember()) ? $words->get('GroupsJoinTheGroup') : $words->get('GroupsLeaveTheGroup') ); ?></span></a>
                <?php endif; ?>
                <div class="clearfix"></div>
                <h3><?= $words->get('GroupMembers'); ?></h3>
                <div class="floatbox">
                    <?php $memberlist_widget->render() ?>
                </div>
                <strong><a href="groups/<?= $group_id.'/members'; ?>">See all members</a></strong>
                
            </div> <!-- subcr -->
        </div> <!-- c38r -->
    </div> <!-- subcolumns -->
</div> <!-- groups -->





