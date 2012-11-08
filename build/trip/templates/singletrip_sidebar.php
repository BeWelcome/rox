<?php 
$words = new MOD_words();
$member = $this->_model->getLoggedInMember();
if ($member)
{
?>
          <h3><?=$words->get('TripsSingleTripActionsHeadline')?></h3>
          <ul class="linklist">
			<li><a href="trip/show/<?=$member->Username?>" title="<?=$words->get('TripsShowMy')?>"><img src="images/icons/world.png" alt="<?=$words->get('TripsShowMy')?>" /></a> <a href="trip/show/<?=$member->Username?>"><?=$words->get('TripsShowMy')?></a></li>
            <li><a href="trip/create" title="<?=$words->get('TripsShowMy')?>"><img src="images/icons/world_add.png" alt="<?=$words->get('TripTitle_create')?>" /></a> <a href="trip/create"><?=$words->get('TripTitle_create')?></a></li>
<?php if (isset($trip->gallery_id_foreign) && $trip->gallery_id_foreign) {
        echo '<li><a href="gallery/show/galleries/'.$trip->gallery_id_foreign.'" title="'.$words->get('Trip_GalleryAddPhotos').'"><img src="images/icons/picture_add.png"> '.$words->get('Trip_GalleryAddPhotos').'</a></li>';
}?>
          </ul>
<?php } ?>

