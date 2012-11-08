<?php
$words = new MOD_words();
$layoutbits = new MOD_layoutbits();
?>
<div class="float_left"><?=$layoutbits->PIC_50_50($trip->handle)?></div><h2 class="tripname"><?=$trip->trip_name; ?></h2>
        <div class="trip_author"><?=$words->get('by')?> <a href="members/<?php echo $trip->handle; ?>"><?php echo $trip->handle; ?></a>
            <a href="blog/<?php echo $trip->handle; ?>" title="Read blog by <?php echo $trip->handle; ?>"><img src="images/icons/blog.gif" alt="" /></a>
            <a href="trip/show/<?php echo $trip->handle; ?>" title="Show trips by <?php echo $trip->handle; ?>"><img src="images/icons/world.gif" alt="" /></a>
        </div>

    
<?php
$CntSubtrips = 0;
if ($trip_data) 
    $CntSubtrips = count($trip_data[$trip->trip_id]);
?>

<?php
if (isset($trip->trip_descr) && $trip->trip_descr) {
	echo '<p>'.$trip->trip_descr.'</p>';
}
if (isset($trip->trip_text) && $trip->trip_text) {
	echo '<p>'.$trip->trip_text.'</p>';
}
if (isset($trip->gallery_id_foreign) && $trip->gallery_id_foreign) {
    $gallery = new GalleryModel;
    $statement = $gallery->getLatestItems('',$trip->gallery_id_foreign);
    if ($statement) {
        // if the gallery is NOT empty, go show it
        $p = PFunctions::paginate($statement, 1, $itemsPerPage = 6);
        $statement = $p[0];
        echo '<p>';
        foreach ($statement as $d) {
        	echo '<a href="gallery/show/image/'.$d->id.'"><img src="gallery/thumbimg?id='.$d->id.'" alt="image" style="height: 100px; width: 100px; margin-right:5px;" class="framed"/></a>';
        }
        echo'</p>';
    	echo '<p><a href="gallery/show/galleries/'.$trip->gallery_id_foreign.'" title="'.$words->getSilent('TripShowPhotoset').'"><img src="images/icons/picture.png"> '.$words->get('TripShowPhotoset').'</a></p>';
    } 
    echo $words->flushBuffer();
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
    </div>

