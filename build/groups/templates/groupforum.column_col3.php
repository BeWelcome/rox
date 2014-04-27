<h3><?= $words->get('GroupForum'); ?></h3>
<div>
    <?php
        $showNewTopicButton = false;
        if ($this->isGroupMember()) {
            $showNewTopicButton = true;
        }
        echo $Forums->showExternalGroupThreads($group_id, false, $showNewTopicButton); ?>
</div>
