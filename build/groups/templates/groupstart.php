<div class="row"><?php foreach ($this->getMessages() as $message) : ?>
<p><?= $words->get($message); ?>
    <?php endforeach; ?>
    <?php
    $group_name_html = htmlspecialchars($this->getGroupTitle(), ENT_QUOTES);
    $purifierModule = new MOD_htmlpure();
    $purifier = $purifierModule->getBasicHtmlPurifier();
    $uri = $_SERVER['REQUEST_URI'];
    ?>

    <div class="col-12 col-md-8">

        <div class="media">
            <?= ((strlen($this->group->Picture) > 0) ? "<img class=\"float-left framed mr-2 mb-2\" src='group/realimg/{$this->group->getPKValue()}' width=\"100px\" alt='Image for the group {$group_name_html}' />" : ''); ?>
            <div class="media-body">
                <h4><?php echo $words->get('GroupDescription'); ?></h4>
                <?php echo $purifier->purify(nl2br($this->group->getDescription())) ?>
            </div>
        </div>

        <div class="pt-3"><h3 class="float-left m-0 mb-2"><?php echo $words->getFormatted('ForumRecentPostsLong'); ?></h3>
        <?php if ($this->isGroupMember()) { ?>
            <a
            href="<? echo $uri; ?>/forum/new"
            class="btn btn-primary float-right"><?php echo $this->words->getBuffered('ForumNewTopic'); ?></a>
        <?php } ?>

        </div>

        <div class="w-100 pt-5">
            <?php
            if (!$this->isGroupMember() && $this->group->latestPost) {
                echo '<div class="small">' . $words->get('GroupInfoLastActivity', date('Y-m-d H:i', $this->group->latestPost)) . '</div>';
            }

            $showNewTopicButton = false;
            if ($this->isGroupMember()) {
                $showNewTopicButton = true;
            }

            echo $Forums->showExternalGroupThreads($group_id, $this->isGroupMember(), false, $showNewTopicButton); ?>
        </div>

</div>


<div class="col-12 col-md-4">

    <?php
    $a = new APP_User();
    if (!$a->isBWLoggedIn('NeedMore,Pending')) {
        // not logged in users cannot join groups
        echo $words->get('GroupsJoinLoginFirst');
    } else {
        $model = new GroupsModel();
        if ($this->member) {
            $memberId = $this->member->id;
        } else {
            $memberId = null;
        }
        switch ($model->getMembershipStatus($this->group, $memberId)) {
            case 'Kicked' :
                // tell user he got banned
                echo $words->getSilent('GroupsBanned');
                break;
            case 'WantToBeIn' :
                // tell user he already applied but still needs to wait confirmation
                echo $words->getSilent('GroupsAlreadyApplied');
                break;
            default:
                if (!$this->isGroupMember() and $this->group->Type == 'NeedAcceptance') {
                    // tell user that application will be moderated
                    echo $words->getSilent('GroupsJoinNeedAccept');
                }
                if (!$this->isGroupMember()) { ?>
                    <a class="btn btn-outline-primary btn-block mb-3" href="group/<?= $this->group->id ?>/join">
                        <?= $words->getSilent('GroupsJoinTheGroup'); ?>
                    </a>
                    <?php echo $words->flushBuffer(); ?>
                <?php }
        }
    } // endif logged in member
    ?>

    <div class="h3"><?= $words->get('GroupMembers'); ?></div>

    <div class="row justify-content-between px-3">
    <?php $memberlist_widget->render() ?>
    </div>

    <?php
    if ($memberCount != $visibleMemberCount) {
        $login_url = 'login/group/' . $this->group->id;
        $loginstr = '<a href="' . $login_url . '#login-widget" alt="login" id="header-login-link">' . $words->getBuffered('GroupsMoreMemberLogin') . '</a>';
        echo $words->get("GroupMoreMembers", $memberCount - $visibleMemberCount, $loginstr);
    } else { ?>
        <a href="group/<?= $group_id . '/members'; ?>"
           class="btn btn-block btn-outline-primary"><?= $words->get('GroupSeeAllMembers'); ?></a>
    <?php } ?>

    <?php
    // Hide the admin list if no user is logged in (which means that visible lis
    if ($memberCount == $visibleMemberCount) {

        ?>

        <h4 class="mt-3"><?php echo $words->get('GroupAdmins'); ?></h4>

        <?php $admins = $this->group->getGroupOwners();
        if (isset($admins) && !empty($admins)) {
            foreach ($admins as $admin) {
                echo '<div class="w-100 mb-1">';
                echo MOD_layoutbits::PIC_50_50($admin->Username);
                echo '<a href="members/' . $admin->Username . '" class="small"> ' . $admin->Username . '</a>';
                echo '</div>';
            }
        } else {
            echo $words->get('GroupNoAdmin');
        } ?>

        <?php
        if ($this->isGroupMember()) { ?>

            <a class="btn btn-block btn-danger mt-3" href="group/<?= $this->group->id ?>/leave">
                <?= $words->getSilent('GroupsLeaveTheGroup'); ?>
            </a>

            <?php echo $words->flushBuffer();
            ?>
        <?php }
    } ?>
</div>

</div>
<div class="pt-3 row">

    <?php
    $relatedgroups = $this->group->findRelatedGroups($group_id); ?>
    <div class="col-12 col-md-8 h3"><?php echo $words->getFormatted('RelatedGroupsTitle'); ?></div>
    <? if ($this->isGroupMember()) { ?>
        <div class="col-12 col-md-4 float-md-right">
            <a href="group/<? echo $this->group->id; ?>/selectrelatedgroup" class="btn btn-block btn-outline-primary"><?= $words->getFormatted('AddRelatedGroupButton'); ?></a>
        </div>
    <? } else {
        echo '<div class="col-12 col-md-4"></div>';
    }
    foreach ($relatedgroups as $group_data) :

        include('groupsdisplay.php');

    endforeach; ?>
</div>
