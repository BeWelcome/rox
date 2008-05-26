<?php
$request = PRequest::get()->request;
$Gallery = new Gallery;
$callbackId = $Gallery->updateGalleryProcess();
$vars = PPostHandler::getVars($callbackId);
$words = new MOD_words();
?>

<form method="post" action="gallery/show/galleries/<?=$request[3]?>/edit/images" name="mod-images" class="def-form">
            <input type="hidden" name="<?=$callbackId?>" value="1"/>

<?php
require TEMPLATE_DIR.'apps/gallery/overview.php';
?>
<?php
require TEMPLATE_DIR.'apps/gallery/user_controls.php';
?>

</form>