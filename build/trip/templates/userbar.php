<?php
$userbarText = array();
$i18n = new MOD_i18n('apps/trip/userbar.php');
$userbarText = $i18n->getText('userbarText');
$words = new MOD_words();

$User = APP_User::login();
if ($User && $User->loggedIn()) {
?>
          <h3>Actions</h3>
          <ul class="linklist">
			<li><a href="trip/show/my"><?=$words->get('TripsShowMy')?></a></li>
			<li><a href="trip/create"><?=$words->get('TripsCreate')?></a></li>
            <li><a href="blog/create"><?=$words->get('BlogsCreateEntry')?></a></li>
		  </ul>
<?php } else { ?>
          <h3>Your own trip!</h3>
          <p>Trips are a great way to keep track of your <b>memories</b>, <b>share stories</b> and <b>pictures</b> and show others where you are/were or will be.</p>
          <p>Plan a trip and <b>make arrangements with your hosts beforehand</b>. Show trips to people outside of BeWelcome or set them to be hidden for the public. Go sign up, to create a new trip yourself.</p>
<?php } ?>

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

