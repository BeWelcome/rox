<?php
$words = new MOD_words();
echo '<h2><a href="trip/'.$trip->trip_id.'">'.$trip->trip_name.'</a></h2>';
?>

<div class="trip_author"><?=$words->get('by')?> <a href="members/<?php echo $trip->handle; ?>"><?php echo $trip->handle; ?></a>
    <a href="blog/<?php echo $trip->handle; ?>" title="Read blog by <?php echo $trip->handle; ?>"><img src="images/icons/blog.gif" alt="" /></a>
    <a href="trip/show/<?php echo $trip->handle; ?>" title="Show trips by <?php echo $trip->handle; ?>"><img src="images/icons/world.gif" alt="" /></a>
</div>

<?php
if (isset($trip->trip_descr) && $trip->trip_descr) {
	echo '<p>'.$trip->trip_descr.'</p>';
}

if (isset($trip->trip_text) && $trip->trip_text) {
	echo '<p>'.$trip->trip_text.'</p>';
}

if ($isOwnTrip) {
	echo '<p class="small"><a href="trip/edit/'.$trip->trip_id.'">Edit</a> | <a href="trip/del/'.$trip->trip_id.'">Delete</a></p><p></p>';
}
?>