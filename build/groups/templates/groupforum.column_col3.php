<div class="row">
<h3><?= $words->get('GroupForum'); ?></h3>
<div>
    <?php
        $showNewTopicButton = $this->canMemberAccess();

        echo $Forums->showExternalGroupThreads($group_id, $this->isGroupMember(), false, $showNewTopicButton); ?>
</div>

</div>
