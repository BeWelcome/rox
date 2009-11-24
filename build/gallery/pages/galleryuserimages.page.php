<?php


//------------------------------------------------------------------------------------
/**
 * page showing latest images and albums of a user
 * 
 *
 */

class GalleryUserImagesPage extends GalleryUserPage
{

    protected function getSubmenuActiveItem()
    {
        return 'images';
    }

    protected function column_col3() {
        $statement = $this->statement;
        $words = $this->getWords();
        ?>
        <h2><?php echo $words->getFormatted('GalleryTitleLatest'); ?></h2>
        <?php
        require SCRIPT_BASE . 'build/gallery/templates/overview.php';
    }

}
