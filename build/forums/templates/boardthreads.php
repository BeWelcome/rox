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
$words = new MOD_words();
$layoutbits = new MOD_layoutbits();
?>

<div class="col-12">
<!-- table structure -->
<table class="table table-striped table-hover">
    <!-- beginning of table head -->
    <thead class="blank">
    <tr>
        <th><?php echo $words->getFormatted('Author'); ?></th>
        <th class="w-100 pl-0">
            <?php
            if (empty($TIGHT_THREADLIST)) {
            echo $words->getFormatted('Thread');
            }
            else {
            echo $words->getFormatted('ForumRecentPosts');
            } ?>
        </th>
        <th><i class="fa fa-comment d-lg-none" title="<?php echo $words->getFormatted('Replies'); ?>"></i><span class="d-none d-lg-table-cell"><?php echo $words->getFormatted('Replies'); ?></span></th>
        <th class="d-none d-md-table-cell"><i class="fa fa-eye d-lg-none" title="<?php echo $words->getFormatted('Views'); ?>"></i><span class="d-none d-lg-table-cell"><?php echo $words->getFormatted('Views'); ?></span></th>
        <th class="text-nowrap d-none d-md-table-cell"><?php echo $words->getFormatted('LastPost'); ?></th>
        <th></th>
    </tr>
    </thead>
    <!-- end of table head -->
    <tbody>
    <!-- beginning of row loop -->
    <?php
        foreach ($threads as $cnt =>  $thread) {

        if ($thread->IdGroup){
            $url = ForumsView::threadURL($thread, 'group/'.$thread->IdGroup.'/forum/');
        }
        else {
            $url = ForumsView::threadURL($thread);
        }
        $max = $thread->replies + 1;
        $maxPage = ceil($max / $this->_model->POSTS_PER_PAGE);

        $last_url = $url.($maxPage != 1 ? '/page'.$maxPage : '').'/#post'.$thread->last_postid;
    ?>
    <tr>
        <th class="middle p-1">
            <a href="members/<?php echo $thread->first_author; ?>"><img class="avatar-50" src="members/avatar/<?php echo $thread->first_author; ?>/50" alt="<?php echo $thread->first_author; ?>" title="<?php echo $thread->first_author; ?>" /><br>
                <small><?php echo $thread->first_author; ?></small>
            </a>
        </th>
        <td class="middle text-left p-1">
            <?php
            if ($thread->stickyvalue < 0) {
                echo '<i class="fa fa-exclamation-circle" alt="'. $words->getSilent('PinnedPost') .'" title="'. $words->getSilent('PinnedPost') .'" /></i> ' . $words->flushBuffer();
            }
            if ($thread->ThreadDeleted=="Deleted") {
                echo "[Deleted] " ;
            }
            if ($thread->ThreadVisibility=="ModeratorOnly") {
                echo "[ModOnly] " ;
            }
            echo "<a href=\"",$url,"\">" ;
            echo $words->fTrad($thread->IdTitle);
            ?>
            <br>
            <span class="small gray"><?php
                if ($thread->IdGroup>0) {
                    echo "<a href=\"group/".$thread->IdGroup."\"><strong>" . $words->getFormatted('Group'). ": </strong>",$this->_model->getGroupName($thread->GroupName),"</a>" ;
                }
                ?></span>
        </td>
        <td class="middle p-1"><?php echo $thread->replies; ?></td>
        <td class="middle p-1 d-none d-md-table-cell"><?php echo number_format($thread->views); ?></td>
        <td class="middle text-nowrap p-1 d-none d-md-table-cell">
            <div class="d-flex flex-row mr-2">
                <div class="align-self-center"><a href="members/<?php echo $thread->last_author; ?>"><img class="avatar-50" src="members/avatar/<?php echo $thread->last_author; ?>/50" alt="<?php echo $thread->last_author; ?>" title="<?php echo $thread->last_author; ?>" /></a></div>
                <div class="pl-2 align-self-center text-left">
                    <small><a href="members/<?php echo $thread->last_author; ?>"><?php echo $thread->last_author; ?></a></small><br>
                    <span class="small gray" title="<?php echo date($words->getSilent('DateHHMMShortFormat'), ServerToLocalDateTime($thread->last_create_time, $this->getSession())); ?>"><a href="<?php echo $last_url; ?>"><?php echo $layoutbits->ago($thread->last_create_time); ?></a></span>
                </div>
            </div>
        </td>
        <td class="middle p-1">
            <a href="<?php echo $last_url; ?>"><i class="fa fa-chevron-right" alt="<?php echo $words->getBuffered('to_last'); ?>" title="<?php echo $words->getBuffered('to_last'); ?>"></i></a><?php echo $words->flushBuffer(); ?>
        </td>
    </tr>
            <!-- end of loop -->
    <?php } ?>
    </tbody>
</table>

    <?php if (count($threads) == 0) {
        echo '<div class="alert alert-notice">' . $words->getSilent('ForumNoThreads') . '</div>';
    } ?>
<!-- end of new table structure -->
</div>

<?php
if ($User && empty($noForumNewTopicButton)) {
?>
    <div class="col-12">
        <a class="btn btn-primary" href="<?php if ($this->_model->IdGroup) {
            echo "group/" . $this->_model->IdGroup . "/";
        } else {
            echo $this->uri;
        } ?>new"><?php echo $words->getBuffered('ForumNewTopic'); ?></a><?php echo $words->flushBuffer(); ?>
    </div>
    <?php
}
?>
    <div class="col-12">
        <?php require 'pages.php'; ?>
    </div>
