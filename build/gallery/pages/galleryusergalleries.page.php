<?php


//------------------------------------------------------------------------------------
/**
 * page showing latest images and albums of a user
 * 
 *
 */

class GalleryUserGalleriesPage extends GalleryUserPage
{
    protected function init()
    {
        $this->page_title = $this->words->getBuffered("GalleryTitleSets");
    }

    protected function getSubmenuActiveItem()
    {
        return 'overview';
    }

    protected function breadcrumbs() {
        $words = $this->words;
        return '<h1><a href="gallery">'.$words->get('Gallery').'</a> &raquo; <a href="gallery/show/user/'.$this->member->Username.'">'.ucfirst($this->member->Username).'</a></h1>';
    }
    
    public function leftSidebar()
    {
        $galleries = $this->galleries;
        $cnt_pictures = $this->cnt_pictures;
        $username = ($member = $this->loggedInMember) ? $member->username : '';
        $loggedInMember = $this->loggedInMember;
        require SCRIPT_BASE . 'build/gallery/templates/userinfo.php';
    }

    protected function column_col3() {
        $statement = $this->statement;
        $galleries = $this->galleries;
        $words = new MOD_words($this->getSession());
        ?>
        <h2><?php echo $words->getFormatted('GalleryTitleSets'); ?></h2>
        <?php
        require SCRIPT_BASE . 'build/gallery/templates/galleries_overview.php';
    }

}
