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
    
<?php if ($type == 'galleries') { ?>

    <h3>Create new photoset:</h3> 
    <br />
    <input type="radio" name="new" value="1" class="float_left"> 
    <input name="g-user" type="hidden" value="<?=$User->getId()?>">
    <input name="g-title" type="text" size="20" maxlength="30" class="float_left">
    <br>
    <input type="submit" name="button" value="submit" id="button" />

    <p class="small"><a style="cursor:pointer" href="gallery/show/galleries/delete" class="button float_right" onclick="return confirm('<?=$words->getFormatted("confirmdeletegallery")?>')"> Delete gallery </a>
    <a style="cursor:pointer" href="gallery/show/galleries/delete" class="button float_right" onclick="return confirm('<?=$words->getFormatted("confirmdeletegallery")?>')"> Add to gallery </a></p>
    
<?php } elseif ($type == 'gallery') { ?>

<p class="small">
    <input type="checkbox" name="selectAllRadio" class="checker" onclick="selectAll(this);"> &nbsp;&nbsp;Select all &nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp; The selected images: &nbsp;&nbsp;&nbsp;&nbsp;
</p>

<input name="removeOnly" type="hidden" value="1">
<input type="submit" name="button" value="Remove from gallery" id="button" />

<?php } elseif ($type == 'images') { 

if ($galleries) { ?>
Add to existing gallery
<br />
<input type="radio" name="new" id="oldGallery" value="0">&nbsp;&nbsp;

<select name="gallery" size="1" onchange="$('oldGallery').checked = true;$('newGallery').checked = false;">
    <option value="">- <?=$words->getFormatted("Select gallery")?> -</option>
<?php
    foreach ($galleries as $d) {
    	echo '<option value="'.$d->id.'">'.$d->title.'</option>';
    }
?>
</select>
<br />
or
<?php } ?>
Create new one: 
<br />
<input type="radio" name="new" id="newGallery" value="1">&nbsp;&nbsp;
<input name="g-user" type="hidden" value="<?=$User->getId()?>">
<input name="g-title" id="g-title" type="text" size="20" maxlength="30" onclick="$('newGallery').checked = true;$('oldGallery').checked = false;">
<br>
<input type="submit" name="button" value="Add to gallery" id="button" onclick="CheckEmpty('g-title')"/>

<p class="small">
    <a style="cursor:pointer" href="gallery/show/image/delete" class="button" onclick="return confirm('<?=$words->getFormatted("confirmdeletegallery")?>')">
    Delete images </a>
    <input type="checkbox" class="checker" name="selectAllRadio" onclick="selectAll(this);"> Select all
</p>

<?php } else { ?>

    <p class="small"><a style="cursor:pointer" href="gallery/show/galleries/delete" class="button" onclick="return confirm('<?=$words->getFormatted("confirmdeletegallery")?>')"> Delete gallery </a>
    <a style="cursor:pointer" href="gallery/show/galleries/delete" class="button" onclick="return confirm('<?=$words->getFormatted("confirmdeletegallery")?>')"> Add to gallery </a></p>

<?php } ?>
    </div>
    
<?php } ?>