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
/** @var Member $User */
$User = $this->_model->getLoggedInMember();

$words = new MOD_words();
$layoutbits = new MOD_layoutbits();
?>


<?php
    $uri = ForumsView::getURI();
    if ($threads = $boards->getThreads()) {
?>

<table class="table table-striped table-hover" style="table-layout: fixed;">
    <tbody>
<?php
    $shown = 0;
    $ascending = true;

    if ($User) {
        $setPreference = $User->getPreference('PreferenceForumOrderListAsc', 'Yes');
        $ascending = 'Yes' == $setPreference;
    }

    for ($i = 0; $shown < 5 && $i < count($threads); $i++) {
        $thread = $threads[$i];

        $url = ForumsView::threadURL($thread);
        if ($url[0] =='s') { // JeanYves Hack/Fix to be sure that forums/ is written in the beginning of the links !
            $url="forums/" . $url ;
        }

        $max = $thread->replies + 1;
        if ($ascending) {
            $maxPage = ceil($max / Forums::CV_POSTS_PER_PAGE);

            $last_url = $url . ($maxPage != 1 ? '/page'.$maxPage : '') . '/#post' . $thread->last_postid;
        } else {
            $last_url = $url; // ordering descending means the first shown post is the latest one
        }

        if (('NoRestriction' === $thread->ThreadVisibility)
            || ('MembersOnly' === $thread->ThreadVisibility)
            || (('GroupOnly' === $thread->ThreadVisibility) && (true === $isGroupMember))
        ) {
        ?>
            <tr>
                <td class="text-truncate"><?php
                    if ($thread->ThreadDeleted=='Deleted') {
                        echo "[Deleted]" ;
                    }
                    if ($thread->ThreadVisibility=="ModeratorOnly") {
                        echo "[ModOnly]" ;
                    }
                    ?>
                    <a href="<?php echo $url; ?>">
                    <?php
                    echo $words->fTrad($thread->IdTitle);
                    ?></a>
                    <div class="w-100">
                    <span class="small grey"><?php echo $words->getSilent('by');?> <a href="members/<?php echo $thread->last_author; ?>"><?php echo $thread->last_author; ?></a>
                    <?php if ($thread->IdGroup > 0 && $showGroups)
                        {
                            echo $words->getFormatted('in') . ' <a href="group/' . $thread->IdGroup . '/" title="' . $words->getSilent('Group') . ': ' . $thread->GroupName . '">' . $thread->GroupName . '</a></span>';
                        } else {
                            echo '</span>';
                    }
                    ?>
                    <?php echo '<span class="small grey pull-right" title="' . date($words->getSilent('DateHHMMShortFormat'), ServerToLocalDateTime($thread->last_create_time, $this->getSession())) . '"><a href="' . $last_url . '" class="grey">' . $layoutbits->ago($thread->last_create_time) . '<i class="fa fa-caret-right ml-1" title="' . $words->getBuffered('to_last') . '"></i></a></span>'; ?>
                  <?php echo $words->flushBuffer(); ?>
                    </div>
                </td>
            </tr>
        <?php
            $shown++;
        }
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
        if ($this->_model->IdGroup) {
            echo '<div id="boardnewtopicbottom"><a class="btn btn-primary" href="group/' . $this->_model->IdGroup . '/new">';
        } else {
            echo '<div id="boardnewtopicbottom"><a class="btn btn-primary" href="' . $this->uri . 'new">';
        }
        echo $words->getBuffered('ForumNewTopic');
        echo '</a></div>';
    }
