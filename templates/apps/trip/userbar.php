<?php
$userbarText = array();
$i18n = new MOD_i18n('apps/trip/userbar.php');
$userbarText = $i18n->getText('userbarText');
?>
          <h3>Actions</h3>
          <ul class="linklist">
			<li><a href="trip/show/my"><?=$userbarText['show_my']?></a></li>
			<li><a href="trip/create"><?=$userbarText['create']?></a></li>
		  </ul>