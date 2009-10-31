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
        return parent::teaserHeadline();
    }
    
    protected function getTopmenuActiveItem()
    {
        return 'gallery';
    }
    
    protected function getSubmenuActiveItem()
    {
        return 'overview';
    }
    
    public function leftSidebar()
    {
        $gallery = $this->gallery;
        $cnt_pictures = $this->cnt_pictures;
        $username = $this->username;
        require SCRIPT_BASE . 'build/gallery/templates/userinfo.php';
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
