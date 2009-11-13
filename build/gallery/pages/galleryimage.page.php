<?php


//------------------------------------------------------------------------------------
/**
 * GalleryImagePage shows a single image with the corresponding info
 *
 */


class GalleryImagePage extends GalleryBasePage
{

    protected function getSubmenuActiveItem()
    {
        return 'overview';
    }

    protected function teaserHeadline() {
        return parent::teaserHeadline();
    }
    
    public function leftSidebar() {
        require SCRIPT_BASE . 'build/gallery/templates/galleryimage.leftsidebar.php';
    }
    
    protected function userControls() {
?>
        <p class="small">
            <input type="checkbox" name="selectAllRadio" class="checker" onclick="selectAll(this);">
            &nbsp;&nbsp;<?=$words->get('SelectAll')?>
            &nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;
            <?=$words->get('ImagesWithSelected')?>: &nbsp;&nbsp;&nbsp;&nbsp;

            <input name="deleteOnly" id="deleteonly" type="hidden" value="0">

            <input type="submit" name="button" value="<?=$words->getBuffered('Delete')?>" class="button" onclick="return confirm('<?=$words->getBuffered("confirmdeleteimages")?>'); $('deleteonly').value = 1;" style="cursor:pointer"/>

            <br />
            <br />
        <?php
        if (isset($galleries) && $galleries) { ?>
        <img src="images/icons/picture_go.png"> <?=$words->get('GalleryAddToPhotoset')?>
        <br />
        <input type="radio" name="new" id="oldGallery" value="0">&nbsp;&nbsp;
        <input name="removeOnly" type="hidden" value="0">
        <select name="gallery" size="1" onchange="$('oldGallery').checked = true;">
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
        <input name="g-title" id="g-title" type="text" size="20" maxlength="30" onclick="$('newGallery').checked = true; $('deleteonly').value = 0;">
        <br>
        <input type="submit" name="button" value="<?=$words->getBuffered('Add')?>" id="button" onclick="$('deleteonly').value = 0; return submitStuff();"/>
        </p>
<?php
    }
}
