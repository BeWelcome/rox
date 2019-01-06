<?php


//------------------------------------------------------------------------------------
/**
 * page showing latest images and albums of a user
 * 
 *
 */

class GalleryUserImagesPage extends GalleryUserPage
{

    protected function getSubmenuActiveItem()
    {
        return 'images';
    }

    protected function column_col3() {
        $statement = $this->statement;
        $words = $this->getWords();
        $this->thumbsize = 1;

        echo '<div class="col-12"><h3>'.$words->getFormatted('GalleryTitleLatest').'</h3></div>';
        require SCRIPT_BASE . 'build/gallery/templates/imagefixedcolumns.list.php';
    }

}
