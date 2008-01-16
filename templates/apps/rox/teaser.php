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

<div id="teaser" class="clearfix">
<div id="teaser_index" style="background: transparent;"> 

<div id="teaser_l1" class="float_left" style="width: 65%; margin-right: 10px; padding-right: 10px; padding-bottom: 20px;"> 

     <h1><?php echo $words->get('IndexPageSpecial') ?></h1>
	 <h2><a href="http://blogs.bevolunteer.org/"><?php echo $words->get('IndexPageSpecialLink1') ?> <em>&raquo;</em></a>&nbsp;&nbsp; <a href="http://www.bewelcome.org/forums"><?php echo $words->get('IndexPageSpecialLink2') ?> <em>&raquo;</em></a></h2>
</div>

<div id="teaser_r1" class="float_right" style="width: 30%; padding-bottom: 20px; text-align: right;"> 

<h2><a href="http://www.flickr.com/photos/22828233@N05/tags/unconference"><?php echo $words->get('IndexPageSpecial3') ?></a></h2>
<?php
require_once("phpFlickr/phpFlickr.php");
// Create new phpFlickr object
$f = new phpFlickr("ae8eecd853e96099423763f64ed51857");
/*$f->enableCache(
    "db",
    "mysql://[DB User]:[DB Password]@[DB Server]/[DB Name]"
); */

$i = 0;
    // Find the NSID of the username inputted via the form
    $person = $f->people_findByUsername('be.welcome');
    
    // Get the friendly URL of the user's photos
    $photos_url = $f->urls_getUserPhotos($person['id']);
    
    // Get the user's first 36 public photos
    $photos = $f->people_getPublicPhotos($person['id'], NULL, 2);
    
    // Loop through the photos and output the html now
    foreach ((array)$photos['photo'] as $photo) {
        echo "<a href=$photos_url$photo[id]>";
        echo "<img class='framed' style='margin: 5px' border='0' alt='$photo[title]' ".
            "src=" . $f->buildPhotoURL($photo, "Square") . ">";
        echo "</a>";
        $i++;
        // If it reaches the sixth photo, insert a line break
        if ($i % 4 == 0) {
            echo "<br>\n";
        }
    }

?>

</div>
</div>
</div>