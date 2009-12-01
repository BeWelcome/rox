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

<?php
    if (isset($allow_title) && $allow_title) {

        if (isset($vars['errors']) && is_array($vars['errors'])) {
            if (in_array('title', $vars['errors'])) {
                echo '<div class="row error">'.$words->getFormatted("forum_error_title").'</div>';
            }
        }
?>
		<div class="row">
			<label for="topic_title"><?php echo $words->getFormatted("forum_label_topicTitle"); ?></label><br />
			<input type="text" style="width: 95%" name="topic_title" size="50" maxlength="200" id="topic_title" value="<?php
			echo isset($vars['topic_title']) ? $words->fTrad($vars['IdTitle']) : '';
			?>" />
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
    <fieldset class="row collapsed" id="fpost_tags_fieldset">
        <legend for="create-tags" onclick="toggleFieldsets('fpost_tags');"><?php echo $words->getFormatted("forum_label_tags"); ?></label><br />
        <div id="fpost_tags"><div>
        <p class="small"><?php echo $words->getFormatted("forum_subline_tags"); ?></p>
        <textarea id="create-tags" name="tags" cols="60" rows="2" class="long"
        <?php
// In case we are in edit mode, this field is a read only, tags cannot be edited by members
        if ($edit) {
            echo "\"readonly\"" ;
        }
        ?>><?php
        // the tags may be set
            echo ($tags_with_commas) ? htmlentities($tags_with_commas, ENT_COMPAT, 'utf-8') : '';
        ?></textarea>
        <div id="suggestion"></div>
        </div></div>
    </fieldset> <!-- row -->

    <fieldset class="row collapsed" id="fpost_place_fieldset">
        <legend for="d_continent" onclick="toggleFieldsets('fpost_place');"><?php echo $words->getFormatted("forum_label_place"); ?></label><br />
        <div id="fpost_place"><div>
        <p class="small"><?php echo $words->getFormatted("forum_subline_place"); ?></p>
        <div id="dropdowns">
        <?php
            echo $locationDropdowns;
        ?>
        </div>
    </div></div>
    </fieldset> <!-- row -->

    <fieldset class="row collapsed" id="fpost_vis_fieldset">
        <legend for="fpost_vis" onclick="toggleFieldsets('fpost_vis');"><?php echo $words->getFormatted("forum_ChooseVisibilty"); ?></label><br />
        <div id="fpost_vis"><div>
    <?php	
    		if (!$edit) {
    			echo $words->getBuffered("forum_ChooseVisibilty") ;?>
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

        }

        if (isset($vars['errors']) && is_array($vars['errors'])) {
            if (in_array('text', $vars['errors'])) {
                echo '<div class="row error">'.$words->getFormatted("forum_error_post").'</div>';
            }
        }
    ?>
    </div></div>
    </fieldset>

    <?php if ($groupsforum) { ?>
        <input type="hidden" name="IdGroup" value="<?=$groupsforum?>">
    <?php } else { ?>
        <fieldset class="row collapsed" id="fpost_group_fieldset">
        <legend for="IdGroup" onclick="toggleFieldsets('fpost_group');"><?php echo $words->getFormatted("forum_label_group"); ?></label><br />
        <div id="fpost_group"><div>
        <p class="small"><?php echo $words->getFormatted("forum_subline_group"); ?></p>
        <?php
            echo $groupsDropdowns;
        ?>
        </div></div>
    </fieldset> <!-- row -->    
    <?php } ?>

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

        function toggleFieldsets(el_name) {
            Effect.toggle(el_name,'slide',{ duration: 0.2 });
            Element.toggleClassName($(el_name+'_fieldset'), 'collapsed');
        }
    </script>
<?php
    }
?>

    <fieldset class="row" id="fpost_lang_fieldset">
        <legend for="IdLanguage"onclick="toggleFieldsets('fpost_lang');"><?php echo $words->getFormatted("forum_ChooseYourLanguage") ?>:</label>&nbsp;&nbsp;
        <div id="fpost_lang"><div>
        <select name="IdLanguage" id="IdLanguage"><?php
        // Here propose to choose a language, a javascript routine at the form checking must make it mandatory
            if (!isset($AppropriatedLanguage)) {
               echo "<option value=\"-1\">-</option>";
            }

            foreach ($LanguageChoices as $Choices) {
                    echo "<option value=\"",$Choices->IdLanguage,"\"" ;
                    if ((isset($AppropriatedLanguage)) and ($AppropriatedLanguage==$Choices->IdLanguage))  {
                       echo " selected='selected'" ;
                    }
                    echo ">",$Choices->Name,"</option>" ;
            }
        ?></select>


		<?php if (!empty($vars['PostVisibility'])) { ?>
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
		<?php } // end if (!empty($vars['PostVisibility'])) ?>
		</div></div>
    </fieldset> <!-- row -->
    
    <div class="row">
        <input type="checkbox" name="NotifyMe" id="NotifyMe" <?php echo $notifymecheck?>>
        <label for="NotifyMe"><?php echo $words->getFormatted("forum_NotifyMeForThisThread") ?></label>
    </div> <!-- row -->
    
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
document.observe("dom:loaded", forumOnload);

// purpose here is to force the user to select a language
function check_SelectedLanguage() {
if (document.editform.IdLanguage.value==-1) {
    alert("<?php echo $words->getFormatted("YouMustSelectALanguage") ?>") ;
     document.editform.IdLanguage.focus();
    return(false);
}
}
function forumOnload() {
    ForumsSuggest.initialize();
    //$('fpost_lang').setStyle({ display: 'none' });
    $('fpost_tags').setStyle({ display: 'none' });
    $('fpost_place').setStyle({ display: 'none' });
    $('fpost_group').setStyle({ display: 'none' });
    $('fpost_vis').setStyle({ display: 'none' });
}

</script>
