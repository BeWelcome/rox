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

<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo $google_conf->maps_api_key; ?>" type="text/javascript"></script>
*/
    $words = new MOD_words();
?>

<?php if ($mapstyle == "mapon") { ?>
    <div id="map" style="height:400px;width:100%; border: 1px solid #888; padding: 1px;">
    </div>
    <div id="legend" style="padding: 20px;">
    <?php
    for($i=1;$i<=3;$i++) {
        $accom = $TabAccomodation[$i-1];
        echo '<img src="images/icons/gicon'.$i.'_a.png" title="'. $words->getBuffered("Accomodation_".$accom) .'" alt="'. $words->getBuffered("Accomodation_".$accom) .'" class="legend_icon" /> ';
        echo '<span>'.$words->getBuffered("Accomodation_".$accom).'</span> ';
    }
    echo $words->flushBuffer();
    ?>
    </div>
<?php } ?>

<?php echo $words->flushBuffer();
