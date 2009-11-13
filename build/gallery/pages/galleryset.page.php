<?php


//------------------------------------------------------------------------------------
/**
 * class for showing all images within a single gallery
 *
 */

class GallerySetPage extends GalleryBasePage
{

    protected function teaserHeadline() {
        $words = $this->words;
        return '<a href="gallery">'.$words->get('Gallery').'</a> > <a href="gallery/show/sets">'.$words->get("Photosets").'</a>';
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
        echo '<h2><a href="gallery/show/sets/'.$gallery->id.'">'.$gallery->title.'</a></h2>';
        if ($this->myself) { echo '<p><a href="gallery/show/sets/'.$gallery->id.'">Delete this gallery</a></p>'; }
        if ($this->myself && $this->upload) {
            // Display the upload form
            require SCRIPT_BASE . 'build/gallery/templates/uploadform.php';
        }
        
        require SCRIPT_BASE . 'build/gallery/templates/latestgallery.php';
        $shoutsCtrl = new ShoutsController;
        $shoutsCtrl->shoutsList('gallery', $gallery->id);
    }
    
    /*
    *  Custom functions
    *
    */


}

?>
