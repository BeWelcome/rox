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
<h2><?php echo $words->get('Signup'); ?></h2>
<p><?php echo $words->get('SignupIntroduction'); ?></p>

<form method="post" action="signup/register" name="signup" id="user-register-form">
  <?=$callback_tag ?>
  <input type="hidden" name="javascriptactive" value="false" />

  <?php
        if (in_array('inserror', $vars['errors'])) {
            echo '<p class="error">'.$errors['inserror'].'</p>';
        }
        ?>

  <!-- Login Information -->
  <fieldset>
    <legend><?php echo $words->get('LoginInformation'); ?></legend>

    <!-- username -->
        <div class="signup-row">
          <label for="username"><?php echo $words->get('SignupUsername'); ?>* </label>
          <input type="text" id="register-username" name="username" style="float: left" <?php
            echo isset($vars['username']) ? 'value="'.htmlentities($vars['username'], ENT_COMPAT, 'utf-8').'" ' : '';
            ?> />
             <?php
          if (in_array('SignupErrorWrongUsername', $vars['errors'])) {
              echo '<div class="error">'.$words->get('SignupErrorWrongUsername').'</div>';
          }
          if (in_array('SignupErrorUsernameAlreadyTaken', $vars['errors'])) {
              echo '<div class="error"><br/>'.
                  $words->getFormatted('SignupErrorUsernameAlreadyTaken', $vars['username']).
                  '</div>';
          }
          ?>
          <!--
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupUsernameDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupUsernameShortDesc'); ?></span>
          -->
        </div> <!-- signup-row -->

    <!-- password -->
        <div class="signup-row">
          <label for="password"><?php echo $words->get('SignupPassword'); ?>* </label>
          <input type="password" id="register-password" name="password" style="float: left" <?php
          echo isset($vars['password']) ? 'value="'.$vars['password'].'" ' : '';
          ?> />
          <?php
          if (in_array('SignupErrorPasswordCheck', $vars['errors'])) {
              echo '<div class="error">'.$words->get('SignupErrorPasswordCheck').'</div>';
          }
          ?>
          <!--
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupPasswordDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupPasswordChoose'); ?></span>
          -->
       </div> <!-- signup-row -->

    <!-- confirm password -->
        <div class="signup-row">
          <label for="passwordcheck"><?php echo $words->get('SignupCheckPassword'); ?>* </label>
          <input type="password" id="register-passwordcheck" name="passwordcheck" style="float: left" <?php
            echo isset($vars['passwordcheck']) ? 'value="'.$vars['passwordcheck'].'" ' : '';
            ?> />
          <!--
          <span class="small"><?php echo $words->get('SignupPasswordConfirmShortDesc'); ?></span>
          -->
        </div> <!-- signup-row -->

    <!-- email -->
        <div class="signup-row">
          <label for="email"><?php echo $words->get('SignupEmail'); ?>* </label>
          <input type="text" id="register-email" name="email" style="float: left" <?php
          echo isset($vars['email']) ? 'value="'.htmlentities($vars['email'], ENT_COMPAT, 'utf-8').'" ' : '';
          ?> />
          <?php
          if (in_array('SignupErrorInvalidEmail', $vars['errors'])) {
              echo '<div class="error">'.$words->get('SignupErrorInvalidEmail').'</div>';
          }
          ?>
          <!--
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupEmailDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupEmailShortDesc'); ?></span>
          -->
        </div> <!-- signup-row -->

    <!-- confirm email -->
        <div class="signup-row">
          <label for="emailcheck"><?php echo $words->get('SignupEmailCheck'); ?>* </label>
          <input type="text" id="emailcheck" name="emailcheck" <?php
            echo isset($vars['emailcheck']) ? 'value="'.htmlentities($vars['emailcheck'], ENT_COMPAT, 'utf-8').'" ' : '';
            ?> />
            <?php
          if (in_array('SignupErrorEmailCheck', $vars['errors'])) {
              echo '<div class="error">'.$words->get('SignupErrorEmailCheck').'</div>';
          }
          ?>
          <!--
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupEmailDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupRetypeEmailShortDesc'); ?>></span>
          -->
        </div> <!-- signup-row -->
  </fieldset>

  <!-- Personal Information -->
  <fieldset>
    <legend><?php echo $words->get('SignupName'); ?></legend>

    <!-- First Name -->
        <div class="signup-row">
          <label for="firstname"><?php echo $words->get('FirstName'); ?>* </label>
          <input type="text" id="firstname" name="firstname" <?php
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
        <div class="signup-row">
          <label for="secondname"><?php echo $words->get('SignupSecondNameOptional'); ?></label>
          <input type="text" id="secondname" name="secondname" <?php
          echo isset($vars['secondname']) ? 'value="'.htmlentities($vars['secondname'], ENT_COMPAT, 'utf-8').'" ' : '';
          ?> />
          <!--
          <span class="small"><?php echo $words->get('SignupSecondNameShortDesc'); ?></span>
          -->
        </div> <!-- signup-row -->

    <!-- Last Name -->
        <div class="signup-row">
          <label for="lastname"><?php echo $words->get('LastName'); ?>* </label>
          <input type="text" id="lastname" name="lastname" <?php
          echo isset($vars['lastname']) ? 'value="'.htmlentities($vars['lastname'], ENT_COMPAT, 'utf-8').'" ' : '';
          ?>/>
          <!--
          <span class="small"><?php echo $words->get('SignupLastNameShortDesc'); ?></span>
          -->
        </div> <!-- signup-row -->

    <!-- Birthdate -->
        <div class="signup-row">
          <label for="BirthDate"><?php echo $words->get('SignupBirthDate'); ?>*</label>
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
              echo '<div class="error">'.$words->getFormatted('SignupErrorBirthDateToLow',SignupModel::YOUNGEST_MEMBER).'</div>';
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
          <label for="gender"><?php echo $words->get('Gender'); ?>*</label>
          <input class="radio" type="radio" id="gender" name="gender" value="female"<?php
             if (!isset($vars['gender']) || $vars['gender'] == 'female') {
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
  </fieldset>

  <fieldset id="location">
    <legend><?php echo $words->get('Location'); ?></legend>

      <div id="spaf_map" style="width:200px; height:200px;">
      </div>
      
      <div class="float_left">
      <ul>
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
      <ul>
        <li id="regions">
        <!-- This is a palceholder for the ajax-content that we get after choosing a country -->
        </li>
      </ul>
      <ul class="floatbox input_float">
        <li>

          <label for="city"><?php echo $words->get('City'); ?>*</label><br />
          <?php echo $city; ?>
          <?php
            if (in_array('SignupErrorProvideCity', $vars['errors'])) {
                echo '<div class="error">'.$words->get('SignupErrorProvideCity').'</div>';
            }
            ?>
          <!--
          <span class="small"><?php echo $words->get('SignupIdCityDescription '); ?></span>
          -->
        </li>
        <li class="number">
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

      <ul class="floatbox input_float">
        <li>

          <label for="street"><?php echo $words->get('SignupStreetName'); ?>*</label><br />
          <input type="text" id="street" name="street" <?php
            echo isset($vars['street']) ? 'value="'.htmlentities($vars['street'], ENT_COMPAT, 'utf-8').'" ' : '';
            ?> />
             <?php
            if (in_array('SignupErrorProvideStreetName', $vars['errors'])) {
                echo '<div class="error">'.$words->get('SignupErrorProvideStreetName').'</div>';
            }
            ?>
          <!--
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupStreetNameDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupStreetNameDescription'); ?></span>
          -->
        </li>
        <li class="number">
          <label for="housenumber"><?php echo $words->get('SignupHouseNumber'); ?>*</label><br />
          <input type="text" id="housenumber" name="housenumber" <?php
          echo isset($vars['housenumber']) ? 'value="'.htmlentities($vars['housenumber'], ENT_COMPAT, 'utf-8').'" ' : '';
          ?> />
          <!--
          <a href="#" onclick="return false;" >
          <img src="../images/icons/help.png" alt="?" height="16" width="16" />
          <span><?php echo $words->get('SignupHouseNumberDescription'); ?></span></a><br />
          <span class="small"><?php echo $words->get('SignupProvideHouseNumber'); ?></span>
          -->
        </li>
      </ul>
      </div>

<div class="float_left">
    <label for="create-location"><?=$words->get('label_setlocation')?>:</label>
    <input type="text" name="create-location" id="create-location" value="" /> <input type="button" id="btn-create-location" value="<?=$words->get('label_search_location')?>" />
    <p class="desc"><?=$words->get('subline_location')?></p>
    <div id="location-suggestion"></div>
<p>
        <input type="submit" value="create" class="submit"<?php
        echo ((isset($submitName) && !empty($submitName))?' name="'.$submitName.'"':'');
        ?> />
    </p>
</div>      

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

  </fieldset>

  <!-- feeback -->
  <fieldset>
    <legend><?php echo $words->get('SignupFeedback'); ?></legend>
    <p><?php echo $words->get('SignupFeedbackDescription'); ?></p>
    <textarea name="feedback" rows="10" cols="80"><?php
    echo isset($vars['feedback']) ? htmlentities($vars['feedback'], ENT_COMPAT, 'utf-8') : '';
    ?></textarea>
  </fieldset>

  <!-- terms -->

  <?php


/*
 *  FIXME
 *
    if (GetStrParam("Terms","")!="") echo " checked" ; // if user has already click, we will not bore him again
    echo " />";


  ?>
  <?php echo $words->get('IAgreeWithTerms'); ?></p>
  <?php
  */
    if (in_array('SignupMustacceptTerms', $vars['errors'])) {
        // SignupMustacceptTerms contains unknown placeholder
        echo '<div class="error">'.$words->get('SignupTermsAndConditions').'</div>';
    }
    ?>
  <p class="checkbox"><input type="checkbox" name="terms"
  <?php
	if (isset ($vars["terms"])) echo " checked" ; // if user has already click, we will not bore him again
	echo ">";
  ?>
  <?php echo $words->get('IAgreeWithTerms'); ?></p>
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
}

function getRegions(){
     var url = 'signup/getregions';
     var pars = 'country='+escape($F('country'));
     var target = 'regions';
     var myAjax = new Ajax.Updater(target, url, {method: 'get', parameters: pars});
}

Event.observe(window, 'load', init, false);
 
</script>
