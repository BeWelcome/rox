<?php
/*
 * template content: 
 * shows the trip author with a picture, trip title, trip text ($trip->trip_descr),
 * number of destinations of the trip and action links (create, edit, delete, add destination)
 */
?>
<div class="float_left"><?=$layoutbits->PIC_50_50($trip->handle)?></div><h2 class="tripname"><?=$trip->trip_name; ?></h2>
        <div class="trip_author"><?=$words->get('by')?> <a href="members/<?php echo $trip->handle; ?>"><?php echo $trip->handle; ?></a>
            <a href="blog/<?php echo $trip->handle; ?>" title="Read blog by <?php echo $trip->handle; ?>"><img src="images/icons/blog.gif" style="vertical-align:bottom;" alt="" /></a>
            <a href="trip/show/<?php echo $trip->handle; ?>" title="Show trips by <?php echo $trip->handle; ?>"><img src="images/icons/world.gif" style="vertical-align:bottom;" alt="" /></a>

<div class="float_right">
<?php
if ($member)
{
?>
          <ul>
            <li class="float_left"><a href="trip/create" title="<?=$words->get('TripTitle_create')?>"><img src="images/icons/world_add.png" style="vertical-align:bottom;" alt="<?=$words->get('TripTitle_create')?>" /></a> <a href="trip/create" title="<?=$words->get('TripTitle_create')?>"><?=$words->get('TripTitle_create')?></a></li>
    <?php if ($member && !$isOwnTrip) { ?>            
            <li class="float_left"><a href="trip/show/<?=$member->Username?>" title="<?=$words->get('TripsShowMy')?>"><img src="images/icons/world.png" style="vertical-align:bottom;" alt="<?=$words->get('TripsShowMy')?>" /></a> <a href="trip/show/<?=$member->Username?>" title="<?=$words->get('TripsShowMy')?>"><?=$words->get('TripsShowMy')?></a></li>
    <?php    }?>
    <?php if ($isOwnTrip) { ?>
            <li class="float_left"><a href="trip/edit/<?=$trip->trip_id; ?>"><img src="styles/css/minimal/images/iconsfam/pencil.png" style="vertical-align:bottom;" alt="<?=$words->get('Trip_EditMyTrip')?>" /></a> <a href="trip/edit/<?=$trip->trip_id; ?>" title="<?=$words->get('Trip_EditMyTrip')?>"><?=$words->get('Trip_EditMyTrip')?></a></li>
            <li class="float_left"><a href="trip/del/<?=$trip->trip_id; ?>"><img src="styles/css/minimal/images/iconsfam/delete.png" style="vertical-align:bottom;" alt="<?=$words->get('Trip_DeleteMyTrip')?>" /></a> <a href="trip/del/<?=$trip->trip_id; ?>" title="<?=$words->get('Trip_DeleteMyTrip')?>"><?=$words->get('Trip_DeleteMyTrip')?></a></li>
            <li class="float_left"><a href="trip/<?=$trip->trip_id; ?>/#destination-form" title="<?=$words->get('Trip_SubtripsCreate')?>"><img src="images/icons/note_add.png" style="vertical-align:bottom;" alt="<?=$words->get('Trip_SubtripsCreate')?>" /></a> <a href="trip/<?=$trip->trip_id; ?>/#destination-form" title="<?=$words->get('Trip_SubtripsCreate')?>"><?=$words->get('Trip_SubtripsCreate')?></a></li>
    <?php    }?>
          </ul>
<?php 
} ?>
</div>
        </div>
<?php
$CntSubtrips = 0;
if ($trip_data) 
    $CntSubtrips = count($trip_data[$trip->trip_id]);

if (isset($trip->trip_descr) && $trip->trip_descr) {
echo '<p class="tripdesc">'.$trip->trip_descr.'</p>';
}
if (isset($trip->trip_text) && $trip->trip_text) {
	echo '<p>'.$trip->trip_text.'</p>';
} ?>
