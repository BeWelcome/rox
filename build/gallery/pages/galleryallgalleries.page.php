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
        $headline.= '<span class="small"> > <a href="gallery/show/sets" title="All Galleries">'.$this->words->getBuffered("Photosets").'</a></span>';
        return $headline;
        return $this->words->getBuffered('Gallery'). ' > <a href="gallery/show/sets">'.$this->words->getBuffered("Gallery_MemberGalleries",$this->username).'</a>';
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

?>
