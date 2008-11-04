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

/*
 * SetLocationPage template
 */ 
 ?>

<div id="signuprox">

<form method="post" name="geo-form-js" id="geo-form-js">
    <input type="hidden" name="javascriptactive" value="false" />
 <?php
        if (in_array('inserror', $vars['errors'])) {
            echo '<p class="error">'.$words->get('inserror').'</p>';
        }
        ?>
<?php
        if (in_array('SignupErrorProvideLocation', $vars['errors'])) {
            echo '<p class="error">'.$words->get('SignupErrorProvideLocation').'</p>';
        }
        ?>

    <fieldset>

        <legend><?php echo $words->get('Location'); ?></legend>

        <div class="floatbox" id="geoselectorjs" style="display: none;" >

            <div class="subcolumns">
              <div class="c50l">
                <div class="subcl">
                  <!-- Content of left block -->

                    <label for="create-location"><?=$words->get('label_setlocation')?>:</label><br />
                    <input type="text" name="create-location" id="create-location" <?php
                    echo isset($vars['create-location']) ? 'value="'.htmlentities($vars['create-location'], ENT_COMPAT, 'utf-8').'" ' : '';
                    ?>
                     />
                     <input id="btn-create-location" class="button" onclick="javascript:return false;" type="submit" value="<?=$words->get('label_search_location')?>" />
                    <p class="desc"><?=$words->get('subline_location')?></p>

                    <div id="location-status"></div>
            <div id="location-suggestion">
            <?php if (isset($vars['geonamename']) && isset($vars['geonameid']) && $vars['geonameid'] != '') { ?>
                <p><b><?=$words->get('Geo_choosenLocation')?>:</b></p>
                <ol id="locations" class="plain">
                    <li style="background-color: #f5f5f5; font-weight: bold; background-image: url(images/icons/tick.png);"><a id="href_4544349">
                    <?=$vars['geonamename']?><br/>
                    <?php if (isset($vars['geonamecountrycode']) && isset($vars['countryname']) && isset($vars['admincode'])) { ?>
                        <img alt="United States" src="images/icons/flags/<?=$vars['geonamecountrycode']?>.png"/>
                        <span class="small"><?=$vars['countryname']?> / <?=$vars['admincode']?></span>
                    <?php } ?>
                    </a></li>
                </ol>
            <?php } ?>
        </div>
                </div>
              </div>

              <div class="c50r">
                <div class="subcr">
                  <!-- Content of right block -->
                    <div id="spaf_map" style="width:240px; height:180px; border: 2px solid #333; display:none;">
                    </div>
                </div>
              </div>
            </div>

            </div> <!-- geoselectorjs -->

    </fieldset>
</form>

    <?php
        $Geo = new GeoController;
        $Geo->layoutkit = $this->layoutkit;
        $Geo->SelectorInclude(array('id' => $vars['id']));
    ?>


<form method="post" action="setlocation" name="signup" id="user-register-form">
  <?=$callback_tag ?>
  <input type="hidden" name="javascriptactive" value="false" />

  <?php
        if (in_array('inserror', $vars['errors'])) {
            echo '<p class="error">'.$errors['inserror'].'</p>';
        }
        ?>

  <fieldset id="location">
  
    <input type="hidden" name="id" id="id" value="<?php
            echo isset($vars['id']) ? htmlentities($vars['id'], ENT_COMPAT, 'utf-8') : '';
        ?>" />
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
    <input type="hidden" name="countryname" id="countryname" value="<?php
            echo isset($vars['countryname']) ? htmlentities($vars['countryname'], ENT_COMPAT, 'utf-8') : '';
        ?>" />
    <input type="hidden" name="geonamecountrycode" id="geonamecountrycode" value="<?php
            echo isset($vars['geonamecountrycode']) ? htmlentities($vars['geonamecountrycode'], ENT_COMPAT, 'utf-8') : '';
        ?>" />
    <input type="hidden" name="admincode" id="admincode" value="<?php
            echo isset($vars['admincode']) ? htmlentities($vars['admincode'], ENT_COMPAT, 'utf-8') : '';
        ?>" />
    <input type="hidden" name="newgeo" id="newgeo" value="0" />

  </fieldset>

  <div id="submit_button" style="display: none;">
    <input type="submit" value="<?php echo $words->get('SubmitForm'); ?>" class="button"
    onclick="javascript:document.signup.javascriptactive.value = 'true'; return true;"
    />
  </div>

</form>
</div> <!-- signup -->

<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php
    $google_conf = PVars::getObj('config_google');
    if (!$google_conf || !$google_conf->maps_api_key) {
        throw new PException('Google config error!');
    }
    echo $google_conf->maps_api_key;

?>" type="text/javascript"></script>
<script src="script/geo_suggest.js" type="text/javascript"></script>
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
            //map.addControl(new GMapTypeControl());
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
        map.clearOverlays();
        map.addOverlay(createMarker(new GLatLng(lat, lng), descr));
    }

    function setGeonameIdInForm(geonameid, latitude, longitude, geonamename, countryname, countrycode, admincode) {
        $('geonameid').value = geonameid;
        $('latitude').value = latitude;
        $('longitude').value = longitude;
        $('geonamename').value = geonamename;
        $('countryname').value = countryname;
        $('geonamecountrycode').value = countrycode;
        $('admincode').value = admincode;
        $('countryname').value = countryname;
        $('newgeo').value = 1;
    }

    function removeHighlight() {
        var lis = $A($('locations').childNodes);
        lis.each(function(li) {
            Element.setStyle(li, {fontWeight:'',backgroundColor:'#fff',backgroundImage:''});
        });
    }

    function setMap(geonameid, latitude, longitude, zoom, geonamename, countryname, countrycode, admincode) {
        setGeonameIdInForm(geonameid, latitude, longitude, geonamename, countryname, countrycode, admincode);
        changeMarker(latitude, longitude, zoom, geonamename+', '+countryname);
        removeHighlight();
        Element.setStyle($('li_'+geonameid), {fontWeight:'bold',backgroundColor:'#f5f5f5',backgroundImage:'url(images/icons/tick.png)'});
    }

    function init(){
        $('submit_button').style.display = 'block';
        $('geoselector').style.display = 'none';
        $('geoselectorjs').style.display = 'block';
        $('spaf_map').style.display = 'block';
        GeoSuggest.initialize('geo-form');
        SPAF_Maps_load();
    }

    window.onunload = GUnload;

    Event.observe(window, 'load', init, false);

</script>
