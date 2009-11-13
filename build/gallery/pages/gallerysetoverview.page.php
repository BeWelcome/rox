<?php


//------------------------------------------------------------------------------------
/**
 * base class for all pages in the groups system,
 * which don't belong to one specific group.
 *
 */

class GallerySetOverviewPage extends GalleryBasePage
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
        $d = $this->d;
        $num_rows = $this->num_rows;
        echo '<h2><a href="gallery/show/sets/'.$gallery->id.'">'.$gallery->title.'</a></h2>';
        if ($this->myself) { echo '<p><a href="gallery/show/sets/'.$gallery->id.'/delete">Delete this gallery</a></p>'; }
        if ($this->myself && $this->upload) {
            // Display the upload form
            require SCRIPT_BASE . 'build/gallery/templates/uploadform.php';
        }
        require SCRIPT_BASE . 'build/gallery/templates/gallerysetoverview.column_col3.php';
    }
    
    public function leftSidebar() {}
    
    /*
    *  Custom functions
    *
    */


}

?>
