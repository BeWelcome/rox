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

$map_conf = PVars::getObj('map');

?>
<input type="hidden" id="osm-tiles-provider-base-url" value="<?php echo ($map_conf->osm_tiles_provider_base_url); ?>"/>
<input type="hidden" id="osm-tiles-provider-api-key" value="<?php echo ($map_conf->osm_tiles_provider_api_key); ?>"/>

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
            <ol id="locations" class="plain">
                <li style="background-color: #f5f5f5; font-weight: bold; background-image: url(images/icons/tick.png);">
                    <a id="href_4544349"><?= urldecode($vars['geonamename']) ?><br/><?php if (isset($vars['geonamecountrycode']) && isset($vars['countryname']) && isset($vars['admincode'])) { ?>
                    <img alt="<?=$vars['countryname']?>" src="images/icons/flags/<?=strtolower($vars['geonamecountrycode'])?>.png"/>
                    <span class="small"><?= urldecode($vars['countryname']) ?><?php if ($vars['admincode'] <> '') { echo " / " . urldecode($vars['admincode']); } ?></span>
                    <?php } ?>
                    </a>
                </li>
            </ol>
            <?php } ?>
        </div>

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

  <div id="submit_button" style="display: none;">
    <input type="submit" value="<?php echo $words->get('Save Location'); ?>" class="button"
    onclick="javascript:document.signup.javascriptactive.value = 'true'; return true;"
    />
  </div>

</form>
</div> 
        
<?php
    if (isset($vars['latitude']) && isset($vars['longitude']) && $vars['latitude'] && $vars['longitude']) {
        // store latitude and logitude into hidden fields (in order to get the values in registermform3.js)
    	echo '<input type="hidden" id="markerLatitude" name="markerLatitude" value="'.$vars['latitude'].'"/>';
        echo '<input type="hidden" id="markerLongitude" name="markerLongitude" value="'.$vars['longitude'].'"/>';
       	if (isset($vars['geonamename']) && isset($vars['geonamecountry'])) {
            $markerDescription = $vars['geonamename'].", ".$vars['geonamecountry'];
            echo '<input type="hidden" id="markerDescription" name="markerDescription" value="'.$markerDescription.'"/>';
        }
    } else {
        echo '<input type="hidden" id="markerLatitude" name="markerLatitude" value="0"/>';
        echo '<input type="hidden" id="markerLongitude" name="markerLongitude" value="0"/>';
    } 
?>   
<script type="text/javascript">
<!--
jQuery(function() {
	// document loaded
	// enable submit button
	$('submit_button').style.display = 'block';
});
//-->
</script>     
