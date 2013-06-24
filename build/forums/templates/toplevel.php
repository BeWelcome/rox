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
$noForumLegendBox = true;
$ToogleTagCloud=true ;
if ($User) $TagCloud=true ;
if (!$User) {
?>
    <div class="row subcolumns">
        <?=$this->words->getFormatted('ForumOnlyForBeWelcomeMember'); ?>
    </div>
<?php
} // end if not User
if ($User && $ownGroupsButtonCallbackId) {
    if ($boards->owngroupsonly == "No") {
        $buttonText = $this->words->getBuffered('SwitchShowOnlyMyGroupsTopics');
    } else {
        $buttonText = $this->words->getBuffered('SwitchShowAllGroupsTopics');
    }
    ?>
    <div class="float_right">
        <form method="post" action="<?php echo rtrim(implode('/', $request), '/').'/';?>">
            <input type="hidden" name="<?php echo $ownGroupsButtonCallbackId; ?>"  value="1">
            <input type="submit" name="submit" value="<?php echo $buttonText; ?>">
        </form>
    </div>
    <?php
    echo $this->words->flushBuffer();
}
?> 

<span class="button float_right"><a href="<?php echo $uri; ?>new"><?php echo $this->words->getBuffered('ForumNewTopic'); ?></a></span>
<?php echo $this->words->flushBuffer(); ?>

<!-- Now displays the recent post list -->	
<?php
    $uri = 'forums/';
    if ($threads = $boards->getThreads()) {
?>
  <div class="row"> 
    <h3><?php echo $this->words->getFormatted('ForumRecentPosts'); $boards->getTotalThreads(); ?>
    </h3>
  </div><!--  row -->
<?php
        require 'boardthreads.php';
?>
</div> <!-- Forum-->
<?php
    }
?>
<br /><br />
<a href="rss/forumthreads"><img src="images/icons/feed.png" alt="RSS feed" /></a>
