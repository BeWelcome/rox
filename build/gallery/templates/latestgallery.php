<?php
$request = PRequest::get()->request;
$Gallery = new Gallery;
$callbackId = $Gallery->updateGalleryProcess();
$vars = PPostHandler::getVars($callbackId);
$words = new MOD_words();
?>

<form method="post" action="gallery/show/sets/<?=$request[3]?>/edit/images" name="mod-images" class="def-form">
            <input type="hidden" name="<?=$callbackId?>" value="1"/>
            <input type="hidden" name="gallery" value="<?=$request[3]?>"/>

<?php
require 'overview.php';
?>
<?php
require 'user_controls.php';
?>

</form>