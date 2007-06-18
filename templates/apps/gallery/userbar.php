<?php
$userbarText = array();
$i18n = new MOD_i18n('apps/gallery/userbar.php');
$userbarText = $i18n->getText('userbarText');
?>
<div id="gallery-userbar" class="vert-infobar box">
    <h2><?=$userbarText['title']?></h2>
    <p>
        <a href="gallery/upload"><?=$userbarText['upload']?></a>
         | 
        <a href="gallery/show/galleries"><?=$userbarText['galleries']?></a>
         |
        <a href="gallery/show/user/<?=APP_User::get()->getHandle()?>"><?=$userbarText['pics']?></a>
    </p>
    <div class="clear"></div>
</div>