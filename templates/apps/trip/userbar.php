<?php
$userbarText = array();
$i18n = new MOD_i18n('apps/trip/userbar.php');
$userbarText = $i18n->getText('userbarText');
?>
<div id="trip-userbar" class="box">
    <h2><?=$userbarText['title']?></h2>
    <p>
        <a href="trip/show/my"><?=$userbarText['show_my']?></a>
         | 
        <a href="trip/create"><?=$userbarText['create']?></a>
    </p>
</div>