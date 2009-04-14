<?php
$words = new MOD_words();

// This trip's gallery  

if (isset($trip->gallery_id_foreign) && $trip->gallery_id_foreign) {
    $gallery = new Gallery;
    $statement = $gallery->getLatestItems('',$trip->gallery_id_foreign);
    if ($statement) {
        echo '<h3>Pictures of this trip</h3>';
        // if the gallery is NOT empty, go show it
        $p = PFunctions::paginate($statement, 1, $itemsPerPage = 8);
        $statement = $p[0];
        foreach ($statement as $d) {
        	echo '<a href="gallery/show/image/'.$d->id.'"><img src="gallery/thumbimg?id='.$d->id.'" alt="image" style="height: 50px; width: 50px; padding:2px;"/></a>';
        }
    	echo '<p><a href="gallery/show/galleries/'.$trip->gallery_id_foreign.'" title="'.$words->getSilent('TripShowPhotoset').'"><img src="images/icons/picture.png"> '.$words->get('TripShowPhotoset').'</a></p>';
    } elseif ($isOwnTrip) {
        echo '<p><a href="gallery/show/galleries/'.$trip->gallery_id_foreign.'" title="'.$words->getSilent('Trip_GalleryAddPhotos').'"><img src="images/icons/picture_add.png"> '.$words->get('Trip_GalleryAddPhotos').'</a></p>';
    }
    echo $words->flushBuffer();
}
?>