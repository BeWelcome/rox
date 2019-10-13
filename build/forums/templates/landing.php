<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330,
Boston, MA  02111-1307, USA.

*/


$User = APP_User::login();
$TIGHT_THREADLIST = true;
$noForumLegendBox = true;
$noForumNewTopicButton = true;

?>

<?php
$ToogleTagCloud=true ;
if ($User) $TagCloud=true ;
if (!$User) {
?>
    <div class="row">
        <?=$this->words->getFormatted('ForumOnlyForBeWelcomeMember'); ?>
    </div>
<?php
} // end if not User
?>
<!-- Now displays the recent groups post list -->
<?php
    echo $this->words->flushBuffer();
?>
    <div class="row">
        <div class="col-12">
            <h3><a href="groups/forums"><?php echo $this->words->getFormatted('Groups'); ?></a> <span class="small">&ndash; <?php echo $this->words->get('GroupsTagLine'); ?></span></h3>
        </div>
    </div>
</div>
<div class="row">
<?php
    $uri = 'forums/';
if ($threads = $groups->getThreads()) {
    $groups->getTotalThreads(); ?>
<?php
        //force pagination render to abort by feeding it's pager variables
        //invalid values because we want to call it separately later
        $multipages = null;
        $currentPage = null;
        $maxPage = null;
        $pages = null;

        require 'boardthreads.php';
?>

<?php
    if ($User && $moreLessThreadsCallbackId) {
?>
        <div class="col-12 col-md-6 mb-2 ">
            <a href="/forums/less/group" class="btn btn-primary mr-2"><?php echo $this->words->getSilent('ShowLess'); ?></a>
            <a href="/forums/more/group" class="btn btn-primary mr-2"><?php echo $this->words->getSilent('ShowMore'); ?></a>
<?php
    }
    if ($User && $ownGroupsButtonCallbackId) {
        if ($boards->owngroupsonly == "No") {
            $buttonText = $this->words->getBuffered('SwitchShowOnlyMyGroupsTopics');
            $href = "/forums/show/groups/only-mine";
        } else {
            $buttonText = $this->words->getBuffered('SwitchShowAllGroupsTopics');
            $href = "/forums/show/groups/all";
        } ?>
        <a href="<?= $href ?>" class="btn btn-primary"><?= $buttonText ?></a>
<?php
         echo $words->flushBuffer();
?>

        <?php
    echo $words->flushBuffer();
    }
?>
        </div>
<div class="col-12 col-md-6">
<?php

    $multipages = array($currentForumPage, $groupspages);
    $currentPage = $currentGroupsPage;
    $maxPage = $groupsMaxPage;

    require 'pages.php';

?>
    </div>

<?php
}
?>

<div id="forum" class="col-12 mt-3">
<!-- Now displays the recent forum post list -->
<?php
if ($User) {
?>
<?php
}
?>
    <h4><a href="forums/bwforum"><?php echo $this->words->getFormatted('AgoraForum'); ?></a> <span class="small">&ndash; <?php echo $this->words->get('AgoraTagLine'); ?></span></h4>
</div>
<?php
    $uri = 'forums/';
if ($threads = $forum->getThreads()) {
    $forum->getTotalThreads(); ?>
<?php
        //force pagination render to abort by feeding it's pager variables
        //invalid values because we want to call it separately later
        $multipages = null;
        $currentPage = null;
        $maxPage = null;
        $pages = null;

        require 'boardthreads.php';
?>
    <?
        $pages = null;

    if ($User && $moreLessThreadsCallbackId) {
?>
        <div class="col-12 col-md-6">
            <a href="/forums/less/agora" class="btn btn-primary mr-2"><?php echo $this->words->getSilent('ShowLess'); ?></a>
            <a href="/forums/more/agora" class="btn btn-primary mr-2"><?php echo $this->words->getSilent('ShowMore'); ?></a>
        </div>
        <div class="col-12 col-md-6">
<?php
    echo $words->flushBuffer();
    }

    $multipages = array($forumpages, $currentGroupsPage);
    $currentPage = $currentForumPage;
    $maxPage = $forumMaxPage;

    require 'pages.php';

?>
</div>

 <!-- Forum-->

<?php
}
?>
