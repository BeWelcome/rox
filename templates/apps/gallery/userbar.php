<?php
$userbarText = array();
$i18n = new MOD_i18n('apps/gallery/userbar.php');
$words = new MOD_words();
$userbarText = $i18n->getText('userbarText');
?>

           <h3>Actions</h3>
           <ul class="linklist">

	        <li><a href="gallery/upload"><?php echo $words->getFormatted('GalleryUpload'); ?></a></li>
	        <li><a href="gallery/galleries"><?php echo $words->getFormatted('GalleryAllPhotos'); ?></a></li>
	        <li><a href="gallery/show/user/<?=APP_User::get()->getHandle()?>"><?php echo $words->getFormatted('GalleryUserPhotos'); ?></a></li>
					
           </ul>
		   