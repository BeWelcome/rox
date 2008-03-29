<?php
$userbarText = array();
$i18n = new MOD_i18n('apps/trip/userbar.php');
$userbarText = $i18n->getText('userbarText');
$words = new MOD_words();
?>
          <h3>Actions</h3>
          <ul class="linklist">
			<li><a href="trip/show/my"><?=$words->get('TripsShowMy')?></a></li>
			<li><a href="trip/create"><?=$words->get('TripsCreate')?></a></li>
            <li><a href="blog/create"><?=$words->get('BlogsCreateEntry')?></a></li>
		  </ul>