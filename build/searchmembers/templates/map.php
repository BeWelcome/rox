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
    $words = new MOD_words();
    ?>
<?php if ($mapstyle == "mapon") { ?>

<?php 
	$map_conf = PVars::getObj('map');
?>
    <input type="hidden" id="osm-tiles-provider-base-url" value="<?php echo ($map_conf->osm_tiles_provider_base_url); ?>"/>
    <input type="hidden" id="osm-tiles-provider-api-key" value="<?php echo ($map_conf->osm_tiles_provider_api_key); ?>"/>
    
    <div id="map"></div>
    <div id="legend" style="padding: 20px;">
    <?php
    function showLegendAccomodation($imgUrl, $i, $TabAccomodation, $words){
    	$accom = $TabAccomodation[$i-1];
    	echo '<img src="'.$imgUrl.'" title="'. $words->getBuffered("Accomodation_".$accom) .'" alt="'. $words->getBuffered("Accomodation_".$accom) .'" class="legend_icon" /> ';
    	echo '<span id="accomodation'.$i.'">'.$words->getBuffered("Accomodation_".$accom).'</span> ';
    }
    // yes, you are welcome
    showLegendAccomodation('images/icons/gicon1_a.png', 1, $TabAccomodation, $words);
    // never ask
    showLegendAccomodation('images/icons/gicon2_a.png', 2, $TabAccomodation, $words);
    // maybe
    showLegendAccomodation('images/icons/gicon3_a.png', 3, $TabAccomodation, $words);
    
    echo $words->flushBuffer();
    ?>
    </div>
<input onclick="searchByMap(0);" type="button" id="mapBoundariesSearchButton" class="button" value="<?php echo $words->getSilent('FindPeopleSubmitMapSearch'); ?>" ><?=$words->flushBuffer()?>
   <?php } ?>

<?php echo $words->flushBuffer();
