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
 * UpdateMandatorypage template
 */
 ?>

<div id="signuprox">
<h2><?=$ww->UpdateMandatoryPage?></h2>
<form method="post" action="updatemandatory" name="geo-form-js" id="geo-form-js">
    <?=$callback_tag ?>
    <input type="hidden" name="javascriptactive" value="false" />
 <?php
        if (in_array('inserror', $vars['errors'])) {
            echo '<p class="error">'.$errors['inserror'].'</p>';
        }
        ?>

  <!-- Personal Information -->
  <fieldset>
    <legend><?php echo $words->get('SignupName'); ?></legend>

    <div class="subcolumns">
      <div class="c50l">
        <div class="subcl">
          <!-- Content of left block -->

          <!-- First Name -->
              <div class="signup-row clearfix">
                <label for="register-firstname"><?php echo $words->get('FirstName'); ?>* </label>
                <input type="text" id="register-firstname" name="firstname" class="float_left" <?php
                echo isset($vars['firstname']) ? 'value="'.htmlentities($vars['firstname'], ENT_COMPAT, 'utf-8').'" ' : '';
                ?> />
                <?php
                  if (in_array('SignupErrorFullNameRequired', $vars['errors'])) {
                      echo '<div class="error">'.$words->get('SignupErrorFullNameRequired').'</div>';
                  }
                  ?>
                <!--
                <a href="#" onclick="return false;" >
                <img src="../images/icons/help.png" alt="?" height="16" width="16" />
                <span><?php echo $words->get('SignupNameDescription'); ?></span></a><br />
                <span class="small"><?php echo $words->get('SignupFirstNameShortDesc'); ?></span>
                -->
              </div> <!-- signup-row -->

          <!-- Second Name -->
              <div class="signup-row clearfix">
                <label for="secondname"><?php echo $words->get('SignupSecondNameOptional'); ?></label>
                <input type="text" id="secondname" name="secondname" class="float_left" <?php
                echo isset($vars['secondname']) ? 'value="'.htmlentities($vars['secondname'], ENT_COMPAT, 'utf-8').'" ' : '';
                ?> />
                <!--
                <span class="small"><?php echo $words->get('SignupSecondNameShortDesc'); ?></span>
                -->
              </div> <!-- signup-row -->

          <!-- Last Name -->
              <div class="signup-row clearfix">
                <label for="lastname"><?php echo $words->get('LastName'); ?>* </label>
                <input type="text" id="lastname" name="lastname" class="float_left" <?php
                echo isset($vars['lastname']) ? 'value="'.htmlentities($vars['lastname'], ENT_COMPAT, 'utf-8').'" ' : '';
                ?>/>
                <!--
                <span class="small"><?php echo $words->get('SignupLastNameShortDesc'); ?></span>
                -->
              </div> <!-- signup-row -->

        </div>
      </div>

      <div class="c50r">
        <div class="subcr">
          <!-- Content of right block -->

          <!-- Birthdate -->
              <div class="signup-row clearfix">
                <label for="BirthDate"><?php echo $words->get('SignupBirthDate'); ?>*</label><br />
                <select id="BirthDate" name="birthyear">
                  <option value=""><?php echo $words->get('SignupBirthYear'); ?></option>
                  <?php echo $birthYearOptions; ?>
                </select>
                <select name="birthmonth">
                  <option value=""><?php echo $words->get('SignupBirthMonth'); ?></option>
                  <?php for ($i=1; $i<=12; $i++) { ?>
                  <option value="<?php echo $i; ?>"<?php
                  if (isset($vars['birthmonth']) && $vars['birthmonth'] == $i) {
                      echo ' selected="selected"';
                  }
                  ?>><?php echo $i; ?></option>
                  <?php } ?>
                </select>
                <select name="birthday">
                  <option value=""><?php echo $words->get('SignupBirthDay'); ?></option>
                  <?php for ($i=1; $i<=31; $i++) { ?>
                  <option value="<?php echo $i; ?>"<?php
                  if (isset($vars['birthday']) && $vars['birthday'] == $i) {
                      echo ' selected="selected"';
                  }
                  ?>><?php echo $i; ?></option>
                  <?php } ?>
                  </select>
                  <?php
                if (in_array('SignupErrorBirthDate', $vars['errors'])) {
                    echo '<div class="error">'.$words->get('SignupErrorBirthDate').'</div>';
                }
                if (in_array('SignupErrorBirthDateToLow', $vars['errors'])) {
                    echo '<div class="error">'.$words->get('SignupErrorBirthDateToLow').'</div>';
                }
                ?>
                <!--
                <a href="#" onclick="return false;" >
                <img src="../images/icons/help.png" alt="?" height="16" width="16" />
                <span><?php echo $words->get('SignupBirthDateDescription'); ?></span></a><br />
                <span class="small"><?php echo $words->get('SignupBirthDateShape'); ?></span>
                -->
              </div> <!-- signup-row -->

          <!-- Gender -->
              <div class="signup-row">
                <label for="gender"><?php echo $words->get('Gender'); ?>*</label><br />
                <input class="radio" type="radio" id="gender" name="gender" value="female"<?php
                   if (isset($vars['gender']) && $vars['gender'] == 'female') {
                       echo ' checked="checked"';
                    }
                    ?> />
                    <?php echo $words->get('female'); ?>
                    <input class="radio" type="radio" name="gender" value="male"<?php
                    if (isset($vars['gender']) && $vars['gender'] == 'male') {
                        echo ' checked="checked"';
                    }
                    ?> />
                    <?php echo $words->get('male'); ?>
                    <?php if (in_array('SignupErrorProvideGender', $vars['errors'])) {
                        echo '<div class="error">'.$words->get('SignupErrorProvideGender').'</div>';
                            }
                ?>

                <!--
                <a href="#" onclick="return false;" >
                <img src="../images/icons/help.png" alt="?" height="16" width="16" />
                <span><?php echo $words->get('SignupGenderDescription'); ?></span></a><br />
                -->
              </div> <!-- signup-row -->

        </div>
      </div>
    </div>


  </fieldset>

    <fieldset>

        <legend><?php echo $words->get('Location'); ?></legend>

        <div class="clearfix" id="geoselectorjs" style="display: none;" >

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

            <div id="location-suggestion">
            <?php if (isset($vars['geonamename']) && isset($vars['geonameid']) && $vars['geonameid'] != '') { ?>
                <p><b><?=$words->get('Geo_choosenLocation')?>:</b></p>
                <ol class="plain">
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

    </fieldset>

    <?php
        $Geo = new GeoController;
        $Geo->layoutkit = $this->layoutkit;
        $Geo->SelectorInclude();
        if (isset($vars['geonameid']) && !$this->session->has( 'GeoVars[' . $geonameid . ']' )) { }
        isset($mem_redirect->location);
    ?>

  <?=$callback_tag ?>
  <input type="hidden" name="javascriptactive" value="false" />

  <?php
        if (in_array('inserror', $vars['errors'])) {
            echo '<p class="error">'.$errors['inserror'].'</p>';
        }
        ?>

  <fieldset id="location">
      <legend><?php echo $words->get('Address'); ?></legend>

          <ul class="clearfix input_float">
        <li style="float: left">

          <label for="register-street"><?php echo $words->get('SignupStreetName'); ?>*</label><br />
          <input type="text" id="register-street" name="street" <?php
            echo isset($vars['street']) ? 'value="'.htmlentities($vars['street'], ENT_COMPAT, 'utf-8').'" ' : '';
            ?> />
          <!--
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupStreetNameDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupStreetNameDescription'); ?></span>
          -->
        </li>
        <li class="number" style="float: left">
          <label for="register-housenumber"><?php echo $words->get('SignupHouseNumber'); ?>*</label><br />
          <input type="text" id="register-housenumber" name="housenumber" <?php
          echo isset($vars['housenumber']) ? 'value="'.htmlentities($vars['housenumber'], ENT_COMPAT, 'utf-8').'" ' : '';
          ?> />
          <!--
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupHouseNumberDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupProvideHouseNumber'); ?></span>
          -->
        </li>

        <li class="number" style="float: left">
          <label for="zip"><?php echo $words->get('SignupZip'); ?></label><br />
          <input type="text" id="zip" name="zip" <?php
            echo isset($vars['zip']) ? 'value="'.htmlentities($vars['zip'], ENT_COMPAT, 'utf-8').'" ' : '';
            ?> />
          <!--
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupZipDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupZipDescriptionShort'); ?></span>
          -->
        </li>
      </ul>

         <?php
        if (in_array('SignupErrorProvideStreetName', $vars['errors'])) {
            echo '<div class="error">'.$words->get('SignupErrorProvideStreetName').'</div>';
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

  <p>
    <input type="submit" class="button" value="<?php echo $words->get('SubmitForm'); ?>" class="button"
    onclick="javascript:document.signup.javascriptactive.value = 'true'; return true;"
    />
  </p>

</form>
</div> <!-- signup -->

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
        $('geoselector').style.display = 'none';
        $('geoselectorjs').style.display = 'block';
        $('spaf_map').style.display = 'block';
        GeoSuggest.initialize('geo-form-js');
        SPAF_Maps_load();
    }

    window.onunload = GUnload;

    Event.observe(window, 'load', init, false);

</script>
