<?php


//------------------------------------------------------------------------------------
/**
 * page showing the latest galleries
 * 
 *
 */

class GalleryStreamPage extends GalleryBasePage
{

    protected function getSubmenuActiveItem()
    {
        return 'stream';
    }

    protected function teaserHeadline() {
        $headline = '<a href="gallery" title="Gallery">' .$this->words->getBuffered('Gallery'). '</a>';
        $headline.= '<span class="small"> > <a href="gallery/show/sets" title="Gallery timeline">'.$this->words->getBuffered("GalleryStream").'</a></span>';
        return $headline;
        return $this->words->getBuffered('Gallery'). ' > <a href="gallery/show/sets">'.$this->words->getBuffered("Gallery_MemberGalleries",$this->member->username).'</a>';
    }
    
    public function leftSidebar()
    {

    }

    protected function column_col3() {
        $statement = $this->statement;
        $galleries = $this->galleries;
        $words = $this->getWords();
        ?>
        <h2><?php echo $words->getFormatted('Stream'); ?></h2>
        <?php
        require SCRIPT_BASE . 'build/gallery/templates/galleries_stream.php';
    }

}

?>
