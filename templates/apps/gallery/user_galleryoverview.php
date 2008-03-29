<?php
$words = new MOD_words();
$Gallery = new Gallery;
$callbackId = $Gallery->updateGalleryProcess();
$vars = PPostHandler::getVars($callbackId);
?>

<h2><?php echo $words->getFormatted('GalleryTitleGalleries'); ?></h2>
<?php
require TEMPLATE_DIR.'apps/gallery/galleries_overview.php';
?>

<h2><?php echo $words->getFormatted('GalleryTitleLatest'); ?></h2>
<p><?php echo $words->getFormatted('galleryTextLatest')?></p>

<form method="post" action="gallery/show/user/<?=$userHandle?>/sets" class="def-form">

    <legend><?php echo $words->getFormatted('GalleryTitleEdit'); ?></legend>
        <div class="row">
            <label for="image-sets-Gallery"><?php echo $words->getFormatted('GalleryLabelTitle'); ?></label><br/>

            <p class="desc"><?php echo $words->getFormatted('GalleryDescTitle'); ?></p>
            <input type="hidden" name="<?=$callbackId?>" value="1"/>
        </div>

<?php
require TEMPLATE_DIR.'apps/gallery/overview.php';
?>

Add to existing gallery
<br />
<input type="radio" name="new" value="0">

<?php
echo '<select name="gallery" size="1">';
    foreach ($galleries as $d) {
    	echo '<option value="'.$d->id.'">'.$d->title.'</option>';
    }
echo '</select>
<br />
or  Create new one: 
<br />
<input type="radio" name="new" value="1"> 
<input name="gallery-title" type="text" size="20" maxlength="30">
<br>';

echo '
    <input type="submit" name="button" value="submit" id="button" />';
?>


</form>