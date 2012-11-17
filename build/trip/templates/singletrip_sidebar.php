<?php 
$words = new MOD_words();
$member = $this->_model->getLoggedInMember();
if ($member)
{
?>
          <h3><?=$words->get('TripsSingleTripActionsHeadline')?></h3>
          <ul class="linklist">
			<li><a href="trip/show/<?=$member->Username?>" title="<?=$words->get('TripsShowMy')?>"><img src="images/icons/world.png" style="vertical-align:bottom;" alt="<?=$words->get('TripsShowMy')?>" /></a> <a href="trip/show/<?=$member->Username?>" title="<?=$words->get('TripsShowMy')?>"><?=$words->get('TripsShowMy')?></a></li>
            <li><a href="trip/create" title="<?=$words->get('TripTitle_create')?>"><img src="images/icons/world_add.png" style="vertical-align:bottom;" alt="<?=$words->get('TripTitle_create')?>" /></a> <a href="trip/create" title="<?=$words->get('TripTitle_create')?>"><?=$words->get('TripTitle_create')?></a></li>
    <?php if ($isOwnTrip) { ?>
            <li><a href="trip/edit/<?=$trip->trip_id; ?>"><img src="styles/css/minimal/images/iconsfam/pencil.png" style="vertical-align:bottom;" alt="<?=$words->get('Trip_EditMyTrip')?>" /></a> <a href="trip/edit/<?=$trip->trip_id; ?>" title="<?=$words->get('Trip_EditMyTrip')?>"><?=$words->get('Trip_EditMyTrip')?></a></li>
            <li><a href="trip/del/<?=$trip->trip_id; ?>"><img src="styles/css/minimal/images/iconsfam/delete.png" style="vertical-align:bottom;" alt="<?=$words->get('Trip_DeleteMyTrip')?>" /></a> <a href="trip/del/<?=$trip->trip_id; ?>" title="<?=$words->get('Trip_DeleteMyTrip')?>"><?=$words->get('Trip_DeleteMyTrip')?></a></li>
            <li><a href="trip/<?=$trip->trip_id; ?>/#destination" title="<?=$words->get('Trip_SubtripsCreate')?>"><img src="images/icons/note_add.png" style="vertical-align:bottom;" alt="<?=$words->get('Trip_SubtripsCreate')?>" /></a> <a href="trip/<?=$trip->trip_id; ?>/#destination" title="<?=$words->get('Trip_SubtripsCreate')?>"><?=$words->get('Trip_SubtripsCreate')?></a></li>
<?php    }?>
          </ul>
<?php 
} ?>

