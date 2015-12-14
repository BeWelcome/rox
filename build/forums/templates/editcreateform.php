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
$groupsforum = ($request[0] == 'groups' && is_numeric($request[1])) ? $request[1] : false;

if (isset($this->suggestionsGroupId)) {
    $groupsforum = $this->suggestionsGroupId;
}

$vars =& PPostHandler::getVars($callbackId);

if (isset($vars['tags']) && $vars['tags']) {
    $tags_with_commas = implode(', ', $vars['tags']);
} else if (isset($tags) && $tags) {
    $tags_with_commas = implode(', ', $tags);
} else {
    $tags_with_commas = false;
}

?>
<script type="text/javascript" src="script/blog_suggest.js"></script>
<script type="text/javascript" src="script/forums_suggest.js"></script>
<?php
if (!isset($disableTinyMCE) || ($disableTinyMCE == 'No')) {
    $textarea = 'topic_text';
    require_once SCRIPT_BASE . 'htdocs/script/tinymceconfig_php.js';
}
?>
<h2>
    <?php
    if ($navichain_items = $boards->getNaviChain()) {
        $navichain = '<span class="forumsboardnavichain">';
        foreach ($navichain_items as $link => $title) {
            $navichain .= '<a href="' . $link . '">' . $title . '</a> :: ';
        }
        $navichain .= '<a href="' . $boards->getBoardLink() . '">' . $boards->getBoardName() . '</a><br /></span>';
    } else {
        $navichain = '';
    }

    echo $navichain;

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
            echo $words->getFormatted("forum_reply_title") . ' &quot;' . $topic->topicinfo->title . '&quot;';
        }
    }
    ?></h2>

<form method="post" onsubmit="return check_SelectedLanguage();" action="<?php echo $uri; ?>" name="editform"
      class="fieldset_toggles" id="forumsform">
    <input type="hidden" name="<?php echo $callbackId; ?>" value="1"/>
    <?php
    if (isset($vars['errors']) && is_array($vars['errors'])) {
        if (in_array('title', $vars['errors'])) {
            echo '<div class="row error">' . $words->getFormatted("forum_error_title") . '</div>';
        }
    }
    if (isset($vars['errors']) && is_array($vars['errors'])) {
        if (in_array('text', $vars['errors'])) {
            echo '<div class="row error">' . $words->getFormatted("forum_error_post") . '</div>';
        }
    }
    if (isset($allow_title) && $allow_title) {

        ?>
        <div class="bw-row">
            <label for="topic_title"><?php echo $words->getFormatted("forum_label_topicTitle"); ?></label><br/>
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
            <input type="text" style="width: 95%" name="topic_title" size="50" maxlength="200" id="topic_title"
                   value="<?php echo $topic_titletrad; ?>"/>
        </div> <!-- row -->
    <? } ?>
    <div class="bw-row">
        <label for="topic_text"><?php echo $words->getFormatted("forum_label_text"); ?></label><br/>
        <textarea name="topic_text" cols="70" rows="15" id="topic_text" class="long"><?php
            if (isset($void_string)) {
                echo $void_string;
            } else {
                echo isset($vars['topic_text']) ? $vars['topic_text'] : '';
            }
            ?></textarea>
    </div>
    <!-- row -->

    <?php

    if (isset($allow_title) && $allow_title) {
        ?>
    <fieldset class="bw-row" id="fpost_tags_and_location_fieldset">
        <legend onclick="toggleFieldsets('fpost_tags_and_location');"><?php echo $words->getFormatted("forum_label_tags_and_location"); ?></legend>
        <div id="fpost_tags_and_location"><div>
        <p class="small"><?php echo $words->getFormatted("forum_subline_tags"); ?></p>
        <textarea id="create-tags" name="tags" cols="60" rows="2" class="long"
        <?php
// In case we are in edit mode, this field is a read only, tags cannot be edited by members
// lupochen asks: Why?
        if ($edit) {
            echo "\"readonly\"" ;
        }
        ?>><?php
        // the tags may be set
            echo ($tags_with_commas) ? htmlentities($tags_with_commas, ENT_COMPAT, 'utf-8') : '';
        ?></textarea>
        <div id="suggestion"></div>
<?php /*
        <p class="small"><?php echo $words->getFormatted("forum_subline_place"); ?></p>
        <div id="dropdowns">
        <?php
            echo $locationDropdowns;
        ?>
        </div>
*/?>
    </div></div>
    </fieldset> <!-- row -->

<?php } // End if $allow_title 

    if ($groupsforum) {
        echo '<input type="hidden" name="IdGroup" value="' . $groupsforum . '">';
        if (isset($this->suggestionsThreadId)) {
            echo '<input type="hidden" name="ThreadId" value="' . $this->suggestionsThreadId . '">';
        }
        if (isset($this->suggestionId)) {
            echo '<input type="hidden" name="SuggestionId" value="' . $this->suggestionId . '">';
        }
        if (isset($this->suggestionsUri)) {
            echo '<input type="hidden" name="SuggestionURI" value="' . $this->suggestionsUri . '">';
        }
    } else {
        if (isset($vars['IdGroup']) && $vars['IdGroup'] != 0 && is_numeric($vars['IdGroup'])) {
            echo '<input type="hidden" name="IdGroup" value="' . intval($vars['IdGroup']) . '">';
        } else {
            echo '<input type="hidden" name="IdGroup" value="0">';
        }
    } ?>

<?php if (isset($this->suggestionsGroupId) || ($groupsforum == SuggestionsModel::getGroupId())) { ?>
    <fieldset class="bw_row" id = "fpost_vis_fieldset" >
        <legend onclick = "toggleFieldsets('fpost_vis');" ><?php echo $words->getFormatted("ForumSuggestionsVisibilityAndLanguage"); ?></legend>
        <div id="fpost_vis">
            <p><?= $words->get('SuggestionsGroupInfoReply') ?></p>
            <input type="hidden" name="PostVisibility" id="PostVisibility" value="MembersOnly"/>
            <input type="hidden" name="IdLanguage" id="IdLanguage" value="0"/>
        </div>
    </fieldset>
<?php } else { ?>
    <fieldset class="bw-row" id="fpost_vis_fieldset">
        <legend onclick="toggleFieldsets('fpost_vis');"><?= $words->getFormatted("forum_label_visibility") ?></legend>
        <div>
            <?php
            // visibility can only be set on groups with 'VisiblePosts' set to 'yes'.
            // Only option to change is to show group post to all members (see #2167)
            if ($groupsforum || ($IdGroup != 0)) {
                if (empty($visibilityCheckbox)) {
                    // Stupid hack to avoid too many code changes ?>
                    <input type="hidden" name="PostVisibility" id="PostVisibility" value="GroupOnly"/>
                    <input type="hidden" name="ThreadVisibility" id="ThreadVisibility" value="GroupOnly"/>
                    <p><?php if ($allow_title) {
                        echo $words->get('ForumsThreadGroupOnly');
                    } else {
                        echo $words->get('ForumsPostGroupOnly');
                    } ?></p>
                    <?php }  else {
                        echo $visibilityCheckbox;
                    }
                } else {
                    // Stupid hack to avoid too many code changes ?>
                    <input type="hidden" name="PostVisibility" id="PostVisibility" value="MembersOnly"/>
                    <input type="hidden" name="ThreadVisibility" id="ThreadVisibility" value="MembersOnly"/>
                    <p><?php if ($allow_title) {
                        echo $words->get('ForumsThreadMembersOnly');
                    } else {
                        echo $words->get('ForumsPostMembersOnly');
                    } ?></p>
            <?php } ?>
        </div>
    </fieldset>
    <fieldset class="bw-row" id="fpost_lang_fieldset">
            <legend
                onclick="toggleFieldsets('fpost_lang');"><?php echo $words->getFormatted("forum_label_lang") ?></legend>
            <div id="fpost_lang">
                <div>
                    <select name="IdLanguage" id="IdLanguage"><?php
                        // Here propose to choose a language, a javascript routine at the form checking must make it mandatory
                        if (!isset($AppropriatedLanguage)) {
                            echo "<option value=\"-1\">-</option>";
                        }

                        $closeOptGroup = false;
                        $closeOptGroupFinal = false;
                        foreach ($LanguageChoices as $Choices) {
                            if (is_string($Choices)) {
                                switch ($Choices) {
                                    case "CurrentLanguage":
                                        echo '<optgroup label="' . $words->getSilent("ForumCurrentLanguage") . '">';
                                        $closeOptGroup = true;
                                        break;
                                    case "DefaultLanguage":
                                        echo '<optgroup label="' . $words->getSilent("ForumDefaultLanguage") . '">';
                                        $closeOptGroup = true;
                                        break;
                                    case "UILanguage":
                                        echo '<optgroup label="' . $words->getSilent("ForumUILanguage") . '">';
                                        $closeOptGroup = true;
                                        break;
                                    case "AllLanguages":
                                        echo '<optgroup label="' . $words->getSilent("ForumAllLanguages") . '">';
                                        $closeOptGroupFinal = true;
                                        break;
                                }
                            } else {
                                echo "<option value=\"", $Choices->IdLanguage, "\"";
                                if ((isset($AppropriatedLanguage)) and ($AppropriatedLanguage == $Choices->IdLanguage)) {
                                    echo " selected='selected'";
                                }
                                echo ">", $Choices->Name, "</option>";
                                if ($closeOptGroup) {
                                    echo "</optgroup>";
                                    $closeOptGroup = false;
                                }
                            }
                        }
                        if ($closeOptGroupFinal) {
                            echo "</optgroup>";
                        }
                        ?></select><?php echo $words->flushBuffer(); ?>
                    <?php echo $words->getFormatted("forum_ChooseYourLanguage") ?>
                </div>
            </div>
        </fieldset> <!-- row -->
    <?php } ?>
    <fieldset class="bw-row" id="fpost_note_fieldset">
        <legend onclick="toggleFieldsets('fpost_note');"><?php echo $words->getFormatted("forum_Notify") ?></legend>
        <div id="fpost_note">
            <div>
                <input type="checkbox" name="NotifyMe" id="NotifyMe" <?php echo $notifymecheck ?>>
                <label for="NotifyMe"><?php echo $words->getFormatted("forum_NotifyMeForThisThread") ?></label>
            </div>
        </div>
    </fieldset>
    <!-- row -->


    <div class="bw-row">
        <input type="submit" class="button" value="<?php
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
    <!-- row -->

</form>

<script type="text/javascript">
    function updateContinent() {
        var urlbit = 'k' + $('d_continent').value;
        update(urlbit);
    }

    function updateCountry() {
        var urlbit = 'k' + $('d_continent').value + '/c' + $('d_country').value;
        update(urlbit);
    }

    function updateAdmincode() {
        var urlbit = 'k' + $('d_continent').value + '/c' + $('d_country').value + '/a' + $('d_admin').value;
        update(urlbit);
    }

    function updateGeonames() {
        var urlbit = 'k' + $('d_continent').value + '/c' + $('d_country').value + '/a' + $('d_admin').value + '/g' + $('d_geoname').value;
        update(urlbit);
    }

    function update(urlbit) {
        <?php /*
        if ($edit) {
            echo '$("forumsform").action = http_baseuri+"forums/edit/m'.$messageid.'/"+urlbit;';
        } else {
            echo '$("forumsform").action = http_baseuri+"forums/new/"+urlbit;';
        }
        */ ?>

        var url = http_baseuri + 'forums/locationDropdowns/' + urlbit
        new Ajax.Request(url,
            {
                method: 'get',
                onSuccess: function (req) {
                    updateDropdowns(req.responseText);
                }
            });
    }

    function updateDropdowns(text) {
        Element.update('dropdowns', text);
    }

    function toggleFieldsets(el_name, instantly) {
        if (instantly) $(el_name).toggle();
        else Effect.toggle(el_name, 'slide', { duration: 0.2 });
        Element.toggleClassName($(el_name + '_fieldset'), 'collapsed');
    }

    // purpose here is to force the user to select a language
    function check_SelectedLanguage() {
        if (document.editform.IdLanguage.value == -1) {
            alert('<?php echo $words->getFormatted("YouMustSelectALanguage") ?>');
            document.editform.IdLanguage.focus();
            return(false);
        }
    }
    function forumOnload() {
        ForumsSuggest.initialize();
        <?php if (isset($allow_title) && $allow_title) { ?>
        toggleFieldsets('fpost_tags_and_location', 1);
        <? } ?>
        toggleFieldsets('fpost_note', 1);
        // toggleFieldsets('fpost_lang',1);
    }

    document.observe("dom:loaded", forumOnload);

</script>
