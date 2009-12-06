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
        return '<a href="gallery">'.parent::breadcrumbs() . '</a> &gt; '. $this->getWords()->getBuffered('GalleryDelete');
    }

    protected function column_col3() {
        $gallery = $this->gallery;
        $statement = $this->statement;
        $words = $this->getWords();

        if ($this->deleted) echo 'Gallery successfully deleted.';
        else require SCRIPT_BASE . 'build/gallery/templates/gallerydelete.column_col3.php';
    }

}
