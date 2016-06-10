<?php
$words = new MOD_words($this->getSession());
$member = $this->member;
if ($member)
{
?>
          <h3><?=$words->get('TripsSingleTripActionsHeadline')?></h3>
          <ul class="linklist">
			<li><a href="trip/show/<?=$member->Username?>" title="<?=$words->getSilent('TripsShowMy')?>"><img src="images/icons/world.png" style="vertical-align:bottom;" alt="<?=$words->getSilent('TripsShowMy')?>" /></a> <a href="trip/show/<?=$member->Username?>" title="<?=$words->getSilent('TripsShowMy')?>"><?=$words->getSilent('TripsShowMy')?></a><?php echo $words->flushBuffer(); ?></li>
            <li><a href="trip/create" title="<?=$words->getSilent('TripTitle_create')?>"><img src="images/icons/world_add.png" style="vertical-align:bottom;" alt="<?=$words->getSilent('TripTitle_create')?>" /></a> <a href="trip/create" title="<?=$words->getSilent('TripTitle_create')?>"><?=$words->getSilent('TripTitle_create')?></a><?php echo $words->flushBuffer(); ?></li>
          </ul>

            <?php
                // ###   NEW   To be programmed: show the first visitor, then the second. !! Different div's (c50l, c50r)!  ###
                $next_trips = MOD_trips::get()->RetrieveVisitorsInCityWithAPicture($_SESSION['IdMember']);
                echo $next_trips ? '<h3>'.$words->getFormatted('RecentMemberCity').'</h3>' : '';
                for ($ii = 0; $ii < count($next_trips); $ii++) {
                    $m = $next_trips[$ii];
                    $tripDate = explode(" ",$m->tripDate);
            ?>
                    <p class="clearfix UserpicFloated">
                        <?php echo MOD_layoutbits::PIC_30_30($m->Username,'',$style='float_left framed') ?><?php echo $words->flushBuffer(); ?>
                        <?php echo '<a href="members/'.$m->Username.'">'.$m->Username.'</a>' ?>
                        <br />
                        <?php echo $m->city; ?> / <?php echo $m->country; ?>
                        <br />
                        <? echo '<a href="blog/'.$m->Username.'/'.$m->tripId.'">'.$words->get('ComingOn').' '.$tripDate[0].'</a>'; ?>
                    </p>
            <?php 
                }
            ?>
<?php }?>
