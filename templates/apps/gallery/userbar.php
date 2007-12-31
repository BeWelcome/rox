<?php
$words = new MOD_words();
?>

           <h3><?php echo $words->getFormatted('ActionsTitle'); ?></h3>
           <ul class="linklist">

	        <li><a href="gallery/upload"><?php echo $words->getFormatted('GalleryUpload'); ?></a></li>
	        <li><a href="gallery/galleries"><?php echo $words->getFormatted('GalleryAllPhotos'); ?></a></li>
	        <li><a href="gallery/show/user/<?=APP_User::get()->getHandle()?>"><?php echo $words->getFormatted('GalleryUserPhotos'); ?></a></li>
					
           </ul>
		   