<?php
if (class_exists('Blog'))
    $Blog = new BlogController;
else
    $Blog = false;
$User = new User;
$callbackId = $User->settingsProcess();
$avCallbackId = $User->avatarProcess();
$vars =& PPostHandler::getVars($callbackId);
$errors   = isset($vars['errors']) ? $vars['errors'] : array();
$messages = isset($vars['messages']) ? $vars['messages'] : array();

$settingsText = array();
$errorText = array();
$messageText = array();
$i18n = new MOD_i18n('apps/user/settings.php');
$settingsText = $i18n->getText('settingsText');
$errorText = $i18n->getText('errorText');
$messageText = $i18n->getText('messageText');

if (!$User = APP_User::login()) {
    echo '<span class="error">'.$errorText['not_logged_in'].'</span>';
    return;
}
?>
<h2><?=$settingsText['title']?></h2>
<?php
foreach ($messages as $msg) {
	if (array_key_exists($msg, $messageText))
        echo '<p class="notify">'.$messageText[$msg].'</p>';
}
if (in_array('password_not_updated', $errors)) {
    echo '<p class="error">'.$errorText['password_not_updated'].'</p>';
}
?>
<fieldset id="user-avatar">
    <legend><?=$settingsText['legend_avatar']?></legend>
    <form method="post" action="user/settings" class="def-form" enctype="multipart/form-data" id="usettings-avform">
        <h3><?=$settingsText['title_avatar']?></h3>
        <img src="user/avatar/<?=$User->getHandle()?>" alt="<?=$User->getHandle()?>" id="usettings-avdisplay"/>
        <div class="row">
            <input type="hidden" name="MAX_FILE_SIZE" value="<?=PFunctions::returnBytes(ini_get('upload_max_filesize'))?>"/>
            <input type="hidden" name="<?=$avCallbackId?>" value="1"/>
            <label for="usettings-av"><?=$settingsText['label_avatar']?></label><br/>
            <input type="file" id="usettings-av" name="av"/>
            <p class="desc"><?=$settingsText['desc_avatar']?></p>
        </div>
        <p><input type="submit" value="<?=$settingsText['avatar_submit']?>"/>
    </form>
    <iframe id="usettings-avform-getter" name="usettings-avform-getter" class="hidden"></iframe>
    <script type="text/javascript">//<!--
//var AvImg = new Uploader('usettings-avform', {
//    hideOnUpload:false,
//    oncomplete:function() {window.refresh();},
//    'notify_heading':'h4'
});
//-->
    </script>
</fieldset>
<fieldset id="user-account">
    <legend><?=$settingsText['legend_password']?></legend>
    <form method="post" action="user/settings" class="def-form">
        <h3><?=$settingsText['title_password']?></h3>
        <div class="row">
            <label for="usettings-p"><?=$settingsText['label_password']?></label><br/>
            <input type="password" id="usettings-p" name="p"/><?php
if (in_array('pwlength', $errors)) {
	echo '<span class="error">'.$errorText['pwlength'].'</span>';
}
?>
            <p class="desc"><?=$settingsText['description_password']?></p>
        </div>
        <div class="row">
            <label for="usettings-pc"><?=$settingsText['label_password_confirm']?></label><br/>
            <input type="password" id="usettings-pc" name="pc"/><?php
if (in_array('pwc', $errors)) {
    echo '<span class="error">'.$errorText['pwc'].'</span>';
}
if (in_array('pwmismatch', $errors)) {
    echo '<span class="error">'.$errorText['pwmismatch'].'</span>';
}
?>
            <p class="desc"><?=$settingsText['description_password_confirm']?></p>
        </div>
        <p>
            <input type="hidden" name="<?=$callbackId?>" value="1"/>
            <input type="submit" value="<?=$settingsText['submit_save']?>"/>
        </p>
    </form>
</fieldset>
    <form method="post" action="user/settings" class="def-form" id="locationform">
<fieldset id="user-profile">
    <legend><?=$settingsText['legend_profile']?></legend>
        <h3><?=$settingsText['title_location']?></h3>
        <div class="row"><?=$settingsText['current_location']?><br />
        <?php
    if ($location && $location->location) {
        echo $location->location;
        echo ', ';
        echo $location->country;
        echo ' <img src="images/icons/flags/'.strtolower($location->code).'.png" alt="">';
    } else {
        echo '&mdash;';
    }
?>
        </div>
        <div class="row">
    <label for="create-location"><?=$settingsText['label_location']?></label><br />
    <input type="text" name="create-location" id="create-location" value="" /> <input type="button" id="btn-create-location" value="<?=$settingsText['label_search_location']?>" />
    <?php
if (in_array('location', $errors)) {
    echo '<span class="error">'.$errorText['location'].'</span>';
}
?>
            <p class="desc"><?=$settingsText['description_location']?></p>
    <div id="location-suggestion"></div>
    <div id="spaf_map" style="width:500px; height:400px;"></div> 


        </div>
<?php
$google_conf = PVars::getObj('config_google');
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
       
            map = new GMap2($("spaf_map"));
<?php 
    if (isset($vars['latitude']) && isset($vars['longitude'])) {
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
        <p>
            <input type="hidden" name="<?=$callbackId?>" value="1"/>
            <input type="submit" value="<?=$settingsText['submit_save_location']?>" onclick="$('locationform').submit();" />
        </p>

</fieldset>
    </form>
<?php
if ($Blog)
    $Blog->userSettingsForm();
?>
<script type="text/javascript">//<!--
createFieldsetMenu();
<?php
$request = PRequest::get()->request;
if (!isset($request[2]))
    $request[2] = '';
switch ($request[2]) {
    case 'blog':
        if ($Blog)
            echo 'setFieldsetMenu("blog-settings");';
        break;

	default:
        echo 'setFieldsetMenu("user-avatar");';
        break;
}
?>

BlogSuggest.initialize('locationform');

function eventHandlerFunction(e) {
    SPAF_Maps_load();
    Event.stop(e);
}
Event.observe('user-profile', "click", eventHandlerFunction, false);
//-->
</script>
<?php
PPostHandler::clearVars($callbackId);
?>