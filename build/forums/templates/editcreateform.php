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
<script type="text/javascript" src="script/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">//<!--
tinyMCE.srcMode = '';
tinyMCE.baseURL = http_baseuri+'script/tiny_mce';
tinyMCE.init({
    theme_advanced_resize_horizontal : false,
    mode: "exact",
    elements: "topic_text",
    content_css : "styles/css/minimal/screen/content_minimal.css",
    theme: "advanced",
    relative_urls:false,
    convert_urls:false,
    theme_advanced_buttons1: "bold,italic,underline,strikethrough,bullist,separator,forecolor,backcolor,charmap,link,image",
    theme_advanced_buttons2: "",
    theme_advanced_buttons3: "",
    theme_advanced_toolbar_location: 'top',
    theme_advanced_statusbar_location: 'bottom',
    theme_advanced_resizing: true
});
//-->
</script>
<h2>
<?php
if ($navichain_items = $boards->getNaviChain()) {
    $navichain = '<span class="forumsboardnavichain">';
    foreach ($navichain_items as $link => $title) {
        $navichain .= '<a href="'.$link.'">'.$title.'</a> :: ';
    }
    $navichain .= '<a href="'.$boards->getBoardLink().'">'.$boards->getBoardName().'</a><br /></span>';
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
        echo $words->getFormatted("forum_reply_title").' &quot;'.$topic->topicinfo->title.'&quot;';
    }
}
?></h2>

<form method="post"  onsubmit="return check_SelectedLanguage();" action="<?php echo $uri; ?>" name="editform" class="fieldset_toggles" id="forumsform">
    <input type="hidden" name="<?php echo $callbackId; ?>" value="1" />

		<div class="row">
            <div class="float_right" style="width: 30%;">
                <?php echo $words->getFormatted("forum_ChooseYourLanguage") ?>
			    <select name="IdLanguage" id="IdLanguage"><?php
                    foreach ($LanguageChoices as $Choices) {
                            echo "<option value=\"",$Choices->IdLanguage,"\"" ;
                            if ((isset($AppropriatedLanguage)) and ($AppropriatedLanguage==$Choices->IdLanguage))  {
                               echo " selected='selected'" ;
                            }
                            echo ">",$Choices->Name,"</option>" ;
                    }
                ?></select>
            </div>
            <?php
                if (isset($allow_title) && $allow_title) {

                    if (isset($vars['errors']) && is_array($vars['errors'])) {
                        if (in_array('title', $vars['errors'])) {
                            echo '<div class="row error">'.$words->getFormatted("forum_error_title").'</div>';
                        }
                    }
            ?>
            <div class="float_left" style="width: 65%;">
			<label for="topic_title"><?php echo $words->getFormatted("forum_label_topicTitle"); ?></label><br />
			<input type="text" style="width: 95%;" name="topic_title" size="50" maxlength="200" id="topic_title" value="<?php
			echo isset($vars['topic_title']) ? $words->fTrad($vars['IdTitle']) : '';
			?>" />
			</div>
            <? } ?>
		</div> <!-- row -->

    <div class="row">
        <label for="topic_text"><?php echo $words->getFormatted("forum_label_text"); ?></label><br />
        <textarea name="topic_text" cols="70" rows="15" id="topic_text" class="long">
        <?php
        if (isset($void_string)) {
            echo $void_string ;
        }
        else {
            echo isset($vars['topic_text']) ? $vars['topic_text'] : '';
        }
        ?></textarea>
    </div> <!-- row -->

<?php
    if (isset($allow_title) && $allow_title) {
?>
    <fieldset class="row" id="fpost_tags_and_location_fieldset">
        <legend for="create-tags" onclick="toggleFieldsets('fpost_tags_and_location');"><?php echo $words->getFormatted("forum_label_tags_and_location"); ?></legend>
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
        <p class="small"><?php echo $words->getFormatted("forum_subline_place"); ?></p>
        <div id="dropdowns">
        <?php
            echo $locationDropdowns;
        ?>
        </div>
    </div></div>
    </fieldset> <!-- row -->

    <fieldset class="row" id="fpost_vis_fieldset">
        <legend for="fpost_vis" onclick="toggleFieldsets('fpost_vis');"><?php echo $words->getFormatted("forum_label_visibility"); ?></legend>
        <div id="fpost_vis"><div>
            <p class="small"><?=$words->getFormatted("forum_ChooseVisibilty")?></p>
    <?php	
    		if (!$edit) {
    		    ?>
    			<select name="ThreadVisibility" id="ThreadVisibility">
    			<?php
    			$CurValue=$vars['ThreadVisibility'] ;

    			echo "<option value='Default'" ;
    			echo ">",$words->getFormatted("forum_edit_vis_Default"),"</option>" ;

    			echo "<option value='NoRestriction'" ;
    			if ($CurValue=="NoRestriction") echo " selected" ;
    			echo ">",$words->getFormatted("forum_edit_vis_NoRestriction"),"</option>" ;

    			echo "<option value='MembersOnly'" ;
    			if ($CurValue=="MembersOnly") echo " selected" ;
    			echo ">",$words->getFormatted("forum_edit_vis_MembersOnly"),"</option>" ;

    			echo "<option value='GroupOnly'" ;
    			if ($CurValue=="GroupOnly") echo " selected" ;
    			echo ">",$words->getFormatted("forum_edit_vis_GroupOnly"),"</option>" ;
    			echo "</select>" ;
    		}

        if (isset($vars['errors']) && is_array($vars['errors'])) {
            if (in_array('text', $vars['errors'])) {
                echo '<div class="row error">'.$words->getFormatted("forum_error_post").'</div>';
            }
        }
    if ($groupsforum) { 
        echo '<input type="hidden" name="IdGroup" value="{$groupsforum}">';
    } else {
        echo '<p class="small">'.$words->getFormatted("forum_subline_group").'</p>';
        echo $groupsDropdowns;
    }
    ?>
        </div></div>
    </fieldset>
<? } // End if $allow_title ?>

<?php if (!empty($vars['PostVisibility'])) { ?>
    <fieldset class="row" id="fpost_postvis_fieldset">
        <legend for="PostVisibility" onclick="toggleFieldsets('fpost_postvis');"><?php echo $words->getFormatted("forum_label_lang") ?></legend>
        <div id="fpost_postvis"><div>
			<br /><?php echo $words->getBuffered("forum_ChooseVisibilty") ?>
			<select name="PostVisibility" id="PostVisibility">
			<?php
			$CurValue=$vars['PostVisibility'] ;
//			echo "<option value='Default'" ;
//			echo ">",$words->getFormatted("forum_edit_vis_Default"),"</option>" ;

			echo "<option value='NoRestriction'" ;
			if ($CurValue=="NoRestriction") echo " selected" ;
			echo ">",$words->getFormatted("forum_edit_vis_NoRestriction"),"</option>" ;

			echo "<option value='MembersOnly'" ;
			if ($CurValue=="MembersOnly") echo " selected" ;
			echo ">",$words->getFormatted("forum_edit_vis_MembersOnly"),"</option>" ;

			if ($vars['IdGroup']!=0) {
				echo "<option value='GroupOnly'" ;
				if ($CurValue=="GroupOnly") echo " selected" ;
				echo ">",$words->getFormatted("forum_edit_vis_GroupOnly"),"</option>" ;
			}

		?>
		</select>
		<br />

		</div></div>
    </fieldset> <!-- row -->
<?php } // end if (!empty($vars['PostVisibility'])) ?>
    
    <fieldset class="row" id="fpost_note_fieldset">
        <legend for="NotifyMe" onclick="toggleFieldsets('fpost_note');"><?php echo $words->getFormatted("forum_Notify") ?></legend>
        <div id="fpost_note"><div>
                <input type="checkbox" name="NotifyMe" id="NotifyMe" <?php echo $notifymecheck?>>
                <label for="NotifyMe"><?php echo $words->getFormatted("forum_NotifyMeForThisThread") ?></label>
		</div></div>
    </fieldset> <!-- row -->
    

    
    <div class="row">
        <input type="submit" value="<?php
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

         ?>" />
    </div> <!-- row -->

</form>

<script type="text/javascript">
    function updateContinent() {
        var urlbit = 'k'+$('d_continent').value;
        update(urlbit);
    }

    function updateCountry() {
        var urlbit = 'k'+$('d_continent').value+'/c'+$('d_country').value;
        update(urlbit);
    }

    function updateAdmincode() {
        var urlbit = 'k'+$('d_continent').value+'/c'+$('d_country').value+'/a'+$('d_admin').value;
        update(urlbit);
    }

    function updateGeonames() {
        var urlbit = 'k'+$('d_continent').value+'/c'+$('d_country').value+'/a'+$('d_admin').value+'/g'+$('d_geoname').value;
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

        var url = http_baseuri+'forums/locationDropdowns/'+urlbit
        new Ajax.Request(url,
        {
            method:'get',
            onSuccess: function(req) {
                updateDropdowns(req.responseText);
            }
        });
    }

    function updateDropdowns(text) {
        Element.update('dropdowns', text);
    }

    function toggleFieldsets(el_name, instantly) {
        if (instantly) $(el_name).toggle();
        else Effect.toggle(el_name,'slide',{ duration: 0.2 });
        Element.toggleClassName($(el_name+'_fieldset'), 'collapsed');
    }

    function forumOnload() {
        ForumsSuggest.initialize();
<?php if (isset($allow_title) && $allow_title) { ?>
        toggleFieldsets('fpost_tags_and_location',1);
        toggleFieldsets('fpost_vis',1);
<? } ?>
<?php if (!empty($vars['PostVisibility'])) { ?>
        toggleFieldsets('fpost_postvis',1);
<? } ?>
        toggleFieldsets('fpost_note',1);
        // toggleFieldsets('fpost_lang',1);
    }

document.observe("dom:loaded", forumOnload);

</script>
