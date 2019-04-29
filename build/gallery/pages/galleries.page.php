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
        return '';
    }
    
    public function leftSidebar()
    {

    }

    protected function column_col3() {
        $statement = $this->statement;
        $galleries = $this->galleries;
        $words = new MOD_words();
        ?>
        <h3><?php echo $words->getFormatted('Photosets'); ?></h3>
        <?php
        require SCRIPT_BASE . 'build/gallery/templates/galleries_overview.php';
    }

}

