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

    protected function teaserHeadline()
    {
        return '<a href="gallery">'.parent::teaserHeadline() . '</a> &gt; '. $this->getWords()->getBuffered('GalleryDelete');
    }

    protected function column_col3() {
        $gallery = $this->gallery;
        $statement = $this->statement;
        $words = $this->getWords();
        echo '<h2><a href="gallery/show/sets/'.$gallery->id.'" class="black">'.$gallery->title.'</a></h2>';
        echo '<div class="gallery_menu">';
        echo $this->submenu().'</div>';

        if ($this->deleted) echo 'Gallery successfully deleted.';
        else require SCRIPT_BASE . 'build/gallery/templates/gallerydelete.column_col3.php';
    }

}
