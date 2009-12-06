<?php


//------------------------------------------------------------------------------------
/**
 * page showing latest images
 * 
 *
 */

class GalleryAllImagesPage extends GalleryOverviewPage
{

    protected function getSubmenuActiveItem()
    {
        return 'images';
    }

    protected function column_col3() {
        $words = $this->getWords();
        $statement = $this->statement;
        echo '<h3>'.$words->get('GalleryAllPhotos').'</h3>';
        require SCRIPT_BASE . 'build/gallery/templates/overview.php';
    }

}

?>
