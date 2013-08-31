<?php


//------------------------------------------------------------------------------------
/**
 * Page for the deletion of a single gallery
 *
 */

class GalleryDeletePage extends GallerySetPage
{       

    protected function getSubmenuActiveItem()
    {
        return 'delete';
    }

    protected function breadcrumbs()
    {
        return '<h1><a href="gallery">'.$this->getWords()->getBuffered('Gallery').'</a> &raquo; <a href="gallery/show/user/'.$this->member->Username.'">'.ucfirst($this->member->Username).'</a> &raquo; <a href="gallery/show/user/'.$this->member->Username.'/sets">'.$this->getWords()->getBuffered("Photosets").'</a> &raquo; ' . $this->getWords()->getBuffered('GalleryDelete') . '</h1>';
    }

    protected function column_col3() {
        $gallery = $this->gallery;
        $statement = $this->statement;
        $words = $this->getWords();

        if ($this->deleted) echo $words->getFormatted('GalleryDeleted');
        else require SCRIPT_BASE . 'build/gallery/templates/gallerydelete.column_col3.php';
    }

}
