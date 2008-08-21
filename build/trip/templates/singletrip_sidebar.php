<?php
$words = new MOD_words();
$CntSubtrips = 0;
if ($trip_data) 
    $CntSubtrips = count($trip_data[$trip->trip_id]);
?>
<h2><?=$words->get('TripAboutThisTrip')?></h2>

<?php
if (isset($trip->trip_descr) && $trip->trip_descr) {
	echo '<p>'.$trip->trip_descr.'</p>';
}
if (isset($trip->trip_text) && $trip->trip_text) {
	echo '<p>'.$trip->trip_text.'</p>';
}
?>

<p class="small">
<?=$CntSubtrips ?> <?=$words->get('Trip_NumberofSubtrips')?>
</p>

<?php
if ($isOwnTrip) {
	echo '<p class="small"><a href="trip/edit/'.$trip->trip_id.'">Edit</a> | <a href="trip/del/'.$trip->trip_id.'">Delete</a></p><p></p>';
}
?>

<!-- This trip's gallery -->  
<?php
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