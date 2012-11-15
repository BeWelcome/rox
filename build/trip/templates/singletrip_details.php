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
if (isset($trip->gallery_id_foreign) && $trip->gallery_id_foreign) {
    $gallery = new GalleryModel;
    $statement = $gallery->getLatestItems('',$trip->gallery_id_foreign);
    if ($statement) {
        // if the gallery is NOT empty, go show it
        $p = PFunctions::paginate($statement, 1, $itemsPerPage = 1);
        $statement = $p[0];
        foreach ($statement as $d) { ?>
            <div class="gallery_container float_right" style="margin: -10px 10px 0; height: 170px; width: 150px; padding: 20px; text-align: center;">
            <a href="gallery/show/sets/<?=$trip->gallery_id_foreign; ?>" title="<?=$words->getSilent('TripShowPhotoset')?>"><img class="framed" src="gallery/thumbimg?id=<?=$d->id?>" alt="image"/></a>
            <h4><a href="members/<?=$trip->handle; ?>"><?=$trip->trip_name; ?></a></h4>
            <p><span class="grey small"><?=$words->get('by')?> <?php echo $trip->handle; ?></span></p>
            </div> 
        <?php    } 
    }
}
if (isset($trip->trip_descr) && $trip->trip_descr) {
echo '<p>'.$trip->trip_descr.'</p>';
}
if (isset($trip->trip_text) && $trip->trip_text) {
	echo '<p>'.$trip->trip_text.'</p>';
} ?>
    </div>

