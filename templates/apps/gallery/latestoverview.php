<?php
$words = new MOD_words();
?>
<h2><?php echo $words->getFormatted('GalleryTitleLatest'); ?></h2>
<p><?php echo $words->getFormatted('galleryTextLatest')?></p>

<?php
require TEMPLATE_DIR.'apps/gallery/overview_simple.php';
?>
<?php
require TEMPLATE_DIR.'apps/gallery/latestflickr.php';
?>