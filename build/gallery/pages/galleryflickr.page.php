<?php

/**
 * page showing latest images from flickr
 * 
 *
 */

class GalleryFlickrPage extends GalleryOverviewPage
{

    protected function getSubmenuActiveItem()
    {
        return 'flickr';
    }

    protected function column_col3() {
        $words = $this->getWords();
        require SCRIPT_BASE . 'build/gallery/templates/galleryflickr.column_col3.php';
    }

}

?>
