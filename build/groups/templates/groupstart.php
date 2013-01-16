<?php foreach ($this->getMessages() as $message) { ?>
<p><?= $words->get($message); ?>
<?php } ?>
<?php
$group_name_html = htmlspecialchars($this->getGroupTitle(), ENT_QUOTES); 
$purifier = MOD_htmlpure::getBasicHtmlPurifier();
?>

<div id="groups">
    <div class="subcolumns">
        <div class="c62l">
            <div class="subcl">
                <div class="row floatbox">
                    <?= ((strlen($this->group->Picture) > 0) ? "<img class=\"float_left framed\" src='groups/realimg/{$this->group->getPKValue()}' width=\"100px\" alt='Image for the group {$group_name_html}' />" : ''); ?>
                    <h3><?php echo $words->get('GroupDescription'); ?></h3>
                    <p><?php echo $purifier->purify(nl2br($this->group->getDescription())) ?></p>
                </div> <!--row floatbox -->

                <h3><?php echo $words->getFormatted('ForumRecentPostsLong');?></h3>
                <div class="row floatbox">
                    <?php echo $Forums->showExternalGroupThreads($group_id); ?>
                </div> <!-- floatbox -->
            </div> <!-- subcl -->
        </div> <!-- c62l -->
        
        <div class="c38r">
            <div class="subcr"><br />
            
                <?php
                    if (!APP_user::isBWLoggedIn('NeedMore,Pending')) : ?>
                    <?= $words->get('GroupsJoinLoginFirst'); ?>
                <?php else : ?>
                <div class="row clearfix">
                    <a class="bigbutton" href="groups/<?=$this->group->id ?>/<?= (($this->isGroupMember()) ? 'leave' : 'join' ); ?>"><span><?= ((!$this->isGroupMember()) ? $words->get('GroupsJoinTheGroup') : $words->get('GroupsLeaveTheGroup') ); ?></span></a>
                </div><br />
                <?php endif; ?>
                <h3><?= $words->get('GroupMembers'); ?></h3>
                <div class="floatbox">
                    <?php $memberlist_widget->render() ?>
                </div>
                <strong><a href="groups/<?= $group_id.'/members'; ?>"><?= $words->get('GroupSeeAllMembers'); ?></a></strong>
                <br><br>
                <h4><?php echo $words->get('GroupAdmins'); ?></h4>
                <div class="floatbox">
                        <?php $admins = $this->group->getGroupOwners();
                        if (isset($admins) && !empty($admins))
                        {
                            foreach ($admins as $admin){
                            
                                echo '<div class="groupmembers center float_left">';
                                echo MOD_layoutbits::PIC_40_40($admin->Username);
                                echo '<div><a href="members/' . $admin->Username .'">' . " " . $admin->Username .'</a></div></div>';
                            }
                        } else {
                            echo $words->get('GroupNoAdmin');
                        } ?>
                </div>
            </div> <!-- subcr -->
        </div> <!-- c38r -->
    </div> <!-- subcolumns -->
    <div class="subcolumns">
    <?php
    $subgroups = $this->group->findSubgroups($group_id);
    if (!empty($subgroups)) { ?>
        <h3><?php echo $words->getFormatted('SubgroupsTitle');?></h3>
    <?php } ?>
    <ul class="floatbox">
        <?php 
        foreach ($subgroups as $group_data) { 
            if (strlen($group_data->Picture) > 0) {
                $img_link = "groups/thumbimg/{$group_data->getPKValue()}";
            } else {
                $img_link = "images/icons/group.png";
            } ?>
        <li class="picbox_subgroup float_left">
            <a href="groups/<?php echo $group_data->getPKValue() ?>">
                <img class="framed_subgroup float_left"  width="60px" height="60px" alt="Group" src="<?php echo $img_link; ?>"/>
            </a>
            <div class="userinfo"><span class="small">
            <h4><a href="groups/<?php echo $group_data->getPKValue() ?>"><?php echo htmlspecialchars($group_data->Name, ENT_QUOTES) ?></a></h4>
                <?php echo $words->get('GroupsMemberCount');?>: <?php echo $group_data->getMemberCount(); ?><br />
                <?php echo $words->get('GroupsNewMembers');?>: <?php echo count($group_data->getNewMembers()) ; ?><br />
            </span></div> <!-- userinfo -->
        </li> <!-- picbox_subgroup -->

    <?php } ?>
    </ul>
    </div><!-- subcolumns -->
</div> <!-- groups -->

