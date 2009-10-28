<?php


//------------------------------------------------------------------------------------
/**
 * Page for the deletion of a single gallery
 *
 */

class GalleryDeletePage extends GalleryBasePage
{       
    protected function getSubmenuItems()
    {
        $words = $this->getWords();
        $member = $this->loggedInMember;
        $items = array();
        $items[] = array('overview', 'gallery', $words->get('GalleryAllPhotos'));
        if ($member->Status == ("Active" || "NeedMore" || "Pending")) {
            $items[] = array('user', 'gallery/show/user/'.APP_User::get()->getHandle(), $words->get('GalleryMy'));
            $items[] = array('upload', 'gallery/upload', $words->get('GalleryUpload'));
        }
        $items[] = array('flickr', 'gallery/flickr', $words->get('GalleryFlickr'));
        return $items; 
    }

    protected function getSubmenuActiveItem()
    {
        return 'overview';
    }

    protected function teaserHeadline() {
        return $this->getWords()->getBuffered('GalleryDelete');
    }
    
    public function leftSidebar()
    {
        $galleries = $this->galleries;
        $cnt_pictures = $this->cnt_pictures;
        $username = isset($this->loggedInMember->Username) ? $this->loggedInMember->Username : '';
        require 'templates/userinfo.php';
    }

    protected function column_col3() {
        $statement = $this->statement;
        $words = new MOD_words();
        ?>
        <h2><?php echo $words->getFormatted('GalleryDelete'); ?></h2>
        <?php
        require 'templates/overview.php';
    }

}

?>
