<?php foreach ($this->getMessages() as $message) : ?>
<p><?= $words->get($message); ?>
    <?php endforeach; ?>
    <?php
    $group_name_html = htmlspecialchars($this->getGroupTitle(), ENT_QUOTES);
    $purifierModule = new MOD_htmlpure();
    $purifier = $purifierModule->getBasicHtmlPurifier();
    $uri = $_SERVER['REQUEST_URI'];
    ?>

    <div class="col-12 col-md-8">

        <div class="w-100">
            <?= ((strlen($this->group->Picture) > 0) ? "<img class=\"float-left framed mr-2 mb-2\" src='groups/realimg/{$this->group->getPKValue()}' width=\"100px\" alt='Image for the group {$group_name_html}' />" : ''); ?>
            <h4><?php echo $words->get('GroupDescription'); ?></h4>
            <?php echo $purifier->purify(nl2br($this->group->getDescription())) ?>
        </div>

        <div class="pt-3"><h3 class="float-left m-0 mb-2"><?php echo $words->getFormatted('ForumRecentPostsLong'); ?></h3><a
            href="<? echo $uri; ?>/forum/new"
            class="btn btn-primary float-right"><?php echo $this->words->getBuffered('ForumNewTopic'); ?></a></div>

        <div class="w-100 pt-5">
            <?php
            if (!$this->isGroupMember() && $this->group->latestPost) {
                echo '<div class="small">' . $words->get('GroupInfoLastActivity', date('Y-m-d H:i', $this->group->latestPost)) . '</div>';
            }

            $showNewTopicButton = false;
            if ($this->isGroupMember()) {
                $showNewTopicButton = true;
            }
            /* only relevant if Suggestion Feature ever comes back
            $suggestionsGroupId = PVars::getObj('suggestions')->groupid;
            if ($group_id == $suggestionsGroupId) {
                $showNewTopicButton = false;
            }
            */
            echo $Forums->showExternalGroupThreads($group_id, $this->isGroupMember(), false, $showNewTopicButton); ?>
        </div>

<div class="pt-3 row">
    <?php
    $relatedgroups = $this->group->findRelatedGroups($group_id);
    if (!empty($relatedgroups)) { ?>
        <h3 class="col-12"><?php echo $words->getFormatted('RelatedGroupsTitle'); ?></h3>
    <?php } ?>


    <?php
    foreach ($relatedgroups as $group_data) :
    if (strlen($group_data->Picture) > 0) {
        $img_link = "groups/thumbimg/{$group_data->getPKValue()}";
    } else {
        $img_link = "images/icons/group.png";
    } ?>


    <div class="col-12 col-md-6 p-2">
        <div class="float-left h-100 mr-2" style="width: 80px;">
            <!-- group image -->
            <a href="groups/<?php echo $group_data->getPKValue() ?>">
                <img class="groupimg framed" alt="Group" src="<?php echo $img_link; ?>"/>
            </a>
        </div>
        <div>
            <!-- group name -->
            <h4>
                <a href="groups/<?= $group_data->getPKValue() ?>"><?php echo htmlspecialchars($group_data->Name, ENT_QUOTES) ?></a>
            </h4>
            <!-- group details -->
            <ul class="groupul mt-1">
                <li><i class="fa fa-group"
                       title="<? echo $words->get('GroupsMemberCount'); ?>"></i> <?= $group_data->getMemberCount(); ?></li>
                <li><? echo $words->get('GroupsNewMembers'); ?> <?php echo count($group_data->getNewMembers()); ?></li>
                <li><?php
                    if ($group_data->latestPost) {
                        $interval = date_diff(date_create(date('d F Y')), date_create(date('d F Y', ServerToLocalDateTime($group_data->latestPost, $this->getSession()))));
                        echo $words->get('GroupsLastPost') . ": " . $interval->format('%a') . " " . $words->get('days_ago');

                    } else {
                        echo $words->get('GroupsNoPostYet');
                    }
                    ?></li>
            </ul>
        </div>
    </div>


    <?php endforeach; ?>
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
                    <a class="btn btn-primary btn-block mb-3" href="groups/<?= $this->group->id ?>/join">
                        <?= $words->getSilent('GroupsJoinTheGroup'); ?>
                    </a>
                    <?php echo $words->flushBuffer(); ?>
                <?php }
        }
    } // endif logged in member
    ?>

    <h3><?= $words->get('GroupMembers'); ?></h3>

    <?php $memberlist_widget->render() ?>

    <?php
    if ($memberCount != $visibleMemberCount) {
        $login_url = 'login/groups/' . $this->group->id;
        $loginstr = '<a href="' . $login_url . '#login-widget" alt="login" id="header-login-link">' . $words->getBuffered('GroupsMoreMemberLogin') . '</a>';
        echo $words->get("GroupMoreMembers", $memberCount - $visibleMemberCount, $loginstr);
    } else { ?>
        <a href="groups/<?= $group_id . '/members'; ?>"
           class="btn btn-block btn-primary"><?= $words->get('GroupSeeAllMembers'); ?></a>
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

            <a class="btn btn-block btn-primary mt-3" href="groups/<?= $this->group->id ?>/leave">
                <?= $words->getSilent('GroupsLeaveTheGroup'); ?>
            </a>

            <?php echo $words->flushBuffer();
            ?>
        <?php }
    } ?>
</div>
