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
<div id="tour">
    <h1><?php echo $words->get('tour_meet')?></h1>
    
    <h2><?php echo $words->getFormatted('tour_meet_title1')?></h2>
    <p><?php echo $words->getFormatted('tour_meet_text1')?></p>

    <div style="padding-top: 30px">
    <?php
    require_once("phpFlickr/phpFlickr.php");
    // Create new phpFlickr object
    $f = new phpFlickr("cbc166b80eb3ab04ad27845703752024");
    //$f->enableCache(
    //    "db",
    //    "mysql://root:@localhost/bewelcometest3"
    //); 
    $i = 0;
        // Find the NSID of the username inputted via the form
        $person = $f->people_findByUsername('be.welcome');
        
        // Get the friendly URL of the user's photos
        $photos_url = $f->urls_getUserPhotos($person['id']);
        
        function getPhotoSizes($photo_id) {
            $photo_id = $photo_id . '';
            $sizes = $f->photos_getSizes($photo_id);
            $return = array();
            if (is_array($sizes)) foreach ($sizes as $k => $size) {
                $return[$size['label']] = $size;
            }
            return $return;
        }

        // Get the user's first 12 public photos
        //$photos = $f->people_getPublicPhotos($person['id'], NULL, 18);
        // Get the photosets's first 20 public photos
        $photos = $f->photosets_getPhotos('72157603941918976', NULL, 20);
        // Loop through the photos and output the html
        foreach ((array)$photos['photo'] as $photo) {
            $sizes = $f->photos_getSizes($photo['id']);
            $url = array();
            if (is_array($sizes)) foreach ($sizes as $k => $size) {
                $url[$size['label']] = $size;
            }
            echo "<a href=",$url['Medium']['source']," class='lightview' rel='gallery[BestOf]'>";
            echo "<img border='0' alt='$photo[title]' ".
                "src=" . $f->buildPhotoURL($photo, "Square") . ">";
            echo "</a>";
            $i++;
        }
    ?>
    </div>

    <div style="padding-top: 30px">
        <h2><?php echo $words->getFormatted('tour_meet_title2')?></h2>
        <p><?php echo $words->getFormatted('tour_meet_text2')?></p>
    </div>
        
</div>
