<?php
$words = new MOD_words();
$Gallery = new Gallery;
$callbackId = $Gallery->updateGalleryProcess();
$vars = PPostHandler::getVars($callbackId);
$User = new APP_User;
$type = "images";
?>
<?
if (isset($vars['errors']) && in_array('gallery', $vars['errors'])) {
    echo '<span class="error">'.$words->get('GalleryErrorsPhotoset').'</span>';
}
?>
<h2><a href="gallery/show/user/<?=$userHandle?>/sets" alt="GalleryTitleSets"><?php echo $words->getFormatted('GalleryTitleSets'); ?></a></h2>
<?php
$itemsPerPage = 3;
require 'galleries_overview.php';
?>

<h2><a href="gallery/show/user/<?=$userHandle?>/pictures" alt="GalleryTitleLatest"><?php echo $words->getFormatted('GalleryTitleLatest'); ?></a></h2>

<form method="post" action="gallery/show/user/<?=$userHandle?>/sets" name="mod-images" class="def-form">
    <input type="hidden" name="<?=$callbackId?>" value="1"/>
  
<?php
$itemsPerPage = 5;
require 'overview.php';
?>
<?php
require 'user_controls.php';
?>

</form>