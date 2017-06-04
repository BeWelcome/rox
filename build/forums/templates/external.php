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

<table class="table table-responsive table-striped table-hover">
    <tbody>
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
                <td><i class="fa fa-comments-o pr-1"></i>
                    <?php
                    if ($thread->ThreadDeleted=='Deleted') {
                        echo "[Deleted]" ;
                    }
                    if ($thread->ThreadVisibility=="ModeratorOnly") {
                        echo "[ModOnly]" ;
                    }
                    ?>
                    <a href="<?php echo $url; ?>" class="bold">
                    <?php
                    echo $words->fTrad($thread->IdTitle);
                    ?></a><br>
                    <span class="small grey"><?php echo $words->getSilent('by');?> <a href="members/<?php echo $thread->last_author; ?>"><?php echo $thread->last_author; ?></a>
                    <?php if ($thread->IdGroup > 0 && $showGroups)
                        {
                            echo $words->getFormatted('in') . ' <a href="groups/' . $thread->IdGroup . '/" title="' . $words->getSilent('Group') . ": " . $thread->GroupName . '">' . MOD_layoutbits::truncate($thread->GroupName, 13) . "</a>";
                        }
                    ?>
                    <?php echo ' - <span title="' . date($words->getSilent('DateHHMMShortFormat'), ServerToLocalDateTime($thread->last_create_time, $this->getSession())) . '"><a href="' . $last_url . '" class="grey">' . $layoutbits->ago($thread->last_create_time) . '<i class="fa fa-caret-right ml-1" title="' . $words->getBuffered('to_last') . '"></i></a></span>'; ?>
                  <?php echo $words->flushBuffer(); ?>
                </td>
            </tr>
        <?php
    }


?>
    </tbody>
</table>

<?php
}
    else
    {
        if ($User) {
            if ($isGroupMember) {
                echo $words->getBuffered('GroupsNoForumPosts');
            } else {
                echo $words->getBuffered('GroupsNoPublicPosts');
            }
        }
    }

    if ($showNewTopicButton && $User && $uri != 'forums/') {
    ?>
    <div id="boardnewtopicbottom"><a class="btn btn-primary" href="<?php echo $this->uri; ?>new
    <?php 
    if (!empty($this->_model->IdGroup)) echo "/u" . $this->_model->IdGroup ;
    echo "\">",$words->getBuffered('ForumNewTopic');
    ?></a><?php echo $words->flushBuffer(); ?></div>
    <?php
    }

?>
