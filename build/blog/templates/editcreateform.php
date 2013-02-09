<?php
/**
 * edit and create form template controller
 *
 * defined vars:
 * $actionUrl           - the url to be used in the action form.
 * $callbackId          - the callback id to be written into the form.
 * $submitValue         - value attribute of submit button.
 * $submitName          - name attribute of submit button.
 *
 * @package blog
 * @subpackage template
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id$
 */

if (!$member) {
    echo '<p class="error">'.$words->get('BlogErrors_not_logged_in').'</p>';
    return false;
}
$words = new MOD_words();
?>

<script type="text/javascript">//<!--
tinyMCE.srcMode = '';
tinyMCE.baseURL = http_baseuri+'script/tiny_mce';
tinyMCE.init({
    mode: "exact",
    elements: "create-txt",
    plugins : "advimage,preview,fullscreen",
    theme: "advanced",
    content_css : "styles/css/minimal/screen/content_minimal.css?3",
    relative_urls:false,
    convert_urls:false,
    theme_advanced_buttons1 : "bold,italic,underline,strikethrough,separator,bullist,numlist,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,forecolor,backcolor,separator,link,image,charmap,separator,preview,cleanup,code,fullscreen",
    theme_advanced_buttons2 : "",
    theme_advanced_buttons3 : "",
    theme_advanced_toolbar_location: 'top',
    theme_advanced_statusbar_location: 'bottom',
    theme_advanced_resizing: true,
    theme_advanced_resize_horizontal : false,
    plugin_preview_width : "800",
    plugin_preview_height : "600",
});
//-->
</script>

<form method="post" action="<?=$actionUrl?>" class="fieldset-menu-form" id="blog-create-form">

<?php
if (in_array('inserror', $vars['errors'])) {
    echo '<p class="error">'.$words->get('BlogErrors_inserror').'</p>';
}
if (in_array('upderror', $vars['errors'])) {
    echo '<p class="error">'.$words->get('BlogErrors_upderror').'</p>';
}
?>




<fieldset id="blog-text">
<legend><?=$words->get('BlogCreateLabelText')?></legend>
    <div class="row">
    <label for="create-title"><?=$words->get('BlogCreateLabelTitle')?>:</label><br/>
        <input type="text" id="create-title" name="t" class="long" size="65"  <?php
        // the title may be set
        echo isset($vars['t']) ? 'value="'.htmlentities($vars['t'], ENT_COMPAT, 'utf-8').'" ' : '';
        ?>/>
        <div id="bcreate-title" class="statbtn"></div>
        <?php
        if (in_array('title', $vars['errors'])) {
            echo '<span class="error">'.$words->get('BlogErrors_title').'</span>';
        }
        ?>
        <p class="desc"></p>
    </div>
    <div class="row">
        <label for="create-txt"><?=$words->get('BlogCreateLabelText')?>:</label><br/>
        <textarea id="create-txt" name="txt" rows="10" cols="65" class="long" ><?php
        // the content may be set
        echo isset($vars['txt']) ? htmlentities($vars['txt'], ENT_COMPAT, 'utf-8') : '';
        ?></textarea>
        <div id="bcreate-c" class="statbtn"></div>
        <?php
        if (in_array('text', $vars['errors'])) {
            echo '<span class="error">'.$words->get('BlogErrors_text').'</span>';
        }
        ?>
        <p class="desc"></p>
    </div>
    <div class="row">
        <label for="create-cat"><?=$words->get('BlogCreateLabelCategories')?>:</label><br />
        <select id="create-cat" name="cat">
            <option value="">-- <?=$words->get('BlogCreateNoCategories')?> --</option>
        <?php
            foreach ($catIt as $c) {
                echo "<option value=\"".$c->blog_category_id."\" ";
                if (isset($vars['cat']) && $c->blog_category_id == $vars['cat']) echo ' selected';
                echo ">".htmlentities($c->name, ENT_COMPAT, 'utf-8')."</option>\n";
            }
        ?>
        </select>
        <?php
        if (in_array('category', $vars['errors'])) {
            echo '<span class="error">'.$words->get('BlogErrors_category').'</span>';
        }
        ?>
        <p class="desc"></p>
    </div>
    <div class="row">
        <label for="create-tags"><?=$words->get('BlogCreateLabelCreateTags')?>:</label><br />
        <textarea id="create-tags" name="tags" cols="40" rows="1"><?php
        // the tags may be set
            echo isset($vars['tags']) ? htmlentities($vars['tags'], ENT_COMPAT, 'utf-8') : '';
        ?></textarea>
        <div id="suggestion"></div>
        <p class="desc"><?=$words->get('BlogCreateLabelSublineTags')?></p>
    </div>
    <p>
        <input type="submit" value="<?=$submitValue?>" class="submit"<?php
        echo ((isset($submitName) && !empty($submitName))?' name="'.$submitName.'"':'');
        ?> />
<?php
echo $callback;
if (isset($vars['id']) && $vars['id']) {
?>
        <input type="hidden" name="id" value="<?=(int)$vars['id']?>"/>
<?php
}
?>
    </p>
</fieldset>

<fieldset id="blog-trip"><legend><?=$words->get('BlogCreate_LabelTrips')?></legend>
    <?php
    if (isset($vars['latitude']) && isset($vars['longitude']) && $vars['latitude'] && $vars['longitude']) {
	// store latitude and logitude into hidden fields (in order to get the values in blogSmallMapGeoLocation.js)
	echo '<input type="hidden" id="markerLatitude" name="markerLatitude" value="'.$vars['latitude'].'"/>';
	echo '<input type="hidden" id="markerLongitude" name="markerLongitude" value="'.$vars['longitude'].'"/>';
	if (isset($vars['geonamename']) && isset($vars['geonamecountry'])) {
		$markerDescription = "'".$vars['geonamename'].", ".$vars['geonamecountry']."'";
		echo '<input type="hidden" id="markerDescription" name="markerDescription" value="'.$markerDescription.'"/>';
	}
} else {
	echo '<input type="hidden" id="markerLatitude" name="markerLatitude" value="0"/>';
	echo '<input type="hidden" id="markerLongitude" name="markerLongitude" value="0"/>';
}
?>
    <input type="hidden" name="geonameid" id="geonameid" value="<?php
            echo isset($vars['geonameid']) ? htmlentities($vars['geonameid'], ENT_COMPAT, 'utf-8') : '';
        ?>" />
    <input type="hidden" name="latitude" id="latitude" value="<?php
            echo isset($vars['latitude']) ? htmlentities($vars['latitude'], ENT_COMPAT, 'utf-8') : '';
        ?>" />
    <input type="hidden" name="longitude" id="longitude" value="<?php
            echo isset($vars['longitude']) ? htmlentities($vars['longitude'], ENT_COMPAT, 'utf-8') : '';
        ?>" />
    <input type="hidden" name="geonamename" id="geonamename" value="<?php
            echo isset($vars['geonamename']) ? htmlentities($vars['geonamename'], ENT_COMPAT, 'utf-8') : '';
        ?>" />
    <input type="hidden" name="geonamecountrycode" id="geonamecountrycode" value="<?php
            echo isset($vars['geonamecountrycode']) ? htmlentities($vars['geonamecountrycode'], ENT_COMPAT, 'utf-8') : '';
        ?>" />
    <input type="hidden" name="admincode" id="admincode" value="<?php
            echo isset($vars['admincode']) ? htmlentities($vars['admincode'], ENT_COMPAT, 'utf-8') : '';
        ?>" />
<div class="subcolumns">
  <div class="c50l">
    <div class="subcl">
      <!-- Content of left block -->
      <!-- Start join with trip block -->
        <label for="create-trip"><?=$words->get('BlogCreateTrips_LabelTrip')?></label><br />
        <select id="create-trip" name="tr">
        <option value="">-- <?=$words->get('BlogCreateTrips_NoTrip')?> --</option>
        <?php
        foreach ($tripIt as $t)
        echo "<option value=\"".$t->trip_id."\"".($t->trip_id == $vars['trip_id_foreign'] ? ' selected="selected"' : '').">".htmlentities($t->trip_name, ENT_COMPAT, 'utf-8')."</option>\n";
        ?>
        </select>
        <?php
        if (in_array('trip', $vars['errors'])) {
        echo '<span class="error">'.$words->get('BlogErrors_trip').'</span>';
        }
        ?>
        <p class="desc"></p>
        <!-- End join with trip block -->
        <!-- Start trip start date block -->
        <label for="create-sty"><?=$words->get('BlogCreateTrips_LabelStartdate')?>:</label><br />
        <div class="floatbox">
        <input type="text" id="create-date" name="date" class="date" maxlength="10" style="width:9em" <?php
        echo isset($vars['date']) ? 'value="'.htmlentities($vars['date'], ENT_COMPAT, 'utf-8').'" ' : '';
        ?> />
        <script type="text/javascript">
        var datepicker  = new DatePicker({
        relative    : 'create-date',
        language    : '<?=isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en'?>',
        current_date: '', 
        topOffset   : '25',
        relativeAppend : true
        });
        </script>
        </div>
        <?php
        if (in_array('startdate', $vars['errors'])) {
        echo '<span class="error">'.$words->get('BlogErrors_startdate').'</span>';
        } elseif (in_array('duration', $vars['errors'])) {
        echo '<span class="error">'.$words->get('BlogErrors_duration').'</span>';
        }
        ?>
        <p class="desc"><?=$words->get('BlogCreateTrips_SublineStartdate')?></p>
        <!-- End trip start date block -->
        <!-- Start location search block -->
        <label for="create-location"><?=$words->get('BlogCreateTrips_LabelLocation')?>:</label><br />
        <input type="text" name="create-location" id="create-location" value="" /> <input type="button" id="btn-create-location" class="button" value="<?=$words->get('label_search_location')?>" />
        <p class="desc"><?=$words->get('BlogCreateTrips_SublineLocation')?></p>
        <!-- End location search block -->
    </div>
  </div>
  <div class="c50r">
    <div class="subcr">
      <div id="spaf_map" style="width:300px; height:200px;"></div>
    </div>
  </div>
</div>
<div id="location-suggestion"></div>
    <p>
        <input type="submit" value="<?=$submitValue?>" class="submit"<?php
        echo ((isset($submitName) && !empty($submitName))?' name="'.$submitName.'"':'');
        ?> />
    </p>
</fieldset>

<fieldset id="blog-settings">
    <legend><?=$words->get('BlogCreate_LabelSettings')?></legend>
    <?php
    /* removed, referencing user app
    if ($User->hasRight('write_sticky@blog')) {
    ?>
        <div class="row">
            <input type="checkbox" id="create-flag-sticky" name="flag-sticky"<?php
            if (isset($vars['flag-sticky']) && (int)$vars['flag-sticky']) {
                echo ' checked="checked"';
            }
            ?>/>
            <label for="create-flag-sticky"> <?=$words->get('BlogCreateSettings_LabelSticky')?></label>
        </div>
    <?php
    }
    */
    ?>
    <label><?=$words->get('label_vis')?></label>
    <div class="row">
        <input type="radio" name="vis" value="pub" id="create-vis-pub"<?php
        if (
            (isset($vars['vis']) && $vars['vis'] == 'pub')
            || (!isset($vars['vis']) && (!$defaultVis || ($defaultVis && $defaultVis->valueint == 2)))
        ) {
            echo ' checked="checked"';
        }
        ?>/> <label for="create-vis-pub"><?=$words->get('BlogCreateSettings_LabelVispublic')?></label>
        <p class="desc"><?=$words->get('BlogCreateSettings_DescriptionVispublic')?></p>
    </div>
    <div class="row">
        <input type="radio" name="vis" value="prt" id="create-vis-prt"<?php
        if (
            (isset($vars['vis']) && $vars['vis'] == 'prt')
            || (!isset($vars['vis']) && $defaultVis && $defaultVis->valueint == 1)
        ) {
            echo ' checked="checked"';
        }
        ?>/> <label for="create-vis-prt"><?=$words->get('BlogCreateSettings_LabelVisprotected')?></label>
        <p class="desc"><?=$words->get('BlogCreateSettings_DescriptionVisprotected')?></p>
    </div>
    <div class="row">
        <input type="radio" name="vis" value="pri" id="create-vis-pri"<?php
        if (
            (isset($vars['vis']) && $vars['vis'] != 'prt' && $vars['vis'] != 'pub')
            || (!isset($vars['vis']) && $defaultVis && $defaultVis->valueint == 0)
        ) {
            echo ' checked="checked"';
        }
        ?>/> <label for="create-vis-pri"><?=$words->get('BlogCreateSettings_LabelVisprivate')?></label>
        <p class="desc"><?=$words->get('BlogCreateSettings_DescriptionVisprivate')?></p>
    </div>
<p>
        <input type="submit" value="<?=$submitValue?>" class="submit"<?php
        echo ((isset($submitName) && !empty($submitName))?' name="'.$submitName.'"':'');
        ?> />
    </p>
</fieldset>
<?php 

$cloudmade_conf = PVars::getObj('cloudmade');

?>
 <input type="hidden" id="cloudmadeApiKeyInput" value="<?php echo ($cloudmade_conf->cloudmade_api_key); ?>"/>

</form>
<script type="text/javascript">//<!--
new FieldsetMenu('blog-create-form', {<?php
if (in_array('startdate', $vars['errors']) || in_array('duration', $vars['errors'])) {
    echo 'active: "blog-trip"';
} else {
    echo 'active: "blog-text"';
}
?>});
BlogSuggest.initialize('blog-create-form');

function eventHandlerFunction(e) {
    // SPAF_Maps_load();
    initOsmMapBlogEdit();
    Event.stop(e);
}
Event.observe('liblog-trip', "click", eventHandlerFunction, false);

//-->
</script>
