<?php
$words = new MOD_words();

$User = APP_User::login();
if ($User && $User->loggedIn()) {
?>
          <h3>Actions</h3>
          <ul class="linklist">
			<li><a href="trip/show/my"><img src="images/icons/world.png"></a> <a href="trip/show/my"><?=$words->get('TripsShowMy')?></a></li>
            <li><a href="trip/create"><img src="images/icons/world_add.png"></a> <a href="trip/create"><?=$words->get('TripTitle_create')?></a></li>
            <li><a href="blog/create"><img src="images/icons/note_add.png"></a> <a href="blog/create"><?=$words->get('Trip_SubtripsCreate')?></a></li>
		  </ul>
            <?php
                // ###   NEW   To be programmed: show the first visitor, then the second. !! Different div's (c50l, c50r)!  ###
                $next_trips = MOD_trips::get()->RetrieveVisitorsInCityWithAPicture($_SESSION['IdMember']);
                echo $next_trips ? '<h3>'.$words->getFormatted('RecentMemberCity').'</h3>' : '';
                for ($ii = 0; $ii < count($next_trips); $ii++) {
                    $m = $next_trips[$ii];
                    $tripDate = explode(" ",$m->tripDate);
            ?>
                    <p class="floatbox UserpicFloated">
                        <?php echo MOD_layoutbits::PIC_30_30($m->Username,'',$style='float_left framed') ?>
                        <?php echo '<a href="bw/member.php?cid='.$m->Username.'">'.$m->Username.'</a>' ?>
                        <br />
                        <?php echo $m->city; ?> / <?php echo $m->country; ?>
                        <br />
                        <? echo '<a href="blog/'.$m->Username.'/'.$m->tripId.'">'.$words->get('ComingOn').' '.$tripDate[0].'</a>'; ?>
                    </p>
            <?php 
                }
            ?>
<?php } else { ?>
          <h3>Your own trip!</h3>
          <p>Trips are a great way to keep track of your <b>memories</b>, <b>share stories</b> and <b>pictures</b> and show others where you are/were or will be.</p>
          <p>Plan a trip and <b>make arrangements with your hosts beforehand</b>. Show trips to people outside of BeWelcome or set them to be hidden for the public. Go sign up, to create a new trip yourself.</p>
<?php } ?>

<?php 
/*
          <h3>Favourite trips</h3>
          <ul class="linklist">
			<li><a href="trip/show/my">User2</a></li>
			<li><a href="trip/create">User88</a></li>
            <li><a href="blog/create">User27</a></li>
		  </ul>
          
          <h3>Users with great galleries</h3>
          <ul class="linklist">
			<li><a href="trip/show/my">User1</a></li>
			<li><a href="trip/create">User1</a></li>
            <li>User2</li>
		  </ul>
*/
?>
