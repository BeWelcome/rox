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
<div id="galleryflickr">
    <h2><?php echo $words->getFormatted('galleryFlickr')?></h2>
    <p><?php echo $words->getFormatted('galleryFlickrUpload')?> <a href="http://www.flickr.com/photos/22828233@N05/"><?php echo $words->getFormatted('galleryFlickrMore')?></a></p>
    <div style="padding: 15px 0">
    <?php
    require_once("phpFlickr/phpFlickr.php");
    // Get our phpflickr config
    $phpflickr_conf = PVars::getObj('phpflickr');
    // Create new phpFlickr object
    $f = new phpFlickr($phpflickr_conf->api);
    $f->enableCache(
                "fs",
                $phpflickr_conf->tmpfolder,
                86400
            );
    $i = 0;
        // Find the NSID of the username inputted via the form
//        $person = $f->people_findByUsername('be.welcome');
        
        // Get the friendly URL of the user's photos
//        $photos_url = $f->urls_getUserPhotos($person['id']);
        
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
        $photos = $f->photos_search(array("tags"=>"be.welcome,bewelcome", "tag_mode"=>"any", "per_page"=>"40"));
//        $photos = $f->tags_getRelated('be.welcome', NULL, 20);
        // Loop through the photos and output the html
        foreach ((array)$photos['photo'] as $photo) {
            $sizes = $f->photos_getSizes($photo['id']);
            $url = array();
            if (is_array($sizes)) foreach ($sizes as $k => $size) {
                $url[$size['label']] = $size;
            }
            echo "<a href=",$url['Small']['source']," class='lightview' rel='gallery[BestOf]'>";
            echo "<img border='0' alt='$photo[title]' ".
                "src=" . $f->buildPhotoURL($photo, "Square") . " style=\"padding:2px;\">";
            echo "</a>";
            $i++;
        }
    ?>
    </div>
        
</div>
