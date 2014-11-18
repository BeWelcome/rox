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
$map_conf = PVars::getObj('map');
?>
<input type="hidden" id="osm-tiles-provider-base-url" value="<?php echo ($map_conf->osm_tiles_provider_base_url); ?>"/>
<input type="hidden" id="osm-tiles-provider-api-key" value="<?php echo ($map_conf->osm_tiles_provider_api_key); ?>"/>

<div id="signuprox2">
<!-- Custom BeWelcome signup progress bar -->
<div class="progress">
    <div class="progress-bar progress-bar-default" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="4" style="width: 25%;">
        <span class="bw-progress hidden-xs">
            <a href="signup/1" <?=($step =='1') ? 'onclick="$(\'user-register-form\').action = \'signup/1\'; $(\'user-register-form\').submit(); return false"' : '' ?>>1. <?php echo $words->getFormatted('LoginInformation')?></a>
        </span>
        <span class="bw-progress progress-bar-default visible-xs-inline">
            <a href="signup/1" <?=($step =='1') ? 'onclick="$(\'user-register-form\').action = \'signup/1\'; $(\'user-register-form\').submit(); return false"' : '' ?>>Schritt 1.</a>
        </span>
    </div>
    <div class="progress-bar progress-bar-default" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="4" style="width: 25%;">
        <span class="bw-progress hidden-xs">
            <a href="signup/2" <?=($step <='2') ? 'onclick="$(\'user-register-form\').action = \'signup/2\'; $(\'user-register-form\').submit(); return false"' : '' ?>>2. <?php echo $words->getFormatted('SignupName')?></a>
        </span>
        <span class="bw-progress visible-xs-inline">
            <a href="signup/2" <?=($step <='2') ? 'onclick="$(\'user-register-form\').action = \'signup/2\'; $(\'user-register-form\').submit(); return false"' : '' ?>>Schritt 2.</a>
        </span>
    </div>
    <div class="progress-bar progress-bar-default" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="4" style="width: 25%;">
        <span class="bw-progress hidden-xs">
            <a href="signup/3" <?=($step <='3') ? 'onclick="$(\'user-register-form\').action = \'signup/3\'; $(\'user-register-form\').submit(); return false"' : '' ?>>3. <?php echo $words->getFormatted('Location')?></a>
        </span>
        <span class="bw-progress visible-xs-inline">
            <a href="signup/3" <?=($step <='3') ? 'onclick="$(\'user-register-form\').action = \'signup/3\'; $(\'user-register-form\').submit(); return false"' : '' ?>>Schritt 3.</a>
        </span>
    </div>
    <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="4" style="width: 25%;">
        <span class="bw-progress hidden-xs">
            <a href="signup/4" <?=($step <='4') ? 'onclick="$(\'user-register-form\').action = \'signup/4\'; $(\'user-register-form\').submit(); return false"' : '' ?>>4. <?php echo $words->getFormatted('SignupSummary')?></a>
        </span>
        <span class="bw-progress visible-xs-inline">
            <a href="signup/4" <?=($step <='4') ? 'onclick="$(\'user-register-form\').action = \'signup/4\'; $(\'user-register-form\').submit(); return false"' : '' ?>>Schritt 4.</a>
        </span>
    </div>
</div>
<div class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title"><?php echo $words->get('Location'); ?><small class="pull-right">Bitte fülle alle Felder aus.</small></h3> 
  </div>
  <div class="panel-body">
<form method="post" action="<?php echo $baseuri.'signup/3'?>" class="form" name="geo-form-js" id="geo-form-js">
    <div class="subcolumns">
        <div class="c50l">
            <?=$callback_tag ?>
            <input type="hidden" name="javascriptactive" value="false" />
            <div class="clearfix" id="geoselectorjs" style="display: none;" >
                <div class"form-group has-feedback">
                    <div class="input-group">
                    <label for="create-location" class="control-label sr-only"><?=$words->getSilent('label_setlocation')?></label><?php echo $words->flushBuffer(); ?>
                    <input type="text" name="create-location" id="create-location" class="form-control" aria-describedby="create-location-loading-status" placeholder="<?=$words->get('label_setlocation')?>"
                    <?php
                        echo isset($vars['create-location']) ? 'value="'.htmlentities($vars['create-location'], ENT_COMPAT, 'utf-8').'" ' : '';
                    ?>
                    >
                    <span id="location-status" class="form-control-feedback form-control-feedback-location" aria-hidden="true"></span>
                    <span id="create-location-loading-status" class="sr-only">(loading icon)</span>
                    <span class="input-group-btn">
                        <button class="button" type="submit" id="btn-create-location" onclick="javascript:return false;"><?=$words->getSilent('label_search_location')?></button>
                    </span>
                    </div><!-- /input-group -->
                    <span class="help-block"><?=$words->get('subline_location')?></span>
                </div>
            </div>
        </div>
        <div class="c50r">
            <!-- Content of right block -->
            <div id="spaf_map" style="width:100%; height:160px; border: 2px solid #333; display:none;">
            </div>
        </div>
    </div><!-- subcolumns -->
        <div id="location-suggestion">
            <?php if (isset($vars['geonamename']) && isset($vars['geonameid']) && $vars['geonameid'] != '') { ?>
            <p><b><?=$words->get('Geo_choosenLocation')?>:</b></p>
            <ol id="locations" class="plain-signup-location">
                <li style="background-color: #f5f5f5; font-weight: bold; background-image: url(images/icons/tick.png);">
                    <a id="href_4544349"><?= urldecode($vars['geonamename']) ?><br/><?php if (isset($vars['geonamecountrycode']) && isset($vars['countryname']) && isset($vars['admincode'])) { ?>
                    <img alt="<?=$vars['countryname']?>" src="images/icons/flags/<?=strtolower($vars['geonamecountrycode'])?>.png" height="11px;" width="16px;" />
                    <span class="small"><?= urldecode($vars['countryname']) ?> / <?= urldecode($vars['admincode']) ?></span>
                    <?php } ?>
                    </a>
                </li>
            </ol>
            <?php } ?>
        </div>
</form>

    <?php
        $Geo = new GeoController;
        $Geo->layoutkit = $this->layoutkit;
        $Geo->SelectorInclude();
        if (isset($vars['geonameid']) && !isset($_SESSION['GeoVars']['geonameid'])) { }
        isset($mem_redirect->location);
    ?>


<form method="post" action="signup/4" name="signup" id="user-register-form">
  <?=$callback_tag ?>
  <input type="hidden" name="javascriptactive" value="false" />

  <?php
        if (in_array('inserror', $vars['errors'])) {
            echo '<span class="help-block alert alert-danger">'.$errors['inserror'].'</span>';
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

  <div class="clearfix">
        <a href="signup/2" class="button back pull-left" title="<?php echo $words->getSilent('LastStep'); ?>" ><span><?php echo $words->getSilent('Back'); ?></span></a><?php echo $words->flushBuffer(); ?>
        <input type="submit" value="<?php echo $words->getSilent('Save Location'); ?>" class="button pull-right"
        onclick="javascript:document.signup.javascriptactive.value = 'true'; return true;"
        /><?php echo $words->flushBuffer(); ?>
  </div>
</form>
  </div>
</div>
</div>
        
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
