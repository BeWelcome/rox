<?php


//------------------------------------------------------------------------------------
/**
 * GalleryImagePage shows a single image with the corresponding info
 *
 */


class GalleryImagePage extends GalleryBasePage
{

    #[\Override]
    protected function getStylesheets() {
        $stylesheets = parent::getStylesheets();
        //$stylesheets[] = 'styles/css/minimal/screen/basemod_minimal_col3_75percent.css';
        return $stylesheets;
    }

    #[\Override]
    protected function getSubmenuActiveItem()
    {
        return 'overview';
    }

    #[\Override]
    protected function teaserHeadline() {
        $title = ($this->image) ? $this->image->title : '';
        return '<h3 class="mt-2"> - '.$title.'</h3>';
    }

    public function leftSidebar() {
        // require SCRIPT_BASE . 'build/gallery/templates/galleryimage.leftsidebar.php';
    }


}
