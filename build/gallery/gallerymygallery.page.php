<?php


//------------------------------------------------------------------------------------
/**
 * base class for all pages in the groups system,
 * which don't belong to one specific group.
 *
 */

class GalleryUserPage extends GalleryPage
{

    protected function getSubmenuActiveItem()
    {
        return 'overview';
    }

    protected function teaserHeadline() {
        return $this->getWords()->getBuffered('GalleryMyGallery');
    }
    
    public function leftSidebar()
    {
        $galleries = $this->galleries;
        $cnt_pictures = $this->cnt_pictures;
        $username = $this->loggedInMember ? $this->loggedInMember->Username : '';
        require 'templates/userinfo.php';
    }

    protected function column_col3() {
        $statement = $this->statement;
        $words = new MOD_words();
        ?>
        <h2><?php echo $words->getFormatted('GalleryTitleLatest'); ?></h2>
        <?php
        require 'templates/overview.php';
    }

}

?>
