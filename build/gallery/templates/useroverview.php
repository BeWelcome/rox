<?php
$words = new MOD_words();
$i18n = new MOD_i18n('apps/gallery/overview.php');
$i18n->setEnvVar('userHandle', $userHandle);
?>
<h2><?php echo $words->getFormatted('GalleryTitleUserOverview'); ?></h2>
<?php
require 'overview.php';
?>