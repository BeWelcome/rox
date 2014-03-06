<?php foreach ($this->getMessages() as $message) : ?>
<p><?= $words->get($message); ?>
<?php endforeach; ?>

<div id="groups">
    <div class="subcolumns">
        <div class="c62l">
            <div class="subcl">
                <div class="bw-row clearfix">
                    <?= ((strlen($this->group->Picture) > 0) ? "<img class=\"float_left framed\" src='groups/realimg/{$this->group->getPKValue()}' width=\"100px\" alt='Image for the group {$this->group->Name}' />" : ''); ?>
                    <h3><?= $words->get('GroupDescription'); ?></h3>
                    <p><?=$this->group->getDescription() ?></p>
                </div> <!--row clearfix -->

                <h3><?= $words->getFormatted('ForumRecentPostsLong');?></h3>
                <div class="clearfix">
                    <?= $Forums->showExternalGroupThreads($group_id); ?>
                </div> <!-- clearfix -->
                
            </div> <!-- subcl -->
        </div> <!-- c62l -->
        
        <div class="c38r">
            <div class="subcr">
            
                <?php
                    if (!APP_user::isBWLoggedIn('NeedMore,Pending')) : ?>
                <h3><?= $words->get('GroupsJoinNamedGroup', $this->getGroupTitle()); ?></h3>
                    <?= $words->get('GroupsJoinLoginFirst'); ?>
                <?php else : ?>
                <h3><?= ((!$this->isGroupMember()) ? $words->get('GroupsJoinNamedGroup', $this->getGroupTitle()) : $words->get('GroupsLeaveNamedGroup', $this->getGroupTitle()) ) ?></h3>
                <div class="bw-row clearfix">
                    <a class="bigbutton" href="groups/<?=$this->group->id ?>/<?= (($this->isGroupMember()) ? 'leave' : 'join' ); ?>"><span><?= ((!$this->isGroupMember()) ? $words->get('GroupsJoinTheGroup') : $words->get('GroupsLeaveTheGroup') ); ?></span></a>
                <?php endif; ?>
                </div>
                <h3><?= $words->get('GroupOwner'); ?></h3>
                <div class="bw-row">
                    <p><?= (($member =$this->group->getGroupOwner()) ? $member->Username : 'Group has no owner'); ?></p>
                </div>
                <h3><?= $words->get('GroupMembers'); ?></h3>
                <div class="clearfix">
                    <?php $memberlist_widget->render() ?>
                </div>
                <strong><a href="groups/<?= $group_id.'/members'; ?>"><?= $words->get('GroupSeeAllMembers'); ?></a></strong>
                
            </div> <!-- subcr -->
        </div> <!-- c38r -->
    </div> <!-- subcolumns -->
</div> <!-- groups -->





