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
?>

<div id="forum">

<?php
$ToogleTagCloud=true ;
if ($User) $TagCloud=true ;
if (!$User) {
?>
    <div class="subcolumns">
        <?=$this->words->getFormatted('ForumOnlyForBeWelcomeMember'); ?>
    </div>
<?php
} // end if not User
?> 

<!-- Now displays the recent forum post list -->
<h2><a href="forums/agora"><?php echo $this->words->getFormatted('ForumTitle'); ?></a></h2>	
<?php
    $uri = 'forums/';
    if ($threads = $forum->getThreads()) {
?>
  <div class="row"> 
    <h3><?php echo $this->words->getFormatted('ForumRecentPosts'); $forum->getTotalThreads(); ?>
    </h3>
  </div><!--  row -->
<?php
        require 'boardthreads.php';
?>
</div> <!-- Forum-->
<?php
    }
?>
<br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
<!-- Now displays the recent groups post list -->
<div id="groups">
<h2><a href="groups/forums"><?php echo $this->words->getFormatted('Groups'); ?></a></h2>
<?php
if ($User) {
    if ($boards->owngroupsonly == "No") {
        $buttonText = $this->words->getBuffered('SwitchShowOnlyMyGroupsTopics');
    } else {
        $buttonText = $this->words->getBuffered('SwitchShowAllForumTopics');
    }
    ?>
    <div class="float_right">
        <span class="button">
            <a href="forums/mygroupsonly"><?php echo $buttonText; ?></a>
        </span>
    </div>
<?php
    echo $this->words->flushBuffer();
}

    $uri = 'forums/';
    if ($threads = $groups->getThreads()) {
?>
  <div class="row"> 
    <h3><?php echo $this->words->getFormatted('ForumRecentPosts'); $groups->getTotalThreads(); ?>
    </h3>
  </div><!--  row -->
<?php
        require 'boardthreads.php';
?>
</div> <!-- Groups-->
<?php
    }
?>

<br /><br />
<a href="rss/forumthreads"><img src="images/icons/feed.png" alt="RSS feed" /></a>

