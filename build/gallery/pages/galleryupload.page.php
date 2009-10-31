<?php


//------------------------------------------------------------------------------------
/**
 * page showing the latest galleries
 * 
 *
 */

class GalleryUploadPage extends GalleryBasePage
{
    protected function init()
    {
        parent::init();
        $this->page_title = 'Upload Pictures | BeWelcome';        
    }

    protected function getSubmenuActiveItem()
    {
        return 'overview';
    }

    protected function teaserHeadline() {
        return '<a href="gallery">'.parent::teaserHeadline() . '</a> &gt; '. $this->words->getBuffered("Gallery_UploadTitle");
    }
    
    public function leftSidebar()
    {

    }

    protected function column_col3() {
        $words = $this->words;
        ?>
        <h2><?php echo $words->getFormatted('Gallery_UploadTitle'); ?></h2>
        <?php
        require SCRIPT_BASE . 'build/gallery/templates/uploadform.php';
    }

}
