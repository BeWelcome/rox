<?php


//------------------------------------------------------------------------------------
/**
 * page showing latest images and albums of a user
 * 
 *
 */

class GalleryUserOverviewPage extends GalleryUserPage
{

    protected function getSubmenuActiveItem()
    {
        return 'overview';
    }

    protected function teaserHeadline() {
        return parent::teaserHeadline();
    }

    protected function column_col3() {
        $statement = $this->statement;
        $galleries = $this->galleries;
        $words = new MOD_words();
        $username = $this->username;
        ?>
        <h2><?php echo $words->getFormatted('GalleryTitleLatest'); ?></h2>
        <?php
        require SCRIPT_BASE . 'build/gallery/templates/user_galleryoverview.php';
    }

}
