<?php


//------------------------------------------------------------------------------------
/**
 * base class for all pages in the groups system,
 * which don't belong to one specific group.
 *
 */

class GalleryPage extends GalleryBasePage
{

    protected function teaserHeadline() {
        return '<a href="gallery">'.$this->getWords()->getBuffered('Gallery').'</a>';
    }
    
    protected function getTopmenuActiveItem()
    {
        return 'gallery';
    }
    
    protected function getSubmenuActiveItem()
    {
        return 'overview';
    }
    
    protected function column_col3() {
        $words = $this->words;
        $cnt_pictures = $this->cnt_pictures;
        $statement = $this->statement;
        $gallery = $this->gallery;
        echo '<span class="small">';
        echo ' > <a href="gallery/show/sets">'.$words->get("Photosets").'</a> > <a href="gallery/show/sets/'.$gallery->id.'">'.$gallery->title.'</a>';
        echo '</span>';
        if ($this->myself && $this->upload) {
            // Display the upload form
            require 'templates/uploadform.php';
        }
        
        require 'templates/latestgallery.php';
        $shoutsCtrl = new ShoutsController;
        $shoutsCtrl->shoutsList('gallery', $gallery->id);
    }
    
    /*
    *  Custom functions
    *
    */


}

?>
