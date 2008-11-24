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

$words = new MOD_words();

?>

<div id="forum">

  <h3><?php echo $words->getFormatted('ForumBrowse'); ?></h3>
    <div class="subcolumns">
      <div class="c33l">
        <div class="subcl category">
          <h4 class="floatbox"><?php echo '<img src="styles/YAML/images/iconsfam/folder_page.png" alt="'. $words->getBuffered('tags') .'" title="'. $words->getBuffered('tags') .'" class="forum_icon" />';?>&nbsp;<?php echo $words->flushBuffer(); ?><?php echo $words->getFormatted('ForumByCategory'); ?></h4>
          <ul>
          <?php
            foreach ($top_tags as $tagid => $tag) {
//              echo '<li><a href="forums/t'.$tagid.'-'.rawurlencode($tag->tag).'">'.$tag->tag.'</a><br />
			   			$TagName=$words->fTrad($tag->IdName) ;
              echo '<li><a href="forums/t'.$tagid.'-'.rawurlencode($TagName).'">'.$TagName.'</a><br />' ;
              echo '<span class="forums_tag_description">'.$tag->tag_description.'</span></li>';

						// Displays the last thread with a post for the current categorie
            	foreach ($tag->Post as $thread) {
								$url = ForumsView::threadURL($thread);
								if ($thread->IdGroup>0) {
									echo "<a href=\"bw/groups.php?action=ShowMembers&IdGroup=".$thread->IdGroup."\">",$words->getFormatted("Group_" . $thread->GroupName),"</a>::" ;
//							echo $words->getFormatted("Group_" . $thread->GroupName),"::" ;
								}
								echo "<a href=\"",$url,"\">" ;
								echo $words->fTrad($thread->IdTitle),"</a><br />"; 
							}
            }
            ?>
          </ul>
        </div> <!-- subcl -->
      </div> <!-- c33l -->

      <div class="c33l">
      </div> <!-- c33l -->

      <div class="c33r">
      </div> <!-- c33r -->
    </div> <!-- subcolumns -->
  
  
<br style="clear: both;" />
<?php
    $uri = 'forums/';
    if ($threads = $boards->getThreads()) {
?>
  <div class="row">
<?php  if ($User) { ?>
    <div class="r">
      <span class="button"><a href="forums/new"><?php echo $words->getBuffered('ForumNewTopic'); ?></a></span><?php echo $words->flushBuffer(); ?>
    </div> <!-- r -->
<?php } ?>    
<!--    <h3><?php echo $words->getFormatted('ForumRecentPosts'); $boards->getTotalThreads(); ?></h3> -->
  </div><!--  row -->
<?php
//        require 'boardthreads.php';
?>
</div> <!-- Forum-->
<?php
    }
?>
<br /><br />
<a href="rss/forumthreads"><img src="images/icons/feed.png" alt="RSS feed" /></a>