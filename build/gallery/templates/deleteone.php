<?php
$User = APP_User::login();
if ($User) {
    $Gallery = new Gallery;
    $callbackId = $Gallery->editProcess();
    $vars =& PPostHandler::getVars($callbackId);
}
$words = new MOD_words();

$d = $image;

if ($deleted){ 
?>
<p class="note"><img src="images/misc/check.gif">&nbsp; &nbsp; <?php echo $words->getFormatted('GalleryImageDeleted'); ?>: <i><?php echo $d->title ?></i></p>
<?php } else { ?>
<p class="warning"><img src="images/misc/checkfalse.gif">&nbsp; &nbsp; <?php echo $words->getFormatted('GalleryImageNotDeleted'); ?>: <i><?php echo $d->title ?></i></p>
<?php } ?>