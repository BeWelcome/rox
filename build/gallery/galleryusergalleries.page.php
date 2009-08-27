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
        $this->page_title = 'PAGE';
    }

    protected function getSubmenuActiveItem()
    {
        return 'overview';
    }

    protected function teaserHeadline() {
        $headline = '<a href="gallery" title="Gallery">' .$this->words->getBuffered('Gallery'). '</a>';
        $headline.= '<span class="small"> > <a href="gallery/show/sets" title="All Galleries">'.$this->words->getBuffered("Photosets").'</a></span>';
        return $headline;
        return $this->words->getBuffered('Gallery'). ' > <a href="gallery/show/sets">'.$this->words->getBuffered("Gallery_MemberGalleries",$this->username).'</a>';
    }
    
    public function leftSidebar()
    {
        $galleries = $this->galleries;
        $cnt_pictures = $this->cnt_pictures;
        $username = isset($_SESSION['Username']) ? $_SESSION['Username'] : '';
        require 'templates/userinfo.php';
    }

    protected function column_col3() {
        $statement = $this->statement;
        $galleries = $this->galleries;
        $words = new MOD_words();
        ?>
        <h2><?php echo $words->getFormatted('Photosets'); ?></h2>
        <?php
        require 'templates/galleries_overview.php';
    }

}

?>
