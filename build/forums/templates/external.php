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
$layoutbits = new MOD_layoutbits();
?>


<?php
    $uri = ForumsView::getURI();
    if ($threads = $boards->getThreads()) {
?>

<table class="forumsboardthreads floatbox">

<?php 
$threadsliced = array_slice($threads, 0, 5);
    foreach ($threadsliced as $cnt =>  $thread) {
        $url = ForumsView::threadURL($thread);
        if ($url{0}=='s') { // JeanYves Hack/Fix to be sure that forums/ is written in the beginning of the links !
            $url="forums/" . $url ;
        }

        $max = $thread->replies + 1;
        $maxPage = ceil($max / $this->_model->POSTS_PER_PAGE);

        $last_url = $url . ($maxPage != 1 ? '/page'.$maxPage : '') . '/#post' . $thread->last_postid;


        ?>
            <tr>
                <td class="forumsboardthreadtitle"><?php echo '<img src="styles/css/minimal/images/iconsfam/comment_add.png" alt="' . $words->getBuffered('tags') . '" title="' . $words->getBuffered('tags') . '" />' . $words->flushBuffer(); ?>
                    <?php
                    if ($thread->ThreadDeleted=='Deleted') {
                        echo "[Deleted]" ;
                    }
                    if ($thread->ThreadVisibility=="ModeratorOnly") {
                        echo "[ModOnly]" ;
                    }
                    ?>
                    <a href="<?php echo $url; ?>" class="news">
                    <?php
                    echo $words->fTrad($thread->IdTitle);
                    ?></a><br />
                    <span class="small grey"><?php echo $words->getFormatted('by');?> <a href="members/<?php echo $thread->last_author; ?>"><?php echo $thread->last_author; ?></a>
                    <?php if ($thread->IdGroup > 0 && $showGroups)
                        {
                            echo $words->getFormatted('in') . ' <a href="groups/' . $thread->IdGroup . '/" title="' . $words->getFormatted('Group') . ": " . $thread->GroupName . '">' . MOD_layoutbits::truncate($thread->GroupName, 13);
                        }
                    ?>
                    <?php echo '</a> - <span title="' . date($words->getFormatted('DateHHMMShortFormat'), ServerToLocalDateTime($thread->last_create_time)) . '"><a href="' . $last_url . '" class="grey">' . $layoutbits->ago($thread->last_create_time) . '</a></span>'; ?>
                    </span>
                    <a href="<?php echo $last_url; ?>"><img src="styles/css/minimal/images/iconsfam/bullet_go.png" alt="<?php echo $words->getBuffered('to_last'); ?>" title="<?php echo $words->getBuffered('to_last'); ?>" align="absmiddle" /></a><?php echo $words->flushBuffer(); ?>
                </td>
            </tr>
        <?php
    }


?>

</table>

<?php
}
    else
    {
        echo $words->getBuffered('GroupsNoForumPosts');
    }
    if ($User && $uri != 'forums/') {
    ?>
    <div id="boardnewtopicbottom"><span class="button"><a href="<?php echo $this->uri; ?>new
    <?php 
    if (!empty($this->_model->IdGroup)) echo "/u" . $this->_model->IdGroup ;
    echo "\">",$words->getBuffered('ForumNewTopic'); 
    ?></a></span><?php echo $words->flushBuffer(); ?></div>
    <?php
    }

?>
