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
} // end if User
?>
<!-- Now displays the recent post list -->	
<?php
    $uri = 'forums/';
    if ($threads = $boards->getThreads()) {
?>
  <div class="row"> 
    <h4><?php echo $this->words->getFormatted('ForumRecentPosts'); $boards->getTotalThreads(); ?>
    <?php
    if ($User) {
        if ($boards->owngroupsonly == "No") {
            $buttonText = $this->words->getBuffered('SwitchShowOnlyMyGroupsTopics');
        } else {
            $buttonText = $this->words->getBuffered('SwitchShowAllForumTopics');
        }
        $url=$_SERVER['REQUEST_URI'] ;
        if (strpos($url,"/mygroupsonly")===false) {
            // in order to avoid to concatenate /mygroupsonly twice
            $url.="/mygroupsonly"  ;
        }
        ?>
        <div class="float_right"><span class="button"><a href="<?php echo $url; ?>"><?php echo $buttonText; ?></a></span></div><?php echo $this->words->flushBuffer(); ?>
        <?php
    }
    ?> 
    </h4>
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
