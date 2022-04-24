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

This form is for editing or translating a post
it is call by the Edit/Translate link
and by the edit post


*/

$words = new MOD_words();

$request = PRequest::get()->request;
$uri = implode('/', $request);
$groupsforum = ($request[0] == 'group' && is_numeric($request[1])) ? $request[1] : false;

$vars =& PPostHandler::getVars($callbackId);

?>
<?php /* obsolete javascript?
<script type="text/javascript" src="script/blog_suggest.js"></script>
<script type="text/javascript" src="script/forums_suggest.js"></script>
*/ ?>

<div class="row no-gutters">
    <div class="col-12">

    <?php
    echo '<h3>';
    if ($allow_title) { // New Topic
        if ($edit) {
            echo $words->getFormatted("forum_edit_topic");
        } else {
            echo $words->getFormatted("forum_new_topic");
        }
    } else { // Answer
        if ($edit) {
            echo $words->getFormatted("forum_edit_post");
        } else {
            $backUrl = str_replace('/reply', '', $uri);
            echo $words->getFormatted("forum_reply_title") . ' &quot;<i><a href="' . $backUrl . '">' . strip_tags($topic->topicinfo->title) . '</a></i>&quot;';
        }
    }
    echo '</h3>';
    ?>

</div>
    <div class="col-12">
<form method="post" onsubmit="return check_SelectedLanguage();" action="<?php echo $uri; ?>" name="editform"
      id="forumsform">
    <div class="row no-gutters">
    <input type="hidden" name="<?php echo $callbackId; ?>" value="1"/>
    <input type="hidden" name="IdLanguage" id="IdLanguage" value="0">

    <?php
        if (isset($vars['errors']) && is_array($vars['errors'])) {
            if (in_array('title', $vars['errors'])) {
                echo '<div class="alert alert-danger">' . $words->getFormatted("forum_error_title") . '</div>';
            }
        }
        if (isset($vars['errors']) && is_array($vars['errors'])) {
            if (in_array('text', $vars['errors'])) {
                echo '<div class="alert alert-danger">' . $words->getFormatted("forum_error_post") . '</div>';
            }
        }
        ?>

<div class="col-12">
        <?php
        if (isset($allow_title) && $allow_title) {

            ?>
            <!-- input title -->
                <div class="o-form-group mb-2">
                        <label class="m-0"
                                                             for="topic_title"><?php echo $words->getFormatted("forum_label_topicTitle"); ?></label>


                    <?php
                    $topic_titletrad = "";
                    if (isset($vars['topic_title'])) {
                        if (isset($vars['IdTitle'])) {
                            $topic_titletrad = $words->fTrad($vars['IdTitle']);
                        } else {
                            $topic_titletrad = $vars['topic_title'];
                        }
                    }
                    ?>

                    <input type="text" class="o-input" name="topic_title" maxlength="200" id="topic_title"
                           value="<?php echo htmlspecialchars($topic_titletrad); ?>" aria-describedby="forumaddtitle">
            </div>

        <?php } ?>
    </div>
    <div class="col-12 mb-2">
        <div class="o-form-group">
            <label for="topic_text"><?php echo $words->getFormatted("forum_label_text"); ?></label>

            <textarea name="topic_text" id="topic_text" class="o-input editor" rows="10" style="min-height: 10em;" placeholder="<?= $words->get('forum.post.placeholder'); ?>"><?php
                if (isset($void_string)) {
                    echo $void_string;
                } else {
                    echo isset($vars['topic_text']) ? $vars['topic_text'] : '';
                }
                ?></textarea>

            <?php
            if ($groupsforum) {
                echo '<input type="hidden" name="IdGroup" value="' . $groupsforum . '">';
            } else {
                if (isset($vars['IdGroup']) && $vars['IdGroup'] != 0 && is_numeric($vars['IdGroup'])) {
                    echo '<input type="hidden" name="IdGroup" value="' . intval($vars['IdGroup']) . '">';
                }
            } ?>
        </div>
    </div>
    <div class="col-12 col-md-4 order-1 order-md-2 mb-1 px-1">
        <div class="o-checkbox">
            <input type="checkbox" name="NotifyMe" id="NotifyMe" class="o-checkbox__input" <?php echo $notifymecheck ?>>
            <label for="NotifyMe" class="o-checkbox__label"><?php echo $words->getFormatted("forum_NotifyMeForThisThread") ?></label>
        </div>
    </div>

        <div class="col-12 col-md-4 order-2 order-md-3 mb-1 px-1">
                    <legend class="sr-only"><?= $words->getFormatted("forum_label_visibility") ?></legend>
                    <?php
                    // visibility can only be set on groups with 'VisiblePosts' set to 'yes'.
                    // Only option to change is to show group post to all members (see #2167)
                    if ($groupsforum || ($IdGroup != 0)) {
                        if (empty($visibilityCheckbox)) {
                            // Stupid hack to avoid too many code changes ?>
                            <input type="hidden" name="PostVisibility" id="PostVisibility" value="GroupOnly">
                            <input type="hidden" name="ThreadVisibility" id="ThreadVisibility" value="GroupOnly">
                            <?php if ($allow_title) {
                                    echo $words->get('ForumsThreadGroupOnly');
                                } else {
                                    echo $words->get('ForumsPostGroupOnly');
                                } ?>
                        <?php } else {
                            echo $visibilityCheckbox;
                        }
                    } else {
                        // Stupid hack to avoid too many code changes ?>
                        <input type="hidden" name="PostVisibility" id="PostVisibility" value="MembersOnly"/>
                        <input type="hidden" name="ThreadVisibility" id="ThreadVisibility" value="MembersOnly"/>
                        <?php if ($allow_title) {
                                echo $words->get('ForumsThreadMembersOnly');
                            } else {
                                echo $words->get('ForumsPostMembersOnly');
                            } ?>
                    <?php } ?>
        </div>

            <div class="col-12 col-md-4 order-3 order-md-1 mb-2">
                <input type="submit" class="btn btn-primary" value="<?php
                if ($allow_title) { // New Topic
                    if ($edit) {
                        echo $words->getFormatted("forum_label_update_topic");
                    } else {
                        echo $words->getFormatted("forum_label_create_topic");
                    }
                } else { // Answer
                    if ($edit) {
                        echo $words->getFormatted("forum_label_update_post");
                    } else {
                        echo $words->getFormatted("forum_label_create_post");
                    }
                }

                ?>"/>
            </div>
    </div>
    </form>
    </div>
</div>

