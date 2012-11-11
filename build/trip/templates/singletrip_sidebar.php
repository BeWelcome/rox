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
            <li><a href="trip/<?=$trip->trip_id; ?>/#destination" title="<?=$words->get('Trip_SubtripsCreate')?>"><img src="images/icons/note_add.png" style="vertical-align:bottom;" alt="<?=$words->get('Trip_SubtripsCreate')?>" /></a> <a href="trip/<?=$trip->trip_id; ?>/#destination" title="<?=$words->get('Trip_SubtripsCreate')?>"><?=$words->get('Trip_SubtripsCreate')?></a></li>
        <?php if (isset($trip->gallery_id_foreign) && $trip->gallery_id_foreign) {
            echo '<li><a href="gallery/show/galleries/'.$trip->gallery_id_foreign.'" title="'.$words->get('Trip_GalleryAddPhotos').'"><img src="images/icons/picture_add.png" style="vertical-align:bottom;" alt="' . $words->get('TripTitle_create') . '"></a> <a href="gallery/show/galleries/'.$trip->gallery_id_foreign.'" title="'.$words->get('Trip_GalleryAddPhotos').'">'.$words->get('Trip_GalleryAddPhotos').'</a><li>';
        }
    }?>
          </ul>
<?php 
} ?>

