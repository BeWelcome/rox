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
//	$i18n = new MOD_i18n('apps/forums/board.php');
//	$boardText = $i18n->getText('boardText');

	$can_del = false;
	$can_edit_own = false;
	$can_edit_foreign = false;

?>

<h2><?php echo $words->getFormatted('SearchResults'); ?></h2>

<?php
use App\Utilities\ForumUtilities;

?>
    <input type="hidden" id="read.more" value="<?php echo $words->get('forum.read.more'); ?>">
    <input type="hidden" id="show.less" value="<?php echo $words->get('forum.read.less'); ?>">
    <input type="hidden" id="keyword" name="keyword" value="<?php echo htmlspecialchars($keyword) ?>">
    <div class="row no-gutters">
        <div class="col-12">
            <h3><?= $words->get('GroupsSearchDiscussionsGroup', htmlspecialchars($keyword, ENT_QUOTES)); ?></h3>
        </div>
        <?php $pager->render();

        $words = new MOD_words();
        $styles = array('l-search-post--dark', '');

        $cnt = 0;
        foreach ($posts as $post) {
            ?>

            <div class="l-search-post <?php echo $styles[$cnt % 2]; ?> u-w-full">
                <div class="c-search-user_info">
                    <a id="post<?php echo $post->id; ?>" style="position: relative; top:-50px;"></a>
                    <div class="d-flex flex-row text-break">
                        <img class="profileimg avatar-48 mr-1" width="48" height="48" src="/members/avatar/<?php echo($post->Username); ?>/48">
                        <div class="small">
                            <a href="members/<?php echo $post->Username; ?>"><?php echo $post->Username; ?></a>
                            <?php
                            if (isset($post->city) && isset($post->country)) {
                                echo '<br>' . $post->city . '<br>' .$post->country;
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="c-search-post_info">
                    <div class="d-flex flex-column flex-sm-row small">
                        <?php
                        echo '<div class="mr-1"><span><i class="fa fa-comment fa-w-16 mr-1" title="' . $words->getFormatted('posted'); ?>"></i><?php echo date($words->getBuffered('DateHHMMShortFormat'), ServerToLocalDateTime($post->created, $this->getSession())) . '</span></div>';
                        echo $words->flushBuffer() . '<div class="mr-1"><i class="fa fa-eye fa-w-16 mr-1" title="' . $words->getFormatted("forum_label_visibility") . '"></i>' . $words->getFormatted("forum_edit_vis_" . $post->ThreadVisibility) . '</div>';
                        ?>
                    </div>
                </div>
                <div class="c-search-permalink">
                    <?php
                    if (isset($post->IdGroup) && $post->IdGroup != 0) {
                        echo '<small><a href="group/' .$post->IdGroup . '/forum/s' . $post->IdThread . '/#post' . $post->id . '"><i class="fa fa-link"></i> ' . $words->get('ForumPermalink') . '</a></small>';
                    } else {
                        echo '<small><a href="forums/s' . $post->IdThread . '/#post' . $post->id . '"><i class="fa fa-link"></i> ' . $words->get('ForumPermalink') . '</a></small>';
                    }
                    ?>
                </div>

                <div class="c-search-content js-highlight js-read-more">
                    <?php echo $post->message; ?>
                </div>
                <div class="c-search-thread_info js-highlight">
                    <div class="u-flex u-justify-end">
                        <small>
                            <?php
                                $title = strip_tags($post->title);
                                if (isset($post->IdGroup) && $post->IdGroup != 0) {
                                    echo $words->get('forum.group');
                                    echo '<a href="group/' . $post->IdGroup . '">' . $post->GroupName . "</a> ";
                                    $threadLink = '<a href="group/' .$post->IdGroup . '/forum/s' . $post->IdThread . '/#post' . $post->id . '">' . $title . '</a>';
                                } else {
                                    $threadLink = '<a href="forums/s' . $post->IdThread . '/#post' . $post->id . '">' . $title . '</a>';
                                }
                                echo $words->get('forum.thread');
                                echo $threadLink;
                            ?>
                        </small>
                    </div>
                </div>
            </div>
            <?php $cnt++;
        }
        $pager->render();
        ?>
    </div>

