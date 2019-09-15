<?php


//------------------------------------------------------------------------------------
/**
 * overview of the gallery, including latest pictures, most important links etc.
 *
 */

class GalleryOverviewPage extends GalleryBasePage
{
    protected function init()
    {
        parent::init();
        $this->addLateLoadScriptFile('build/gallery.js');
    }

    protected function getStylesheets() {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'styles/css/minimal/screen/basemod_minimal_col3_75percent.css';
        return $stylesheets;
    }

    protected function getSubmenuActiveItem()
    {
        return 'overview';
    }

    protected function teaser() {
        echo '<div id="teaser" class="page-teaser clearfix">'.$this->teaserHeadline().'</div>';
    }

    protected function teaserHeadline() {
        return '<h1>'.$this->getWords()->get('Gallery').'</h1>';
    }

    public function leftSidebar()
    {
        $loggedInMember = $this->loggedInMember;
        $words = $this->words;
        $galleries = $this->galleries;
        $cnt_pictures = $this->cnt_pictures ? $this->cnt_pictures : 0;
        require SCRIPT_BASE . 'build/gallery/templates/galleryoverview.leftsidebar.php';
    }

    protected function column_col3() {
        $statement = $this->statement;
        $galleries = $this->galleries;
        $words = $this->words;
        $itemsPerPage = 12;
        require SCRIPT_BASE . 'build/gallery/templates/galleries_overview.php';
    }
}
