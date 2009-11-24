<?php


//------------------------------------------------------------------------------------
/**
 * overview of the gallery, including latest pictures, most important links etc.
 *
 */

class GalleryOverviewPage extends GalleryBasePage
{
    protected function getStylesheets() {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'styles/css/minimal/screen/basemod_minimal_col3_75percent.css';
        return $stylesheets;
    }

    protected function getSubmenuActiveItem()
    {
        return 'overview';
    }

    protected function teaserHeadline() {
        return '<h1>'.$this->getWords()->getBuffered('Gallery').'</h1>';
    }
    
    public function leftSidebar()
    {
        $loggedInMember = $this->loggedInMember;
        $words = $this->words;
        if ($loggedInMember) {
            $galleries = $this->galleries;
            $cnt_pictures = $this->cnt_pictures ? $this->cnt_pictures : 0;
            $username = $loggedInMember->Username;
            require SCRIPT_BASE . 'build/gallery/templates/galleryoverview.leftsidebar.php';
        } else {
            //require SCRIPT_BASE . 'build/gallery/templates/galleryoverview_nonlogged.php';
        }
    }

    protected function column_col3() {
        $statement = $this->statement;
        $galleries = $this->galleries;
        $words = $this->words;
        ?>
        <h3><?php echo $words->getFormatted('GalleryTitleLatest'); ?></h3>
        <?php
        require SCRIPT_BASE . 'build/gallery/templates/galleries_overview.php';
    }

}

?>
