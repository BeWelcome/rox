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

    protected function column_col3() {
        $statement = $this->statement;
        $galleries = $this->galleries;
        $words = new MOD_words($this->getSession());
        $username = $this->member->Username;
        require SCRIPT_BASE . 'build/gallery/templates/user_galleryoverview.php';
    }

}

?>
