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
    <p><strong>Lorem Ipsum</strong> is simply dummy text of the printing and typesetting industry. <i>Lorem Ipsum has been the industry's standard dummy</i> text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>
    <p style="font-family: Lato">It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. <i>It was popularised in the 1960s with the release of Letraset</i> sheets containing Lorem Ipsum passages, and more recently</p>
    <p>with desktop publishing software like Aldus PageMaker <i style="font-family: Lato">including versions of</i> Lorem Ipsum.</p>
<?php echo $words->flushBuffer();

