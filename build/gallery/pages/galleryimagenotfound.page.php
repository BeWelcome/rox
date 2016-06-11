<?php

//------------------------------------------------------------------------------------
/**
 * GalleryImageNotFoundPage shows a sorry-message if a given image was not found
 *
 */


class GalleryImageNotFoundPage extends GalleryImagePage
{

    protected function getSubmenuActiveItem()
    {
        return 'overview';
    }

    protected function teaserHeadline() {
        return '<a href="gallery">'.parent::teaserHeadline() . '</a> &raquo; '. $this->getWords()->getBuffered('GalleryImageNotFound');
    }
    
    protected function column_col3() {
        $words = new MOD_words();
        ?>
        <p class=" error"><?php echo $words->getFormatted('GalleryImageNotFoundText'); ?></p>
        <?php
    }
    
    public function leftSidebar()
    {
    }

}
