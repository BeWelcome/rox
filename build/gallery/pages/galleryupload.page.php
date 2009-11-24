<?php


//------------------------------------------------------------------------------------
/**
 * page showing the latest galleries
 * 
 *
 */

class GalleryUploadPage extends GalleryUserPage
{
    protected function init()
    {
        parent::init();
        $this->page_title = 'Upload Pictures | BeWelcome';        
    }

    protected function getSubmenuActiveItem()
    {
        return 'upload';
    }

    protected function column_col3() {
        $words = $this->words;
        echo <<<HTML
        <h2>{$words->getFormatted('Gallery_UploadTitle')}</h2>
HTML;
        require SCRIPT_BASE . 'build/gallery/templates/uploadform.php';
    }

}
