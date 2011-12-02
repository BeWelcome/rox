<?php
$words = new MOD_words();
$layoutbits = new MOD_layoutbits();
?>

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

