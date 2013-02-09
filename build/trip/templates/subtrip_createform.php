<?php 
/*
 * template content: 
 * shows the add destinations form with destination and options tab
 */
 
$cloudmade_conf = PVars::getObj('cloudmade');

if ($isOwnTrip) {
?>
    <div style="padding: 20px 0" id="destination-form">
    <h3>
    <img src="images/icons/note_add.png" alt="<?=$words->get('Trip_SubtripsCreate')?>"/> <?=$words->get('Trip_SubtripsCreate')?><br />
    </h3>
    <p class="small"><?=$words->get('Trip_SubtripsCreateDesc')?></p>
    </div>
 <input type="hidden" id="cloudmadeApiKeyInput" value="<?php echo ($cloudmade_conf->cloudmade_api_key); ?>"/>

<?php
        if (!isset($vars['errors']) || !is_array($vars['errors'])) {
            $vars['errors'] = array();
        }
        
        if (!isset($request[2]) || $request[2] != 'finish') {
            $actionUrl = implode('/', $request);
            $submitName = '';
            $submitValue = $words->getSilent('BlogCreateSubmit');
        } else if (isset($request[2]) && $request[2] === 'finish') {
            echo '<p>'.$words->get('BlogCreateFinishText')."</p>\n";
            $submitValue = $words->getSilent('BlogCreateSubmit');
            $actionUrl = 'trip/' . $request[1];
        }
/**
 * edit and create form template controller // mostly copied from Blog Application
 * for documentation look at build/blog/editcreateform.php
 * @author: Michael Dettbarn (bw: lupochen)
 */
if (!$member) {
    echo '<p class="error">'.$words->get('BlogErrors_not_logged_in').'</p>';
    return false;
}
?>

<form method="post" action="<?=$actionUrl?>" class="fieldset-menu-form" id="destination-edit-form">

<?php
if (in_array('inserror', $vars['errors'])) {
    echo '<p class="error">'.$words->get('BlogErrors_inserror').'</p>';
}
if (in_array('upderror', $vars['errors'])) {
    echo '<p class="error">'.$words->get('BlogErrors_upderror').'</p>';
}

// Setting variables:
if (!isset($vars['trip_id_foreign']) && isset($trip->trip_id)) $vars['trip_id_foreign'] = $trip->trip_id;
?>

<fieldset id="destination"><legend><?=$words->get('TripDestinationLabelTab')?></legend>
        <div class="subcolumns">

          <div class="c38l" >
            <div class="subcl" >
                
                <div>
                <label for="create-title"><?=$words->get('BlogCreateLabelTitle')?>:</label><br/>
                    <input type="text" id="create-title" name="t" class="long" <?php
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
                                <label for="create-date"><?=$words->get('BlogCreateTrips_LabelStartdate')?>:</label><br />
                <div class="floatbox">
                    <input type="text" id="create-date" name="date" class="date" maxlength="10" <?php
                    echo isset($vars['date']) ? 'value="'.htmlentities($vars['date'], ENT_COMPAT, 'utf-8').'" ' : '';
                    ?> />
                	<script type="text/javascript">
                		/*<[CDATA[*/
                		var datepicker	= new DatePicker({
                		relative	: 'create-date',
                		language	: '<?=isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en'?>',
                		current_date : '', 
                		topOffset   : '25',
                		relativeAppend : true
                		});
                		/*]]>*/
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
                    
                    <input id="create-trip" name="tr" type="hidden" value="<?=$vars['trip_id_foreign'] ? $vars['trip_id_foreign'] : ''?>" />
                    <?php
                    if (in_array('trip', $vars['errors'])) {
                        echo '<span class="error">'.$words->get('BlogErrors_trip').'</span>';
                    }
                    ?>
                    <p class="desc"></p>
                <div class="row">
                    <label for="create-txt"><?=$words->get('BlogCreateLabelText')?>:</label><br/>
                    <textarea id="create-txt" name="txt" rows="11" cols="30"><?php
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
                
            </div> <!-- subcl -->
          </div> <!-- c50l -->
          <div class="c62r" >
            <div class="subcr" >
          <div id="spaf_map" style="width:99%; height:320px; border: 2px solid #333; display:none;"></div>
            </div> <!-- subcr -->
          </div> <!-- c50r -->

        </div> <!-- subcolumns -->
    <div class="row">
<?php

if (isset($vars['latitude']) && isset($vars['longitude']) && $vars['latitude'] && $vars['longitude']) {
	// store latitude and logitude into hidden fields (in order to get the values in registermform3.js)
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
</div>
    <label for="create-location"><?=$words->get('BlogCreateTrips_LabelLocation')?>:</label><br />
    <input type="text" name="create-location" id="create-location" value="" /> <br />
    <input type="button" id="btn-create-location" class="button" value="<?=$words->get('label_search_location')?>" />
    <p class="desc"><?=$words->get('BlogCreateTrips_SublineLocation')?></p>

          <div id="location-suggestion"></div>
</fieldset>

<fieldset id="destination-options">
<legend><?=$words->get('TripDestinationOptionsLabelTab')?></legend>

    <div class="row">
        <label for="create-tags"><?=$words->get('BlogCreateLabelCreateTags')?>:</label><br />
        <textarea id="create-tags" name="tags" cols="40" rows="1"><?php
        // the tags may be set
            echo isset($vars['tags']) ? htmlentities($vars['tags'], ENT_COMPAT, 'utf-8') : '';
        ?></textarea>
        <div id="suggestion"></div>
        <p class="desc"><?=$words->get('BlogCreateLabelSublineTags')?></p>
    </div>

    <?php echo $callback; ?>
<?php
if (isset($vars['id']) && $vars['id']) {
?>
        <input type="hidden" name="id" value="<?=(int)$vars['id']?>"/>
<?php
}
?>

    <legend><?=$words->get('BlogCreate_LabelSettings')?></legend>

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
</fieldset>

    <p class="row">
        <input type="submit" value="<?=$submitValue?>" class="submit"<?php
        echo ((isset($submitName) && !empty($submitName))?' name="'.$submitName.'"':'');
        ?> />
    </p>



</form>
<?php    }?>

<script type="text/javascript">//<!--

BlogSuggest.initialize('destination-edit-form');

document.observe("dom:loaded", function() 
{
      var activeFieldset = '<?php if (!empty($vars['activeFieldset'])) { echo $vars['activeFieldset']; } ?>'; // Value inserted by PHP.
      if (activeFieldset == '') {
        var defaultFieldset = 'destination';
        // Trim leading hashbang
        var hashValue = document.location.hash.replace('#!', '');
        if (hashValue == '') {
          activeFieldset = defaultFieldset;
        } else {
          /* This allows URLs like "/editmyprofile#!profileaccommodation",
           * which opens the "Accommodation" form tab after loading the page.
           * The hashbang value needs to match the ID of the fieldset that
           * is to be opened.
           */
          var tab = document.getElementById(hashValue);
          if (tab != null && tab.tagName.toLowerCase() == 'fieldset') {
            activeFieldset = hashValue;
          } else {
            activeFieldset = defaultFieldset;
          }
        }
      }
      new FieldsetMenu('destination-edit-form', {active: activeFieldset});
});

//-->
</script>
<script type="text/javascript">//<!--
jQuery(function() {
    $('spaf_map').style.display = 'block';
    initOsmMapBlogEdit();
});

//-->
</script>
