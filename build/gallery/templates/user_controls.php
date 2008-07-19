<?php
/*
/* user controls for use with a list of pictures
/* */
$words = new MOD_words();
$User = new APP_User;
$R = MOD_right::get();
$GalleryRight = $R->hasRight('Gallery');

if ($User && (($User->getHandle() == $userHandle) || ($GalleryRight > 1)) ) {

if (!isset($galleries))
$galleries = $this->_model->getUserGalleries($User->getId());

?>
    <div class="floatbox">
        <hr />
<?php if ($type == 'galleries') {

/*
/* If the controls handle galleries
*/ 

$Gallery = new Gallery;
$callbackId = $Gallery->updateGalleryProcess();
$vars = PPostHandler::getVars($callbackId);
?>

<form method="post" action="gallery/create/finished" class="def-form" id="gallery-create-form">
<input type="hidden" name="<?=$callbackId?>" value="1"/>
<p class="small">
    <img src="images/icons/add.png" class="float_left"> <?=$words->get('GalleryCreateNewPhotoset')?>
    &nbsp;&nbsp;
    <input type="hidden" name="new" id="newGallery" value="1">
    <input name="g-user" type="hidden" value="<?=$User->getId()?>">
    <input name="g-title" type="text" size="20" maxlength="30">
    <input type="submit" name="button" value="<?=$words->getBuffered('Create')?>" id="button" />
</p>
</form>


<?php } elseif ($type == 'gallery') { 
/*
/* If the controls handle images within a gallery
*/

?>

<p class="small">
    <input type="checkbox" name="selectAllRadio" class="checker" onclick="selectAll(this);">
    &nbsp;&nbsp;<?=$words->get('SelectAll')?>
    &nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;
    <?=$words->get('GalleryWithSelected')?>: &nbsp;&nbsp;&nbsp;&nbsp;

    <input name="removeOnly" type="hidden" value="1">
    <input type="submit" name="button" value="<?=$words->getBuffered('GalleryRemoveImagesFromPhotoset')?>" class="button" style="cursor:pointer"/>

</p>

<?php } elseif ($type == 'images') {
/*
/* If the controls handle images
*/
 
?>
<p class="small">
    <input type="checkbox" name="selectAllRadio" class="checker" onclick="selectAll(this);">
    &nbsp;&nbsp;<?=$words->get('SelectAll')?>
    &nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;
    <?=$words->get('ImagesWithSelected')?>: &nbsp;&nbsp;&nbsp;&nbsp;

    <input name="removeOnly" id="removeonly" type="hidden" value="1">
    <input type="submit" name="button" value="<?=$words->getBuffered('Delete')?>" class="button" onclick="return confirm('<?=$words->getBuffered("confirmdeleteimages")?>')" style="cursor:pointer"/>

    <br />
    <br />
<?php
if ($galleries) { ?>
<img src="images/icons/picture_go.png"> <?=$words->get('GalleryAddToPhotoset')?>
<br />
<input type="radio" name="new" id="oldGallery" value="0">&nbsp;&nbsp;

<select name="gallery" size="1" onchange="$('oldGallery').checked = true;$('newGallery').checked = false;">
    <option value="">- <?=$words->get('GallerySelectPhotoset')?> -</option>
<?php
    foreach ($galleries as $d) {
    	echo '<option value="'.$d->id.'">'.$d->title.'</option>';
    }
?>
</select>
<br />
<?=$words->get('or')?>
<?php } ?> 

<?=$words->get('GalleryCreateNewPhotoset')?>: 
<br />
<input type="radio" name="new" id="newGallery" value="1">&nbsp;&nbsp;
<input name="g-user" type="hidden" value="<?=$User->getId()?>">
<input name="g-title" id="g-title" type="text" size="20" maxlength="30" onclick="$('newGallery').checked = true;$('oldGallery').checked = false;$('removeonly').value = 0;">
<br>
<input type="submit" name="button" value="<?=$words->getBuffered('Add')?>" id="button" onclick="CheckEmpty('g-title');$('removeonly').value = 0;"/>
</p>


<?php } else { ?>
    <p class="small"><a style="cursor:pointer" href="gallery/show/galleries/delete" class="button" onclick="return confirm('<?=$words->getBuffered("confirmdeletegallery")?>')"> <?=$words->get('GalleryDeletePhotoset')?> </a>
    <a style="cursor:pointer" href="gallery/show/galleries/delete" class="button" onclick="return confirm('<?=$words->getBuffered("confirmdeletegallery")?>')"> <?=$words->get('GalleryAddToPhotoset')?> </a></p>

<?php } 
echo $words->flushBuffer(); ?>
    </div>
    
<?php } ?>