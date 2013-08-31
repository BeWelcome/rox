<?php


//------------------------------------------------------------------------------------
/**
 * page showing the latest galleries
 * 
 *
 */

class GalleryAllGalleriesPage extends GalleryBasePage
{

    protected function getSubmenuActiveItem()
    {
        return 'overview';
    }

    protected function teaserHeadline() {
        $headline = '<a href="gallery" title="Gallery">' .$this->words->getBuffered('Gallery'). '</a>';
        $headline.= '<span class="small"> &raquo; <a href="gallery/show/sets" title="All Galleries">'.$this->words->getBuffered("Photosets").'</a></span>';
        return $headline;
        return $this->words->getBuffered('Gallery'). ' &raquo; <a href="gallery/show/sets">'.$this->words->getBuffered("Gallery_MemberGalleries",$this->member->username).'</a>';
    }
    
    public function leftSidebar()
    {

    }

    protected function column_col3() {
        $statement = $this->statement;
        $galleries = $this->galleries;
        $words = $this->getWords();
        ?>
        <h3><?php echo $words->getFormatted('Photosets'); ?></h3>
        <?php
        require SCRIPT_BASE . 'build/gallery/templates/galleries_overview.php';
    }

}

?>
