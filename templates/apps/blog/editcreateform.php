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
if (!$User) {
	echo '<p class="error">'.$errors['not_logged_in'].'</p>';
    return false;
}
?>

<script type="text/javascript">//<!--
tinyMCE.srcMode = '';
tinyMCE.baseURL = http_baseuri+'script/tiny_mce';
tinyMCE.init({
    mode: "exact",
    elements: "create-txt",
    theme: "advanced",
    relative_urls:false,
    convert_urls:false,
    theme_advanced_buttons1 : "bold,italic,underline,strikethrough,link, bullist,separator,justifyleft,justifycenter,justifyfull,bullist,numlist,forecolor,backcolor, charmap",
    theme_advanced_buttons2 : "",
    theme_advanced_buttons3 : "",    
    theme_advanced_toolbar_location: 'top',
    theme_advanced_statusbar_location: 'bottom',
    theme_advanced_resizing: true
    
});
//-->
</script>

<form method="post" action="<?=$actionUrl?>" class="def-form" id="blog-create-form">

<?php
if (in_array('inserror', $vars['errors'])) {
    echo '<p class="error">'.$errors['inserror'].'</p>';
}
if (in_array('upderror', $vars['errors'])) {
    echo '<p class="error">'.$errors['upderror'].'</p>';
}
?>




<fieldset id="blog-text">
<legend><?=$lang['label_text']?></legend>
    <div class="row">
    <label for="create-title"><?=$lang['label_title']?>:</label><br/>
        <input type="text" id="create-title" name="t" class="long" <?php 
        // the title may be set
        echo isset($vars['t']) ? 'value="'.htmlentities($vars['t'], ENT_COMPAT, 'utf-8').'" ' : ''; 
        ?>/>
        <div id="bcreate-title" class="statbtn"></div>
        <?php
        if (in_array('title', $vars['errors'])) {
            echo '<span class="error">'.$errors['title'].'</span>';
        }
        ?>
        <p class="desc"></p>
    </div>
    <div class="row">
        <label for="create-txt"><?=$lang['label_text']?>:</label><br/>
        <textarea id="create-txt" name="txt" rows="10" cols="50"><?php 
        // the content may be set
        echo isset($vars['txt']) ? htmlentities($vars['txt'], ENT_COMPAT, 'utf-8') : ''; 
        ?></textarea>
        <div id="bcreate-c" class="statbtn"></div>
        <?php
        if (in_array('text', $vars['errors'])) {
            echo '<span class="error">'.$errors['text'].'</span>';
        }
        ?>
        <p class="desc"></p>
    </div>
    <p>
        <input type="submit" value="<?=$submitValue?>" class="submit"<?php
        echo ((isset($submitName) && !empty($submitName))?' name="'.$submitName.'"':'');
        ?> />
        <input type="hidden" name="<?php
        // IMPORTANT: callback ID for post data 
        echo $callbackId; ?>" value="1"/>
<?php
if (isset($vars['id']) && $vars['id']) {
?>
        <input type="hidden" name="id" value="<?=(int)$vars['id']?>"/>
<?php
}
?>
    </p>
</fieldset>







<fieldset id="blog-tags"><legend><?=$lang['label_tags']?></legend>
    <div class="row">
        <?php // if(isset($vars['cat'])) print_r($vars['cat']);?>
        <label for="create-cat"><?=$lang['label_categories']?>:</label><br />
        <select id="create-cat" name="cat">
            <option value="">-- <?=$lang['no_category']?> --</option>
        <?php
            foreach ($catIt as $c) {
                echo "<option value=\"".$c->blog_category_id."\" ";
                if ($c->blog_category_id == $vars['cat']) echo ' selected';
                echo ">".htmlentities($c->name, ENT_COMPAT, 'utf-8')."</option>\n";
            }
        ?>
        </select>
        <input type="submit" value="+" class="submit" name="submit_cat_add" />
        <?php
        if (in_array('category', $vars['errors'])) {
            echo '<span class="error">'.$errors['category'].'</span>';
        }
        ?>
        <p class="desc"></p>
    </div>
    <div class="row">
        <label for="create-tags"><?=$lang['label_tags']?>:</label><br />
        <textarea id="create-tags" name="tags" cols="40" rows="5"><?php 
        // the tags may be set
            echo isset($vars['tags']) ? htmlentities($vars['tags'], ENT_COMPAT, 'utf-8') : ''; 
        ?></textarea>
        <div id="suggestion"></div>
        <p class="desc"><?=$lang['subline_tags']?></p>
    </div>
    <p>
        <input type="submit" value="<?=$submitValue?>" class="submit"<?php
        echo ((isset($submitName) && !empty($submitName))?' name="'.$submitName.'"':'');
        ?> />
    </p>
</fieldset>






<fieldset id="blog-trip"><legend><?=$lang['label_trip']?></legend>
    <div class="row">
        <label for="create-sty"><?=$lang['label_startdate']?>:</label><br />
        <input type="text" id="create-sty" name="sty" style="width:3em" <?php 
        echo isset($vars['sty']) ? 'value="'.htmlentities($vars['sty'], ENT_COMPAT, 'utf-8').'" ' : ''; 
        ?> onblur="Cal.setDateSE('create-sty', 'create-stm', 'create-std', false, 'create-eny', 'create-enm', 'create-end', false);" onfocus="Cal.setDateSE('create-sty', 'create-stm', 'create-std', false, 'create-eny', 'create-enm', 'create-end', false);"/>
        <select id="create-stm" name="stm" onblur="Cal.setDateSE('create-sty', 'create-stm', 'create-std', false, 'create-eny', 'create-enm', 'create-end', false);" onfocus="Cal.setDateSE('create-sty', 'create-stm', 'create-std', false, 'create-eny', 'create-enm', 'create-end', false);">
            <option value="">-</option>
            <?php
                foreach ($monthNames as $m=>$name) {
                    echo '<option value="'.$m.'"';
                    if (isset($vars['stm']) && (int)$vars['stm'] == $m) {
                        echo ' selected="selected"';
                    }
                    echo '>'.$name.'</option>';
                }
            ?>
        </select>
        <input type="text" id="create-std" name="std" style="width:2em" <?php 
        echo isset($vars['std']) ? 'value="'.htmlentities($vars['std'], ENT_COMPAT, 'utf-8').'" ' : ''; 
        ?> onblur="Cal.setDateSE('create-sty', 'create-stm', 'create-std', false, 'create-eny', 'create-enm', 'create-end', false);" onfocus="Cal.setDateSE('create-sty', 'create-stm', 'create-std', false, 'create-eny', 'create-enm', 'create-end', false);"/> 
        <a href="#" id="create-stsel" onclick="Cal.aCalTarget('create-sty', 'create-stm', 'create-std');Cal.aCal('create-stsel');return false;">cal</a>
        <?php
        if (in_array('startdate', $vars['errors'])) {
            echo '<span class="error">'.$errors['startdate'].'</span>';
        } elseif (in_array('duration', $vars['errors'])) {
            echo '<span class="error">'.$errors['duration'].'</span>';
        }
        ?>
        <p class="desc"><?=$lang['subline_startdate']?></p>
    </div>

    <div class="row">
        <label for="create-trip"><?=$lang['label_trip']?>:</label><br />
        <select id="create-trip" name="tr">
            <option value="">-- <?=$lang['no_trip']?> --</option>
        <?php
            foreach ($tripIt as $t)
                echo "<option value=\"".$t->trip_id."\"".($t->trip_id == $vars['trip_id_foreign'] ? ' selected="selected"' : '').">".htmlentities($t->trip_name, ENT_COMPAT, 'utf-8')."</option>\n";
        ?>
        </select>
        <?php
        if (in_array('trip', $vars['errors'])) {
            echo '<span class="error">'.$errors['trip'].'</span>';
        }
        ?>
        <p class="desc"></p>
    </div> 
<?php
if ($google_conf && $google_conf->maps_api_key) {
?>
    <div class="row">
    <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php
        echo $google_conf->maps_api_key;

    ?>" type="text/javascript"></script>
         <script type="text/javascript">
         var map = null;
    
    function createMarker(point, descr) {
         var marker = new GMarker(point);
         GEvent.addListener(marker, "click", function() {
            marker.openInfoWindowHtml(descr);
         });
         return marker;
    }

    var loaded = false;
    function SPAF_Maps_load() {
         if (!loaded && GBrowserIsCompatible()) {
       
            map = new GMap2(document.getElementById("spaf_map"));
<?php 
    if (isset($vars['latitude']) && isset($vars['longitude']) && $vars['latitude'] && $vars['longitude']) {
        echo 'map.setCenter(new GLatLng('.htmlentities($vars['latitude'], ENT_COMPAT, 'utf-8').', '.htmlentities($vars['longitude'], ENT_COMPAT, 'utf-8').'), 8);';
        if (isset($vars['geonamename']) && isset($vars['geonamecountry'])) {
            $desc = "'".$vars['geonamename'].", ".$vars['geonamecountry']."'";
            echo 'var marker = new GMarker(new GLatLng('.$vars['latitude'].', '.$vars['longitude'].'), '.$desc.');
                map.addOverlay(marker);
                GEvent.addListener(marker, "click", function() {
                    marker.openInfoWindowHtml('.$desc.');
                });
                marker.openInfoWindowHtml('.$desc.');';
        }
    } else {
        echo 'map.setCenter(new GLatLng(47.3666667, 8.55), 8);';
    } ?>
            map.addControl(new GSmallMapControl());
            map.addControl(new GMapTypeControl());
        }
        loaded = true;
    }

    function changeMarker(lat, lng, zoom, descr) {
        if (!loaded) {
            SPAF_Maps_load();
            loaded = true;
        }
        map.panTo(new GLatLng(lat, lng));
        map.setZoom(zoom);
        map.addOverlay(createMarker(new GLatLng(lat, lng), descr));
    }

    function setGeonameIdInForm(geonameid, latitude, longitude, geonamename, countrycode, admincode) {
        $('geonameid').value = geonameid;
        $('latitude').value = latitude;
        $('longitude').value = longitude;
        $('geonamename').value = geonamename;
        $('geonamecountrycode').value = countrycode;
        $('admincode').value = admincode;    
    }

    function removeHighlight() {
        var lis = $A($('locations').childNodes);
        lis.each(function(li) {
            Element.setStyle(li, {fontWeight:''});
        });
    }

    function setMap(geonameid, latitude, longitude, zoom, geonamename, countryname, countrycode, admincode) {
        setGeonameIdInForm(geonameid, latitude, longitude, geonamename, countrycode, admincode);
        changeMarker(latitude, longitude, zoom, geonamename+', '+countryname); 
        removeHighlight();
        Element.setStyle($('li_'+geonameid), {fontWeight:'bold'});
    }

    window.onunload = GUnload;
    </script>
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
<?php
}
?>
    <label for="create-location"><?=$lang['label_location']?>:</label>
    <input type="text" name="create-location" id="create-location" value="" /> <input type="button" id="btn-create-location" value="<?=$lang['label_search_location']?>" />
    <p class="desc"><?=$lang['subline_location']?></p>
    <div id="location-suggestion"></div>
    <div id="spaf_map" style="width:500px; height:400px;"></div> 
<p>
        <input type="submit" value="<?=$submitValue?>" class="submit"<?php
        echo ((isset($submitName) && !empty($submitName))?' name="'.$submitName.'"':'');
        ?> />
    </p>   
</fieldset>






<fieldset id="blog-settings">
    <legend><?=$lang['legend_settings']?></legend>
    <?php
    if ($User->hasRight('write_sticky@blog')) {
    ?>
        <div class="row">
            <input type="checkbox" id="create-flag-sticky" name="flag-sticky"<?php
            if (isset($vars['flag-sticky']) && (int)$vars['flag-sticky']) {
                echo ' checked="checked"';
            }
            ?>/>
            <label for="create-flag-sticky"> <?=$lang['label_flag_sticky']?></label>
        </div>
    <?php
    }
    ?>
    <label><?=$lang['label_vis']?></label>
    <div class="row">
        <input type="radio" name="vis" value="pub" id="create-vis-pub"<?php
        if (
            (isset($vars['vis']) && $vars['vis'] == 'pub')
            || (!isset($vars['vis']) && (!$defaultVis || ($defaultVis && $defaultVis->valueint == 2)))
        ) {
            echo ' checked="checked"';
        }            
        ?>/> <label for="create-vis-pub"><?=$lang['label_vispublic']?></label>
        <p class="desc"><?=$lang['description_vispublic']?></p>
    </div>
    <div class="row">
        <input type="radio" name="vis" value="prt" id="create-vis-prt"<?php
        if (
            (isset($vars['vis']) && $vars['vis'] == 'prt')
            || (!isset($vars['vis']) && $defaultVis && $defaultVis->valueint == 1)
        ) {
            echo ' checked="checked"';
        }       
        ?>/> <label for="create-vis-prt"><?=$lang['label_visprotected']?></label>
        <p class="desc"><?=$lang['description_visprotected']?></p>
    </div>
    <div class="row">
        <input type="radio" name="vis" value="pri" id="create-vis-pri"<?php
        if (
            (isset($vars['vis']) && $vars['vis'] != 'prt' && $vars['vis'] != 'pub')
            || (!isset($vars['vis']) && $defaultVis && $defaultVis->valueint == 0)
        ) {
            echo ' checked="checked"';
        }            
        ?>/> <label for="create-vis-pri"><?=$lang['label_visprivate']?></label>
        <p class="desc"><?=$lang['description_visprivate']?></p>
    </div>
<p>
        <input type="submit" value="<?=$submitValue?>" class="submit"<?php
        echo ((isset($submitName) && !empty($submitName))?' name="'.$submitName.'"':'');
        ?> />
    </p>
</fieldset>





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
    SPAF_Maps_load();
    Event.stop(e);
}
Event.observe('liblog-trip', "click", eventHandlerFunction, false);

//-->
</script>
