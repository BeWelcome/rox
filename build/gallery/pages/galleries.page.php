<?php


//------------------------------------------------------------------------------------
/**
 * page showing the latest galleries
 * 
 *
 */

class GalleryGalleriesPage extends GalleryBasePage
{

    protected function getSubmenuActiveItem()
    {
        return 'overview';
    }

    protected function teaserHeadline()
    {
        return '<a href="gallery">'.parent::teaserHeadline() . '</a> &gt; '. $this->words->getBuffered("Photosets");
    }
    
    public function leftSidebar()
    {

    }

    protected function column_col3() {
        $statement = $this->statement;
        $galleries = $this->galleries;
        $words = new MOD_words();
        ?>
        <h2><?php echo $words->getFormatted('Photosets'); ?></h2>
        <?php
        require SCRIPT_BASE . 'build/gallery/templates/galleries_overview.php';
    }

}

