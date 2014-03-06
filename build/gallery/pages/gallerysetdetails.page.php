<?php


//------------------------------------------------------------------------------------
/**
 * class for showing all images within a single gallery
 *
 */

class GallerySetDetailsPage extends GallerySetPage
{
    
    protected function getTopmenuActiveItem()
    {
        return 'gallery';
    }
    
    protected function getSubmenuActiveItem()
    {
        return 'details';
    }
    
    protected function column_col3() {
        $words = $this->words;
        $cnt_pictures = $this->cnt_pictures;
        $statement = $this->statement;
        $gallery = $this->gallery;

        $mem_redirect = $this->layoutkit->formkit->getMemFromRedirect();
        $page_url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);
        $formkit = $this->layoutkit->formkit;
        $callback_tag = $formkit->setPostCallback('GalleryController', 'updateGalleryCallback');

        if ($this->myself && $this->upload) {
            // Display the upload form
            require SCRIPT_BASE . 'build/gallery/templates/uploadform.php';
        }
        $this->thumbsize = 1;
        echo '<form method="POST" action="">'.$callback_tag;
        require SCRIPT_BASE . 'build/gallery/templates/imagefixedcolumns.list.php';
        if ($this->myself) {
        echo <<<HTML
        <p class="small">
            <input type="checkbox" name="selectAllRadio" class="checker" onclick="common.selectAll(this);">
            &nbsp;&nbsp;{$words->get('SelectAll')}
            &nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;
            {$words->get('GalleryWithSelected')}: &nbsp;&nbsp;&nbsp;&nbsp;

            <input name="gallery" type="hidden" value="{$gallery->id}">
            <input name="removeOnly" type="hidden" value="1">
            <input type="submit" class="button" name="button" value="{$words->getBuffered('GalleryRemoveImagesFromPhotoset')}" class="button" style="cursor:pointer"/>
            <a href="gallery/show/sets/{$this->gallery->id}/upload" class="button" /><img src="images/icons/picture_add.png">{$words->get('GalleryUploadPhotos')}</a>

        </p>
        </form>
HTML;
        }
    }
    
    /*
    *  Custom functions
    *
    */


}

?>
