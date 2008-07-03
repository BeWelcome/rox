<?php
$words = new MOD_words();
$Gallery = new Gallery;
$callbackId = $Gallery->updateGalleryProcess();
$vars = PPostHandler::getVars($callbackId);
$User = new APP_User;
$type = "images";
?>

<h2><?php echo $words->getFormatted('GalleryTitleGalleries'); ?></h2>
<?php
require 'galleries_overview.php';
?>

<h2><?php echo $words->getFormatted('GalleryTitleLatest'); ?></h2>
<p><?php echo $words->getFormatted('galleryTextLatest')?></p>

<form method="post" action="gallery/show/user/<?=$userHandle?>/galleries" name="mod-images" class="def-form">
    <input type="hidden" name="<?=$callbackId?>" value="1"/>
  
<?php
require 'overview.php';
?>
<?php
require 'user_controls.php';
?>

</form>