<?php


//------------------------------------------------------------------------------------
/**
 * class for showing all images within a single gallery
 *
 */

class GallerySetDetailsPage extends GallerySetPage
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
        return 'details';
    }
    
    protected function column_col3() {
        $words = $this->words;
        $cnt_pictures = $this->cnt_pictures;
        $statement = $this->statement;
        $gallery = $this->gallery;
        echo '<h2><a href="gallery/show/sets/'.$gallery->id.'" class="black">'.$gallery->title.'</a></h2>';
        echo '<div class="gallery_menu">';
        echo $this->submenu().'</div>';
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
