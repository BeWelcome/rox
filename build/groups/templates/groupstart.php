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
                    <h3><?php echo $words->get('GroupDescription'); ?></h3>
                    <p><?php echo $purifier->purify(nl2br($this->group->getDescription())) ?></p>
                </div> <!--row clearfix -->

                <div class="pt-2"><h3 class="float-left m-0 mb-2"><?php echo $words->getFormatted('ForumRecentPostsLong');?></h3><a href="<? echo $uri; ?>/new" class="btn btn-primary float-right"><?php echo $this->words->getBuffered('ForumNewTopic'); ?></a></div>
                <div class="pt-5 w-100">
                    <?php
                        if (!$this->isGroupMember() && $this->group->latestPost) {
                            echo '<div class="small">' . $words->get('GroupInfoLastActivity', date('Y-m-d H:i', $this->group->latestPost)) . '</div>';
                        }

                        $showNewTopicButton = false;
                        if ($this->isGroupMember()) {
                            $showNewTopicButton = true;
                        }
                        $suggestionsGroupId = PVars::getObj('suggestions')->groupid;
                        if ($group_id == $suggestionsGroupId) {
                            $showNewTopicButton = false;
                        }
                    echo $Forums->showExternalGroupThreads($group_id, $this->isGroupMember(), false, $showNewTopicButton); ?>
                </div>

                <div>
                    <?php
                    $relatedgroups = $this->group->findRelatedGroups($group_id);
                    if (!empty($relatedgroups)) { ?>
                        <h3><?php echo $words->getFormatted('RelatedGroupsTitle');?></h3>
                    <?php } ?>
                    <ul>
                        <?php
                        foreach ($relatedgroups as $group_data) :
                            if (strlen($group_data->Picture) > 0) {
                                $img_link = "groups/thumbimg/{$group_data->getPKValue()}";
                            } else {
                                $img_link = "images/icons/group.png";
                            } ?>
                            <li class="picbox_relatedgroup float_left">
                                <a href="groups/<?php echo $group_data->getPKValue() ?>">
                                    <img class="framed_relatedgroup float_left" alt="Group" src="<?php echo $img_link; ?>"/>
                                </a>
                                <div class="userinfo"><a href="groups/<?php echo $group_data->getPKValue() ?>"><?php echo htmlspecialchars($group_data->Name, ENT_QUOTES) ?></a><br />
                                    <?php echo $words->get('GroupsMemberCount');?>: <?php echo $group_data->getMemberCount(); ?><br />
                                    <?php echo $words->get('GroupsNewMembers');?>: <?php echo count($group_data->getNewMembers()) ; ?><br />
                                    </span></div> <!-- userinfo -->
                            </li> <!-- picbox_relatedgroup -->

                        <?php endforeach; ?>
                    </ul>
                    <?php
                    if (($this->group->VisibleComments == 'yes') && ($memberCount == $visibleMemberCount)){
                        $shouts = new ShoutsController();
                        $shouts->shoutsList('groups',$group_id);
                    }
                    ?>
                </div>

            </div>

        
        <div class="col-12 col-md-4">
            <div>
            
            <?php
                $a = new APP_User();
                if (!$a->isBWLoggedIn('NeedMore,Pending')) {
                    // not logged in users cannot join groups
                    echo $words->get('GroupsJoinLoginFirst');
                } else {
                    $model = new GroupsModel();
                    if ($this->member){
                        $memberId = $this->member->id;
                    } else {
                        $memberId = null;
                    }
                    switch ($model->getMembershipStatus($this->group,$memberId)){
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
                        <div class="bw-row clearfix">
                            <a class="button" href="groups/<?=$this->group->id ?>/join">
                                <span>
                                    <?= $words->getSilent('GroupsJoinTheGroup'); ?>
                                </span>
                            </a>
                            <?php echo $words->flushBuffer(); ?>
                        </div>
                        <?php }
                    }
                } // endif logged in member
                ?>
                
                <h3><?= $words->get('GroupMembers'); ?></h3>
                <div class="row">
                    <div class="col-12">
                        <?php $memberlist_widget->render() ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                    <?php
                    if ($memberCount != $visibleMemberCount) {
                        $login_url = 'login/groups/' . $this->group->id;
                        $loginstr = '<a href="' . $login_url . '#login-widget" alt="login" id="header-login-link">' . $words->getBuffered('GroupsMoreMemberLogin') . '</a>';
                        echo $words->get("GroupMoreMembers", $memberCount - $visibleMemberCount, $loginstr);
                    } else { ?>
                    <a href="groups/<?= $group_id.'/members'; ?>" class="btn btn-block btn-primary"><?= $words->get('GroupSeeAllMembers'); ?></a>
                    <?php } ?>
                    </div>
                </div>
                <?php
                    // Hide the admin list if no user is logged in (which means that visible lis
                    if ($memberCount == $visibleMemberCount) {

                ?>

                <h4 class="mt-3"><?php echo $words->get('GroupAdmins'); ?></h4>
                <div class="row">
                    <div class="col-12">
                        <?php $admins = $this->group->getGroupOwners();
                        if (isset($admins) && !empty($admins))
                        {
                            foreach ($admins as $admin){
                            
                                echo '<div class="groupmembers center float-left">';
                                echo MOD_layoutbits::PIC_50_50($admin->Username);
                                echo '<a href="members/' . $admin->Username .'">' . " " . $admin->Username .'</a></div>';
                            }
                        } else {
                            echo $words->get('GroupNoAdmin');
                        } ?>
                    </div>
                </div>
                <?php
                if ($this->isGroupMember()) { ?>
                <div class="row mt-3">
                    <div class="col-12">
                    <a class="btn btn-block btn-primary" href="groups/<?=$this->group->id ?>/leave">
                        <?= $words->getSilent('GroupsLeaveTheGroup'); ?>
                    </a>
                    </div></div><?php echo $words->flushBuffer();
                ?>
                <?php }
                } ?>
            </div>
        </div>

