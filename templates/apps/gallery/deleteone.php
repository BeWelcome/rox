<?php
$User = APP_User::login();
if ($User) {
    $Gallery = new Gallery;
    $callbackId = $Gallery->editProcess();
    $vars =& PPostHandler::getVars($callbackId);
}
$words = new MOD_words();

$d = $image;
?>
<h2><?php echo $d->title ?></h2>
<p><?php echo $words->getFormatted('GalleryImageDeleted'); ?></p>