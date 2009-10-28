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
        $headline = '<a href="gallery" title="Gallery">' .$this->words->getBuffered('Gallery'). '</a>';
        $headline.= '<span class="small"> > <a href="gallery/show/sets" title="All Galleries">'.$this->words->getBuffered("Gallery_UploadTitle").'</a></span>';
        return $headline;
    }
    
    public function leftSidebar()
    {

    }

    protected function column_col3() {
        $words = $this->words;
        ?>
        <h2><?php echo $words->getFormatted('Gallery_UploadTitle'); ?></h2>
        <?php
        require 'templates/uploadform.php';
    }

}

?>
