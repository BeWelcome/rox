<?php
$userbarText = array();
$i18n = new MOD_i18n('apps/gallery/userbar.php');
$userbarText = $i18n->getText('userbarText');
?>

           <h3>Actions</h3>
           <ul class="linklist">

	        <li><a href="gallery/upload"><?=$userbarText['upload']?></a></li>
	        <li><a href="gallery/show/galleries"><?=$userbarText['galleries']?></a></li>
	        <li><a href="gallery/show/user/<?=APP_User::get()->getHandle()?>"><?=$userbarText['pics']?></a></li>
					
           </ul>
		   