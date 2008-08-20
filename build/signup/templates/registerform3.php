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
 * REGISTER FORM TEMPLATE
 */
?>

<div id="signuprox">

<form method="post" action="signup/4" name="signup" id="user-register-form">
  <?=$callback_tag ?>
  <input type="hidden" name="javascriptactive" value="false" />

  <?php
        if (in_array('inserror', $vars['errors'])) {
            echo '<p class="error">'.$errors['inserror'].'</p>';
        }
        ?>

  <fieldset id="location">
    <legend><?php echo $words->get('Location'); ?></legend>

<div class="subcolumns">
  <div class="c50l">
    <div class="subcl">
      <!-- Content of left block -->
        
      <div class="float_left">
      <ul style="display:none">
        <li>

          <label for="country"><?php echo $words->get('Country'); ?>*</label><br />
          <?php echo $countries; ?>
          <?php
          if (in_array('SignupErrorProvideCountry', $vars['errors'])) {
              echo '<div class="error">'.$words->get('SignupErrorProvideCountry').'</div>';
          }
          ?>
        </li>
      </ul>
      <!--
        <li>

          <label for="city"><?php echo $words->get('City'); ?>*</label><br />
          <?php echo $city; ?>
          <?php
            if (in_array('SignupErrorProvideCity', $vars['errors'])) {
                echo '<div class="error">'.$words->get('SignupErrorProvideCity').'</div>';
            }
            ?>

          <span class="small"><?php echo $words->get('SignupIdCityDescription '); ?></span>

        </li> 
        -->

      <ul class="floatbox input_float">
        <li>

          <label for="street"><?php echo $words->get('SignupStreetName'); ?>*</label><br />
          <input type="text" id="register-street" name="street" style="float: left" <?php
            echo isset($vars['street']) ? 'value="'.htmlentities($vars['street'], ENT_COMPAT, 'utf-8').'" ' : '';
            ?> />
          <!--
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupStreetNameDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupStreetNameDescription'); ?></span>
          -->
        </li>
        <li class="number">
          <label for="housenumber"><?php echo $words->get('SignupHouseNumber'); ?>*</label><br />
          <input type="text" id="register-housenumber" name="housenumber" style="float: left" <?php
          echo isset($vars['housenumber']) ? 'value="'.htmlentities($vars['housenumber'], ENT_COMPAT, 'utf-8').'" ' : '';
          ?> />
          <!--
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupHouseNumberDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupProvideHouseNumber'); ?></span>
          -->
        </li>

        <li class="number">
          <label for="zip"><?php echo $words->get('SignupZip'); ?></label><br />
          <input type="text" id="zip" name="zip" style="float: left" <?php
            echo isset($vars['zip']) ? 'value="'.htmlentities($vars['zip'], ENT_COMPAT, 'utf-8').'" ' : '';
            ?> />
          <!--
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupZipDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupZipDescriptionShort'); ?></span>
          -->
        </li>
        
         <?php
        if (in_array('SignupErrorProvideStreetName', $vars['errors'])) {
            echo '<div class="error">'.$words->get('SignupErrorProvideStreetName').'</div>';
        }
        ?>
      </ul>
      
      <ul class="floatbox">
        <label for="create-location"><?=$words->get('label_setlocation')?>:</label><br />
        <input type="text" name="create-location" id="create-location" <?php
        echo isset($vars['create-location']) ? 'value="'.htmlentities($vars['create-location'], ENT_COMPAT, 'utf-8').'" ' : '';
        ?>
         /> <input type="button" id="btn-create-location" class="button" value="<?=$words->get('label_search_location')?>" />
        <p class="desc"><?=$words->get('subline_location')?></p>
       </ul>
      </div>
    </div>
  </div>

  <div class="c50r">
    <div class="subcr">
      <!-- Content of right block -->
        <div id="spaf_map" style="width:240px; height:180px; float:right; border: 2px solid #333">
        </div>
    </div>
  </div>
</div>
    
<div id="location-suggestion"></div>
      

<div class="float_left">

</div>      

<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php
    $google_conf = PVars::getObj('config_google');
    if (!$google_conf || !$google_conf->maps_api_key) {
        throw new PException('Google config error!');
    }
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
            Element.setStyle(li, {fontWeight:'',backgroundColor:'#fff',backgroundImage:''});
        });
    }

    function setMap(geonameid, latitude, longitude, zoom, geonamename, countryname, countrycode, admincode) {
        setGeonameIdInForm(geonameid, latitude, longitude, geonamename, countrycode, admincode);
        changeMarker(latitude, longitude, zoom, geonamename+', '+countryname); 
        removeHighlight();
        Element.setStyle($('li_'+geonameid), {fontWeight:'bold',backgroundColor:'#f5f5f5',backgroundImage:'url(images/icons/tick.png)'});
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
    <input type="hidden" name="region" id="adminName1" value="<?php 
            echo isset($vars['admincode']) ? htmlentities($vars['adminName1'], ENT_COMPAT, 'utf-8') : ''; 
        ?>" />
      
  </fieldset>

  <p>
    <input type="submit" value="<?php echo $words->get('SubmitForm'); ?>" class="submit"
    onclick="javascript:document.signup.javascriptactive.value = 'true'; return true;"
    />
  </p>

</form>
</div> <!-- signup -->

<script type="text/javascript">
 Register.initialize('user-register-form');
GeoSuggest.initialize('user-register-form');

 SPAF_Maps_load();
 
function init(){
     //$('country').style.display = 'none';
     //Event.observe('country', 'change', getRegions, false);
     $('btn-create-location').click();
<?php //echo isset($vars['geonameid']) ? '(function() { $(\'li_'.htmlentities($vars['geonameid'], ENT_COMPAT, 'utf-8').'\').click()}).defer();' : ''; ?>
}

// probably not needed anymore
// function getRegions(){
     // var url = 'signup/getregions';
     // var pars = 'country='+escape($F('country'));
     // var target = 'regions';
     // var myAjax = new Ajax.Updater(target, url, {method: 'get', parameters: pars});
// }

Event.observe(window, 'load', init, false);
 
</script>
