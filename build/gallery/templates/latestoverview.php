<?php
$words = new MOD_words();
?>
<h2><?php echo $words->getFormatted('GalleryTitleLatest'); ?></h2>
<?php
require 'overview.php';
