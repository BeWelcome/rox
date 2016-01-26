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

?>
<div id="tour">
    <h3><?php echo $words->get('tour_meet')?></h3>
    
    <h4><?php echo $words->getFormatted('tour_meet_title1')?></h4>
    <p><?php echo $words->getFormatted('tour_meet_text1')?></p>

    <div style="padding-top: 30px">
    <?php
/*    require_once SCRIPT_BASE . 'build/tour/phpFlickr/phpFlickr.php';
    // Get our phpflickr config
    $phpflickr_conf = PVars::getObj('phpflickr');
    // Create new phpFlickr object
    $f = new phpFlickr($phpflickr_conf->api);

    $f->enableCache(
        "fs",
        $phpflickr_conf->tmpfolder,
        864000
    ); 
    $i = 0;
        // bewelcome-org group: 771581@N21
        // Get the friendly URL of the user's photos
        //$photos_url = $f->urls_getUserPhotos($person['id']);
        
        function getPhotoSizes($photo_id) {
            $photo_id = $photo_id . '';
            $sizes = $f->photos_getSizes($photo_id);
            $return = array();
            if (is_array($sizes)) foreach ($sizes as $k => $size) {
                $return[$size['label']] = $size;
            }
            return $return;
        }

        // Get the groups's first 20 public photos
        $photos = $f->groups_pools_getPhotos('771581@N21',NULL,NULL,NULL,27);
        $photosFlat = '';
        // Loop through the photos and output the html
        foreach ((array)$photos['photos']['photo'] as $photo) {
            $sizes = $f->photos_getSizes($photo['id']);
            $url = array();
            if (is_array($sizes)) foreach ($sizes as $k => $size) {
                $url[$size['label']] = $size;
            }
            $photosFlat .= "<a href=\"".$url['Medium']['source']."\" class=\"lightview\" rel='gallery[BestOf]'>";
            $photosFlat .=  "<img border=\"0\" alt='$photo[title]' ".
                "src=\"" . $f->buildPhotoURL($photo, "Square") . "\" />";
            $photosFlat .=  "</a>";
            $i++;
        }
        echo $photosFlat;
*/        ?>
    </div>

    <div class="clearfix" style="padding-top: 30px">
        <h4><?php 
        echo $words->getFormatted('tour_meet_title2')?></h4>
        <p><?php echo $words->getFormatted('tour_meet_text2')?></p>
        
<?php
// Get 4 random members with a public profile and show their pictures+username
$Rox = new Rox();
$members = $Rox->getMembersStartpage(12,'random');
$count = count($members);
for ($ii=0;$ii<count($members);$ii++) {
        $m=$members[$ii] ;
?>
            <div class="float_left" style="padding-right: 15px">
                <p class="center">
                    <span class="username"><?php echo '<a href="members/'.$m->Username.'">'.$m->Username.'</a>' ?></span><br />
                    <?php echo MOD_layoutbits::PIC_50_50($m->Username,'',$style='framed') ?><br />
                    <span class="small grey"><?php echo $m->countryname; ?></span>
                </p>
            </div> <!-- float_left -->
<?php } ?>

    </div>
    <a class="button" href="tour/trips" onclick="this.blur();" style="margin-bottom: 20px"><span><?php echo $words->getFormatted('tour_goNext')?> &raquo; <?php echo $words->getFormatted('tour_link_trips')?></span></a>
</div>
